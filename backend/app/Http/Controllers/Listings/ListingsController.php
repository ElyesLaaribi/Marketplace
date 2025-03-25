<?php

namespace App\Http\Controllers\Listings;

use App\Models\Listing;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ListingResource;
use App\Http\Requests\StoreListingRequest;
use App\Http\Requests\UpdateListingRequest;


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
     * Update the specified resource in storage.
     */
    public function update(UpdateListingRequest $request, Listing $listing)
    {
        
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('listings', 'public');
            $data['image'] = $imagePath;
        }
    
        $listing->update($data);
    
        return ListingResource::make($listing);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Listing $listing)
    {
        $listing->delete();
        return response()->noContent();
    }
}
