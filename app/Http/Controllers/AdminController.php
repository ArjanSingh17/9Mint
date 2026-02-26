<?php

namespace App\Http\Controllers;
use App\Models\Collection;
use App\Models\Nft;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentIntent;
use App\Models\SalesHistory;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    public function index()
    {
        $pendingCollectionsCount = Collection::query()
            ->where('approval_status', Collection::APPROVAL_PENDING)
            ->count();

        return view('admin.dashboard', compact('pendingCollectionsCount'));
    }

    public function approvals()
    {
        $pendingCollections = Collection::query()
            ->where('approval_status', Collection::APPROVAL_PENDING)
            ->withCount('nfts')
            ->orderBy('created_at')
            ->get();

        return view('admin.approvals-index', compact('pendingCollections'));
    }

    public function reviewCollection(Collection $collection)
    {
        $collection->load(['nfts.tokens.owner', 'nfts.tokens.listing']);

        if ($collection->approval_status !== Collection::APPROVAL_PENDING) {
            return redirect()
                ->route('admin.approvals.index')
                ->with('error', 'Only pending submissions can be reviewed on this page.');
        }

        return view('admin.approvals-show', compact('collection'));
    }

    public function users()
    {
        // Fetch all users from the database
        $users = User::orderBy('id')->get();

        return view('admin.users', compact('users'));
    }

    public function banUser($id)
    {
        $actor = auth()->user();
        $user = User::findOrFail($id);

        if ($user->id === $actor->id) {
            return redirect()->back()->with('error', 'You cannot ban yourself.');
        }

        if ($user->isSuperAdmin()) {
            return redirect()->back()->with('error', 'The 9Mint superadmin account cannot be banned.');
        }

        if ($user->isBanned()) {
            return redirect()->back()->with('error', 'User is already banned.');
        }

        $user->update([
            'banned_at' => now(),
            'banned_by' => $actor->id,
        ]);

        return redirect()->back()->with('success', 'User banned successfully.');
    }

    public function deleteUser($id)
    {
        $actor = auth()->user();
        $user = User::findOrFail($id);
        $confirmation = (string) request('confirm_username');

        if ($user->id === $actor->id) {
            return redirect()->back()->with('error', 'You cannot delete yourself.');
        }

        if ($user->isSuperAdmin()) {
            return redirect()->back()->with('error', 'The 9Mint superadmin account cannot be deleted.');
        }

        if (! $user->isBanned()) {
            return redirect()->back()->with('error', 'You must ban a user before deleting their account.');
        }

        if ($confirmation !== $user->name) {
            return redirect()->back()->with('error', 'Username confirmation does not match. Account not deleted.');
        }

        $user->delete();

        return redirect()->back()->with('success', 'User deleted successfully.');
    }

    // Show the form with current user data
    public function editUser($id)
    {
        $user = User::findOrFail($id);
        return view('admin.users_edit', compact('user'));
    }

    // Process the update
    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // Validate the inputs
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id, // Allow their CURRENT email
            'role' => 'required|in:admin,user,customer',
        ]);

        $actor = auth()->user();
        $targetRole = $request->input('role');
        if ($targetRole === 'customer') {
            $targetRole = 'user';
        }

        $isPromotionToAdmin = $targetRole === 'admin' && $user->role !== 'admin';
        if ($isPromotionToAdmin && ! $actor->isSuperAdmin()) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors([
                    'role' => 'Only the 9Mint superadmin can make another user an admin.',
                ]);
        }

        if ($user->isSuperAdmin() && $targetRole !== 'admin') {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors([
                    'role' => 'The 9Mint superadmin role cannot be changed.',
                ]);
        }

        // Update the user
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $targetRole,
        ]);

        return redirect()->route('admin.users')->with('success', 'User updated successfully.');
    }

    public function inventory()
{
    // Fetch all NFTs and their collection info
    $nfts = \App\Models\Nft::with('collection')->get();

    return view('admin.inventory', compact('nfts'));
}

    public function orders()
    {
        $saleTransactions = OrderItem::query()
            ->with(['order.user', 'listing.seller', 'token.nft'])
            ->whereHas('order', function ($query) {
                $query->where('status', 'paid');
            })
            ->get();

        $creatorFeePayments = PaymentIntent::query()
            ->with('order.user')
            ->where('status', 'captured')
            ->where('metadata->context', 'creator_fee')
            ->get();

        $creatorFeeRefunds = WalletTransaction::query()
            ->with(['user', 'order'])
            ->where('type', 'credit')
            ->where('metadata->source', 'creator_fee_hold_release')
            ->get();

        $transactions = collect();

        foreach ($saleTransactions as $item) {
            $order = $item->order;
            $listing = $item->listing;
            $token = $item->token;
            $nft = $token?->nft ?? $listing?->token?->nft;

            $transactions->push([
                'occurred_at' => $order?->placed_at ?? $item->created_at,
                'type' => 'NFT Sale',
                'reference' => 'ORDER-' . ($order?->id ?? 'N/A'),
                'item' => $nft?->name ?? ('Listing #' . ($item->listing_id ?? 'N/A')),
                'ids' => [
                    'order' => $order?->id,
                    'listing' => $item->listing_id,
                    'nft' => $nft?->id,
                    'token' => $item->token_id,
                    'edition' => $token?->serial_number,
                ],
                'buyer' => $order?->user?->name ?? 'Unknown',
                'seller' => $listing?->seller?->name ?? 'Unknown',
                'amount_label' => strtoupper((string) ($item->pay_currency ?? $order?->pay_currency ?? 'GBP')) . ' ' . number_format((float) ($item->pay_unit_amount ?? 0) * (int) ($item->quantity ?? 1), 2),
                'status' => $order?->status ?? 'unknown',
            ]);
        }

        foreach ($creatorFeePayments as $intent) {
            $order = $intent->order;
            $meta = (array) ($intent->metadata ?? []);
            $collectionId = $meta['collection_id'] ?? null;
            $amount = (float) ($meta['amount'] ?? $order?->pay_total_amount ?? 0);
            $currency = strtoupper((string) ($meta['currency'] ?? $order?->pay_currency ?? 'GBP'));

            $transactions->push([
                'occurred_at' => $order?->placed_at ?? $intent->created_at,
                'type' => 'Collection Creation Fee',
                'reference' => 'PI-' . $intent->id,
                'item' => 'Collection creation fee' . ($collectionId ? ' (Collection #' . $collectionId . ')' : ''),
                'ids' => [
                    'order' => $order?->id,
                    'collection' => $collectionId,
                ],
                'buyer' => $order?->user?->name ?? 'Unknown',
                'seller' => '9Mint',
                'amount_label' => $currency . ' ' . number_format($amount, 2),
                'status' => $intent->status,
            ]);
        }

        foreach ($creatorFeeRefunds as $refund) {
            $meta = (array) ($refund->metadata ?? []);
            $collectionId = $meta['collection_id'] ?? null;

            $transactions->push([
                'occurred_at' => $refund->created_at,
                'type' => 'Collection Fee Refund',
                'reference' => 'WTX-' . $refund->id,
                'item' => 'Creator fee refund' . ($collectionId ? ' (Collection #' . $collectionId . ')' : ''),
                'ids' => [
                    'order' => $refund->order_id,
                    'wallet_tx' => $refund->id,
                    'collection' => $collectionId,
                ],
                'buyer' => $refund->user?->name ?? 'Unknown',
                'seller' => '9Mint',
                'amount_label' => strtoupper((string) ($refund->currency ?? 'GBP')) . ' ' . number_format((float) $refund->amount, 2),
                'status' => 'refunded',
            ]);
        }

        $transactions = $transactions
            ->sortByDesc(fn ($row) => optional($row['occurred_at'])->getTimestamp() ?? 0)
            ->values();

        return view('admin.orders', compact('transactions'));
    }

    public function approveCollection(Collection $collection)
    {
        if ($collection->approval_status === Collection::APPROVAL_APPROVED) {
            return back()->with('status', 'Collection is already approved.');
        }
        if ($collection->submitted_by_user_id && $collection->creation_fee_payment_state === 'unpaid') {
            return back()->with('error', 'Creator fee has not been paid yet.');
        }

        DB::transaction(function () use ($collection) {
            if ($collection->creation_fee_payment_state === 'held_wallet' && filled($collection->creation_fee_hold_reference)) {
                app(WalletService::class)->captureHold((string) $collection->creation_fee_hold_reference);
                $collection->creation_fee_payment_state = 'consumed';
            } elseif ($collection->creation_fee_payment_state === 'paid_unheld') {
                $collection->creation_fee_payment_state = 'consumed';
            }

            $collection->approval_status = Collection::APPROVAL_APPROVED;
            $collection->is_public = true;
            $collection->approved_at = now();
            $collection->approved_by = auth()->id();
            $collection->rejected_at = null;
            $collection->rejected_by = null;
            $collection->rejection_reason = null;
            $collection->save();

            Nft::query()
                ->where('collection_id', $collection->id)
                ->update([
                    'approval_status' => Nft::APPROVAL_APPROVED,
                    'approved_at' => now(),
                    'approved_by' => auth()->id(),
                    'rejected_at' => null,
                    'rejected_by' => null,
                    'rejection_reason' => null,
                    'is_active' => true,
                ]);
        });

        return redirect()
            ->route('admin.approvals.index')
            ->with('status', 'Collection approved.');
    }

    public function rejectCollection(Request $request, Collection $collection)
    {
        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:2000'],
        ]);

        return $this->rejectWholeCollectionSubmission($collection, $data['reason'] ?? null);
    }

    private function rejectWholeCollectionSubmission(Collection $collection, ?string $reason = null)
    {
        $uploadDirectory = public_path('images/nfts/' . $collection->uploadFolderName());
        $manualRefundRequired = false;

        DB::transaction(function () use ($collection, &$manualRefundRequired) {
            if ($collection->creation_fee_payment_state === 'held_wallet' && filled($collection->creation_fee_hold_reference)) {
                app(WalletService::class)->releaseHold((string) $collection->creation_fee_hold_reference);
            } elseif (in_array($collection->creation_fee_payment_state, ['paid_unheld', 'consumed'], true)) {
                $manualRefundRequired = true;
            }

            Nft::query()
                ->where('collection_id', $collection->id)
                ->delete();

            $collection->delete();
        });

        $baseUploadsPath = realpath(public_path('images/nfts'));
        $targetPath = realpath($uploadDirectory) ?: $uploadDirectory;
        if ($baseUploadsPath && str_starts_with(str_replace('\\', '/', $targetPath), str_replace('\\', '/', $baseUploadsPath))) {
            File::deleteDirectory($uploadDirectory);
        }

        if ($manualRefundRequired) {
            Log::warning('Manual creator fee refund required after rejected collection.', [
                'collection_id' => $collection->id,
                'creation_fee_order_id' => $collection->creation_fee_order_id,
                'creation_fee_provider' => $collection->creation_fee_provider,
                'rejection_reason' => $reason,
            ]);

            return redirect()
                ->route('admin.approvals.index')
                ->with('status', 'Collection rejected and removed. Manual refund is required.');
        }

        return redirect()
            ->route('admin.approvals.index')
            ->with('status', 'Collection rejected and removed.');
    }

    public function approveNft(Nft $nft)
    {
        return back()->with('error', 'Approve the full collection submission from the Creator Approvals page.');
    }

    public function rejectNft(Request $request, Nft $nft)
    {
        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:2000'],
        ]);

        if (! $nft->collection) {
            return back()->with('error', 'NFT has no parent collection. Unable to reject submission.');
        }

        return $this->rejectWholeCollectionSubmission(
            $nft->collection,
            $data['reason'] ?? 'NFT failed moderation. Entire collection submission rejected.'
        );
    }
}
