<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateListingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'price' => 'sometimes|numeric|min:0',
            'description' => 'sometimes|string',
            'images' => 'sometimes|array|min:1',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp,svg',
            'deleted_images' => 'sometimes|array',
            'deleted_images.*' => 'string',
            'category_id' => 'sometimes|exists:categories,id',
            'status' => 'sometimes|boolean'
        ];
    }
}