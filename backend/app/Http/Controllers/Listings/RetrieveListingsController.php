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
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        $query = Listing::with(['user', 'category']);
             
        $lat = $request->query('lat');
        $lng = $request->query('lng');
        $maxDistance = $request->query('distance', 50); 
    
        if ($lat && $lng) {
            $query->join('users', 'users.id', '=', 'listings.user_id');
    
            $hasLocationData = $query->clone()
                ->where(function($q) {
                    $q->whereNotNull('listings.latitude')
                    ->whereNotNull('listings.longitude')
                    ->orWhere(function($sq) {
                        $sq->whereNotNull('users.latitude')
                            ->whereNotNull('users.longitude');
                    });
                })->exists();
        
            if ($hasLocationData) {
                $query->selectRaw('listings.*')
                    ->selectRaw("(
                        6371 * acos(
                            cos(radians(?)) * cos(radians(COALESCE(listings.latitude, users.latitude))) * 
                            cos(radians(COALESCE(listings.longitude, users.longitude)) - radians(?)) + 
                            sin(radians(?)) * sin(radians(COALESCE(listings.latitude, users.latitude)))
                        )
                    ) AS distance", [$lat, $lng, $lat])
                    ->whereRaw('(
                        (listings.latitude IS NOT NULL AND listings.longitude IS NOT NULL) OR
                        (users.latitude IS NOT NULL AND users.longitude IS NOT NULL)
                    )')
                    ->having('distance', '<=', $maxDistance)
                    ->orderBy('distance', 'asc');
            } else {
                $query->selectRaw('listings.*');
            }
        }
    
    
    $listings = $query->get();
    
    return ListingClientResource::collection($listings);
}

    /**
     * Display the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Listing  $listing
     * @return \App\Http\Resources\ListingClientResource
     */
    public function show(Request $request, Listing $listing)
{
    $lat = $request->query('lat');
    $lng = $request->query('lng');
    
    if ($lat && $lng && 
        (($listing->latitude !== null && $listing->longitude !== null) || 
         ($listing->user->latitude !== null && $listing->user->longitude !== null))) {
        
        $targetLat = $listing->latitude !== null ? $listing->latitude : $listing->user->latitude;
        $targetLng = $listing->longitude !== null ? $listing->longitude : $listing->user->longitude;
        
        $earthRadius = 6371; 
        $dLat = deg2rad($targetLat - $lat);
        $dLon = deg2rad($targetLng - $lng);
        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat)) * cos(deg2rad($targetLat)) * 
             sin($dLon/2) * sin($dLon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        $distance = $earthRadius * $c;
        
        $listing->distance = round($distance, 1);
    }
    
    $listing->load(['user', 'category']);
    return new ListingClientResource($listing);
}
}