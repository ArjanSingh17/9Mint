<?php

namespace App\Http\Controllers;

use App\Models\Nft;
use Illuminate\Support\Facades\DB;

class TrendingController extends Controller
{
    public function index()
    {
        $sevenDaysAgo = now()->subDays(7);

        $trendingNfts = Nft::query()
            ->select([
                'nfts.id',
                'nfts.name',
                'nfts.slug',
                'nfts.image_url',
                'nfts.collection_id',
                DB::raw('COUNT(sales_histories.id) as sales_count'),
            ])
            ->join('nft_tokens', 'nft_tokens.nft_id', '=', 'nfts.id')
            ->join('sales_histories', 'sales_histories.token_id', '=', 'nft_tokens.id')
            ->where('sales_histories.sold_at', '>=', $sevenDaysAgo)
            ->where('nfts.is_active', true)
            ->groupBy('nfts.id', 'nfts.name', 'nfts.slug', 'nfts.image_url', 'nfts.collection_id')
            ->orderByDesc('sales_count')
            ->limit(50)
            ->with('collection')
            ->get();

        return view('trending', compact('trendingNfts'));
    }
}
