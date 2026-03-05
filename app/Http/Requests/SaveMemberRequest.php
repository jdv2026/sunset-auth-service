<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class SaveMemberRequest extends FormRequest 
{

    public function authorize(): bool 
	{
        return true; 
    }

    public function rules(): array 
	{
        return [
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'height' => 'required|integer|min:0|max:600',
            'phone' => 'required|string|max:25',
            'dob' => 'required|date|before:' . Carbon::now()->subYears(15)->toDateString(),
            'address' => 'required|string|max:50',
            'file' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp|max:5120', 
            'username' => ['required','string','max:15', Rule::unique('users', 'username')],
            'password' => 'required|string|min:8|max:20',
			'isStaff' => 'sometimes|required|in:true,false,0,1',
			'isAdmin' => 'sometimes|required|in:true,false,0,1',
        ];
    }

    public function attributes(): array 
	{
        return [
            'dob' => 'date of birth',
            'isAdmin' => 'admin flag',
            'isStaff' => 'staff flag',
        ];
    }
}
