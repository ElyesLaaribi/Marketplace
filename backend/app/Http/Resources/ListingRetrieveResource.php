<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ListingRetrieveResource extends JsonResource
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
            'image' => $this->image ? asset('storage/' . $this->image) : null,
        ];
    }
}
