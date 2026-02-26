<?php

namespace App\Http\Controllers;

use App\Models\Nft;
use App\Models\NftReview;
use Illuminate\Http\Request;

class NftReviewController extends Controller
{
    public function store(Request $request, Nft $nft)
    {
        $alreadyReviewed = NftReview::where('nft_id', $nft->id)
            ->where('user_id', auth()->id())
            ->exists();

        if ($alreadyReviewed) {
            return back()->with('error', 'You have already reviewed this NFT.');
        }

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review_text' => 'required|string|min:5',
        ]);

        NftReview::create([
            'nft_id'      => $nft->id,
            'user_id'     => auth()->id(),
            'rating'      => $request->rating,
            'review_text' => $request->review_text,
        ]);

        return redirect()->route('nfts.show', $nft->slug)
    ->with('success', 'Review updated successfully!');

    }

    public function update(Request $request, Nft $nft)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review_text' => 'required|string|min:5',
        ]);

        $review = NftReview::where('nft_id', $nft->id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $review->update([
            'rating' => $request->rating,
            'review_text' => $request->review_text,
        ]);

        return back()->with('success', 'Review updated successfully!');
    }
}