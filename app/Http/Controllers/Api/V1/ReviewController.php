<?php

namespace App\Http\Controllers\Api\V1;


use App\Http\Controllers\Controller;  
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    public function store(Request $request)
    {
        DB::table('reviews')->insert([
            'name' => $request->name,
            'review' => $request->review,
            'rating' => $request->rating,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'message' => 'Review added successfully'
        ]);
    }

    public function highRated()
    {
        $reviews = DB::table('reviews')
            ->where('rating', '>=', 4)
            ->get();

        return response()->json($reviews);
    }
}
