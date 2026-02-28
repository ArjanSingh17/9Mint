<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CreatorCollectionController extends Controller
{
    public const CREATION_FEE_GBP = 80.00;

    public function create()
    {
        return view('creator.collections.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string'],
            'cover_image' => ['nullable', 'image', 'max:5120'],
            'ref_currency' => [
                'required',
                'string',
                'max:10',
                Rule::in(array_map('strtoupper', config('pricing.enabled_currencies', ['GBP']))),
            ],
            'nfts' => ['required', 'array', 'min:5'],
            'nfts.*.name' => ['required', 'string', 'max:160'],
            'nfts.*.description' => ['nullable', 'string'],
            'nfts.*.editions_total' => ['required', 'integer', 'min:1'],
            'nfts.*.ref_amount' => ['required', 'numeric', 'min:0.01'],
            'nfts.*.image' => ['required', 'image', 'max:5120'],
        ]);

        $this->clearExistingCreatorDraft($request);
        $draft = $this->buildCreatorDraft($data);

        $request->session()->forget('checkout_order_id');
        $request->session()->forget('creator_fee_collection_id');
        $request->session()->put('creator_fee_draft', $draft);

        return redirect()
            ->route('checkout.index')
            ->with('status', 'Collection draft saved. Complete the £80 creation fee checkout to submit it for review.');
    }

    private function buildCreatorDraft(array $data): array
    {
        $draftId = (string) Str::uuid();
        $baseDir = "creator-drafts/{$draftId}";
        $nfts = [];

        foreach (array_values($data['nfts']) as $index => $nftInput) {
            $imageTempPath = $this->storeUploadedFileToDraft(
                $nftInput['image'],
                $baseDir,
                'nft-' . ($index + 1)
            );

            $nfts[] = [
                'name' => $nftInput['name'],
                'description' => $nftInput['description'] ?? null,
                'editions_total' => (int) $nftInput['editions_total'],
                'ref_amount' => (float) $nftInput['ref_amount'],
                'image_temp_path' => $imageTempPath,
            ];
        }

        return [
            'id' => $draftId,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'ref_currency' => strtoupper((string) $data['ref_currency']),
            'cover_image_temp_path' => ! empty($data['cover_image'])
                ? $this->storeUploadedFileToDraft($data['cover_image'], $baseDir, 'cover')
                : null,
            'nfts' => $nfts,
            'creation_fee_amount_gbp' => self::CREATION_FEE_GBP,
            'created_at' => now()->toIso8601String(),
        ];
    }

    private function storeUploadedFileToDraft(
        \Illuminate\Http\UploadedFile $file,
        string $baseDir,
        string $prefix
    ): string {
        $extension = strtolower((string) $file->getClientOriginalExtension());
        if ($extension === '') {
            $extension = strtolower((string) $file->extension());
        }
        if ($extension === '') {
            $extension = 'png';
        }

        $filename = $prefix . '-' . Str::uuid() . '.' . $extension;

        return (string) $file->storeAs($baseDir, $filename, 'local');
    }

    private function clearExistingCreatorDraft(Request $request): void
    {
        $existing = $request->session()->get('creator_fee_draft');
        if (is_array($existing) && ! empty($existing['id'])) {
            Storage::disk('local')->deleteDirectory('creator-drafts/' . $existing['id']);
        }
        $request->session()->forget('creator_fee_draft');
    }
}
