<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminLoginRequest extends FormRequest 
{

	public function authorize(): bool 
	{
        return true;
    }

    public function rules(): array 
	{
        return [
            'username' => 'required|string|min:3|max:50',
            'password' => 'required|string|min:6|max:50',
        ];
    }

    public function messages(): array 
	{
        return [
            'username.required' => 'Username is required.',
            'password.required' => 'Password is required.',
        ];
    }
}
