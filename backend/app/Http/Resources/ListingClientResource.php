<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ListingClientResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return[
            'name' => $this->name,
            'price' => $this->price,
            'description' => $this->description,
            'images' => collect($this->images ?? [])->map(function ($image) {
                return $image ? asset('storage/' . $image) : null;
            })->filter(),
            'category_id' => $this->category_id,
            'cat_title' => optional($this->category)->cat_title,
            'user_id' => $this->user_id,
            'user_name' => optional($this->user)->name,
            'location' => [
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
                'address' => $this->address,
                'city' => $this->city,
            ],
            'distance' => isset($this->distance) ? round($this->distance, 1) : null,
            'created_at' => $this->created_at,
        ];
    }
}
