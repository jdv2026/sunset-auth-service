<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentRequest extends FormRequest 
{

    public function authorize(): bool 
	{
        return true; 
    }

    public function rules(): array 
	{
        return [
            'plan' => 'required|integer',
            'amount' => 'required|numeric|min:0',
            'discount' => 'required|numeric|min:0|max:100',
            'id' => 'required|integer',
        ];
    }

    public function attributes(): array 
	{
        return [
            'id' => 'payment ID',
        ];
    }
}
