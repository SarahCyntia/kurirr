<?php

namespace App\Http\Controllers;

use App\Models\Rating;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    public function index()
    {
        return response()->json(Rating::with('pelanggan')->get(), 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'rating' => 'required|integer|min:1|max:5',
            'respon' => 'nullable|string',
        ]);

        $rating = Rating::create($request->all());
        return response()->json($rating, 201);
    }

    public function show(Rating $rating)
    {
        return response()->json($rating->load('customer'), 200);
    }

    public function destroy(Rating $rating)
    {
        $rating->delete();
        return response()->json(['message' => 'Rating deleted successfully'], 200);
    }
}
