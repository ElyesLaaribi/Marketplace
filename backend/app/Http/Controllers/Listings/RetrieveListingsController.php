<?php

namespace App\Http\Controllers\Listings;

use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Http\Resources\ListingRetrieveResource;

class RetrieveListingsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return ListingRetrieveResource::collection(Listing::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Listing $listing)
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
