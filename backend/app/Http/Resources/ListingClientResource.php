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
        ];
    }
}
