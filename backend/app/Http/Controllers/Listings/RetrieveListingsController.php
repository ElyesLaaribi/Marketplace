<?php

namespace App\Http\Controllers\Listings;

use App\Models\Listing;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ListingClientResource;

class RetrieveListingsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 12);
        $page = $request->input('page', 1);
        
        $query = Listing::with(['user', 'category']);
        
        // Apply category filter
        if ($request->has('category') && $request->category) {
            $query->whereHas('category', function($q) use ($request) {
                $q->where('cat_title', $request->category);
            });
        }
        
        // Apply search filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Get price range before applying price filter
        $priceRange = [
            'min' => $query->min('price'),
            'max' => $query->max('price')
        ];

        // Apply price range filter
        if ($request->has('min_price') && $request->min_price) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->has('max_price') && $request->max_price) {
            $query->where('price', '<=', $request->max_price);
        }

        // Get total count before pagination
        $totalCount = $query->count();
        
        // Get paginated results
        $listings = $query->paginate($perPage, ['*'], 'page', $page);
        
        return response()->json([
            'data' => ListingClientResource::collection($listings),
            'total' => $totalCount,
            'current_page' => $listings->currentPage(),
            'per_page' => $listings->perPage(),
            'last_page' => $listings->lastPage(),
            'price_range' => $priceRange
        ]);
    }

    public function getCategoryProducts(Request $request, $category)
    {
        $perPage = $request->input('per_page', 12);
        $page = $request->input('page', 1);
        
        $query = Listing::with(['user', 'category'])
            ->whereHas('category', function($q) use ($category) {
                $q->where('cat_title', $category);
            });

        // Apply price range filter
        if ($request->has('min_price') && $request->min_price) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->has('max_price') && $request->max_price) {
            $query->where('price', '<=', $request->max_price);
        }

        // Get total count before pagination
        $totalCount = $query->count();
        
        // Get paginated results
        $listings = $query->paginate($perPage, ['*'], 'page', $page);
        
        return response()->json([
            'data' => ListingClientResource::collection($listings),
            'total' => $totalCount,
            'current_page' => $listings->currentPage(),
            'per_page' => $listings->perPage(),
            'last_page' => $listings->lastPage(),
        ]);
    }

    
    /**
     * Display the specified resource.
     */
    public function show(Listing $listing)
    {
        $listing->load(['user', 'category']); 
        return ListingClientResource::make($listing);
    }

}