<?php

namespace App\Http\Controllers\Listings;

use App\Models\Listing;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ListingResource;
use App\Http\Requests\StoreListingRequest;


class ListingsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return ListingResource::collection(Listing::all());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreListingRequest $request)
    {
        $imagePath = $request->hasFile('image') 
            ? $request->file('image')->store('listings', 'public') 
            : null;

        $listing = Listing::create(
            array_merge(
                $request->validated(), 
                ['image' => $imagePath]
            )
        );

        return ListingResource::make($listing);
    }

    /**
     * Display the specified resource.
     */
    public function show(Listing $listing)
    {
        return ListingResource::make($listing);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Listing $listing)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Listing $listing)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Listing $listing)
    {
        //
    }
}
