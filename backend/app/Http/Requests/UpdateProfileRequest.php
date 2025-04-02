<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
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

        $UserId = $this->user() ? $this->user()->id : 'Null';
        
        return [
            'name' => 'sometimes|string',
            'email' => 'sometimes|email|unique:users,email,'  . $UserId . ',id',
            'phone' => 'sometimes|numeric',
            'cin' => 'sometimes|digits:8|unique:users,cin,' . $UserId . ',id',
            'country' => 'sometimes|string',
            'city' => 'sometimes|string',
        ];
    }
}
