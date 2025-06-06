<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreListingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'images' => 'required|array|min:1',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp,svg',
            'category_id' => 'required|exists:categories,id',
            'status' => 'boolean',
            'address' => 'required|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180'
        ];

    }
     /**
     * Custom error messages (optional)
     */
    public function messages(): array
    {
        return [
            'image.required' => 'An image is required',
            'image.image' => 'The file must be an image',
            'image.mimes' => 'Image must be a file of type: jpeg, png, jpg, gif',
            'category_id.exists' => 'The selected category does not exist'
        ];
    }
}
