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
    public function index()
    {
        return ListingClientResource::collection(Listing::all());
    }

    
    /**
     * Display the specified resource.
     */
    public function show(Listing $listing)
    {
        //
    }

}