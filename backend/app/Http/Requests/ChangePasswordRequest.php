<?php

namespace App\Http\Requests;

use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
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
            'current_password' => 'required|string',
            'password' => 'required|string|confirmed|min:8'
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $user = auth()->user();
    
            if (!$user) {
                $validator->errors()->add('auth', 'User is not authenticated.');
                return;
            }
    
            if (!Hash::check($this->input('current_password'), $user->password)) {
                $validator->errors()->add('current_password', 'The current password is incorrect.');
            }
        });
    }
}
