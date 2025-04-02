<?php

namespace App\Http\Controllers\Listings;

use App\Models\Listing;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ListingResource;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\StoreListingRequest;
use App\Http\Requests\UpdateListingRequest;

class ListingsController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Listing::class);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        $listings = Listing::where('user_id', auth()->id())
            ->with('category')
            ->latest();

        // If location filtering is requested
        if ($request->has('nearby') && $request->has('lat') && $request->has('lng')) {
            $lat = $request->input('lat');
            $lng = $request->input('lng');
            $radius = $request->input('radius', 50); // Default 50km radius
            
            $listings = $listings->selectRaw("*, (6371 * acos(
                cos(radians(?)) * cos(radians(latitude)) * 
                cos(radians(longitude) - radians(?)) + 
                sin(radians(?)) * sin(radians(latitude))
            )) AS distance", [$lat, $lng, $lat])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->having('distance', '<=', $radius)
            ->orderBy('distance');
        }

        return ListingResource::collection($listings->get());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreListingRequest  $request
     * @return \App\Http\Resources\ListingResource
     */
    public function store(StoreListingRequest $request)
    {
        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
            $imagePaths[] = $image->store('listings', 'public');
            }
        }

        // Create listing with all the data
        $listing = Listing::create([
            ...$request->validated(),
            'user_id' => auth()->id(),
            'images' => $imagePaths,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);

        return ListingResource::make($listing->load('category'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Listing  $listing
     * @return \App\Http\Resources\ListingResource
     */
    public function show(Listing $listing)
    {
        return ListingResource::make($listing->load('category'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateListingRequest  $request
     * @param  \App\Models\Listing  $listing
     * @return \App\Http\Resources\ListingResource
     */
    public function update(UpdateListingRequest $request, Listing $listing)
{
    $data = $request->validated();
    $existingImages = $listing->images ?? [];

    if ($request->has('deleted_images')) {
        $deletedImages = array_map(function ($imageUrl) {
            return str_replace(asset('storage/') . '/', '', $imageUrl);
        }, $request->input('deleted_images'));

        foreach ($deletedImages as $imagePath) {
            if (Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }
        }

        $existingImages = array_values(array_diff($existingImages, $deletedImages));
    }

    if ($request->hasFile('images')) {
        $newImagePaths = [];
        foreach ($request->file('images') as $image) {
            $newImagePaths[] = $image->store('listings', 'public');
        }
        $existingImages = array_merge($existingImages, $newImagePaths);
    }

    $data['images'] = $existingImages;
    
    // If location data is provided in the request, use it
    if ($request->has('latitude') && $request->has('longitude')) {
        $data['latitude'] = $request->latitude;
        $data['longitude'] = $request->longitude;
    }
    // If no location data is provided but we don't have it stored yet, 
    // we could set default values here if needed
    
    $listing->update($data);

    return ListingResource::make($listing->load('category'));
}

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Listing  $listing
     * @return \Illuminate\Http\Response
     */
    public function destroy(Listing $listing)
    {
        if ($listing->images) {
            foreach ($listing->images as $imagePath) {
                if (Storage::disk('public')->exists($imagePath)) {
                    Storage::disk('public')->delete($imagePath);
                }
            }
        }
        
        $listing->delete();
        
        return response()->json(['message' => 'Listing deleted successfully']);
    }
}