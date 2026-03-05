<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SetSettingsRequest extends FormRequest 
{

    public function authorize(): bool 
	{
        return true;
    }

    public function rules(): array 
	{
        return [
            'name' => 'required|string',
            'className' => 'required|string',
            'orientation' => 'required|string',
            'toolBar' => 'required|boolean',
            'footer' => 'required|boolean',
            'footerFixed' => 'required|boolean',
            'isDarkMode' => 'required|string',
        ];
    }

    public function attributes(): array 
	{
        return [
            'className' => 'CSS class name',
            'toolBar' => 'toolbar',
            'footerFixed' => 'footer fixed',
            'isDarkMode' => 'dark mode',
        ];
    }
}
