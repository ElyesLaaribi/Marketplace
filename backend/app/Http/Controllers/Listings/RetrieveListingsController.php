<?php

namespace App\Http\Controllers\Listings;

use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use App\Http\Resources\ListingResource;
use App\Http\Requests\StoreListingRequest;
use App\Http\Requests\UpdateListingRequest;

class ListingsController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Listing::class); 
    }

    public function index()
    {
        $listings = Listing::where('user_id', auth()->id())
            ->with('category')
            ->latest()
            ->get();

        return ListingResource::collection($listings);
    }

    public function store(StoreListingRequest $request)
    {
        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imagePaths[] = $image->store('listings', 'public');
            }
        }

        $listing = Listing::create(
            array_merge(
                $request->validated(),
                [
                    'user_id' => auth()->id(),
                    'images' => json_encode($imagePaths) 
                ]
            )
        );

        return ListingResource::make($listing->load('category'));
    }

    public function show(Listing $listing)
    {
        return ListingResource::make($listing->load('category'));
    }

    public function update(UpdateListingRequest $request, Listing $listing)
    {
    $data = $request->validated();

    $existingImages = $listing->images ?? [];

    if ($request->has('deleted_images')) {
        $deletedImages = $request->input('deleted_images');

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
        $listing->update($data);

        return ListingResource::make($listing->load('category'));
    }
    public function destroy(Listing $listing)
    {
        $images = json_decode($listing->images, true) ?? [];
        foreach ($images as $image) {
            Storage::disk('public')->delete($image);
        }

        $listing->delete();
        return response()->noContent();
    }
}