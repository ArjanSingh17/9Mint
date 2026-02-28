<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Models\Nft;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
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

        $user = $request->user();

        $collection = DB::transaction(function () use ($data, $user) {
            $collectionSlug = $this->uniqueCollectionSlug($data['name']);

            $collection = Collection::create([
                'slug' => $collectionSlug,
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'cover_image_url' => null,
                'creator_name' => $user->name,
                'submitted_by_user_id' => $user->id,
                'approval_status' => Collection::APPROVAL_PENDING,
                'is_public' => false,
                'creation_fee_payment_state' => 'unpaid',
                'creation_fee_refund_state' => 'none',
                'creation_fee_amount_gbp' => self::CREATION_FEE_GBP,
            ]);

            $collectionFolder = $collection->uploadFolderName();
            $targetDirectory = public_path("images/nfts/{$collectionFolder}");
            if (! File::exists($targetDirectory)) {
                File::makeDirectory($targetDirectory, 0755, true);
            }

            if (! empty($data['cover_image'])) {
                $coverImageUrl = $this->storeUploadedImageToCollectionFolder(
                    $data['cover_image'],
                    $collectionFolder,
                    'cover'
                );
                $collection->update(['cover_image_url' => $coverImageUrl]);
            }

            foreach (array_values($data['nfts']) as $index => $nftInput) {
                $imageUrl = $this->storeUploadedImageToCollectionFolder(
                    $nftInput['image'],
                    $collectionFolder,
                    'nft-' . ($index + 1)
                );
                Nft::create([
                    'collection_id' => $collection->id,
                    'slug' => $this->uniqueNftSlug($nftInput['name']),
                    'name' => $nftInput['name'],
                    'description' => $nftInput['description'] ?? null,
                    'image_url' => $imageUrl,
                    'editions_total' => (int) $nftInput['editions_total'],
                    'editions_remaining' => (int) $nftInput['editions_total'],
                    'primary_ref_amount' => (float) $nftInput['ref_amount'],
                    'primary_ref_currency' => strtoupper((string) $data['ref_currency']),
                    'is_active' => false,
                    'submitted_by_user_id' => $user->id,
                    'approval_status' => Nft::APPROVAL_PENDING,
                ]);
            }

            return $collection;
        });

        $request->session()->forget('checkout_order_id');
        $request->session()->put('creator_fee_collection_id', $collection->id);

        return redirect()
            ->route('checkout.index')
            ->with('status', 'Collection submitted. Complete the £80 creation fee checkout to send it for review.');
    }

    private function uniqueCollectionSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 1;

        while (Collection::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
    }

    private function uniqueNftSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 1;

        while (Nft::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
    }

    private function storeUploadedImageToCollectionFolder(
        \Illuminate\Http\UploadedFile $file,
        string $collectionFolder,
        string $namePrefix
    ): string {
        $extension = strtolower((string) $file->getClientOriginalExtension());
        if ($extension === '') {
            $extension = strtolower((string) $file->extension());
        }
        if ($extension === '') {
            $extension = 'png';
        }

        $filename = $namePrefix . '-' . Str::uuid() . '.' . $extension;
        $targetDirectory = public_path("images/nfts/{$collectionFolder}");
        if (! File::exists($targetDirectory)) {
            File::makeDirectory($targetDirectory, 0755, true);
        }

        $file->move($targetDirectory, $filename);

        return "/images/nfts/{$collectionFolder}/{$filename}";
    }
}
