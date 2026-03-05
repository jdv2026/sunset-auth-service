<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetEventLogsRequest extends FormRequest 
{

    public function authorize(): bool 
	{
        return true; 
    }


    public function rules(): array 
	{
        return [
            'limit' => 'sometimes|integer|min:1',
            'pageIndex' => 'sometimes|integer|min:0',
            'sortMetaColumn' => 'sometimes|string',
            'sortMetaDirection' => 'sometimes|string|in:asc,desc',
            'search' => 'sometimes|string',
        ];
    }

    public function attributes(): array 
	{
        return [
            'pageIndex' => 'page index',
            'sortMetaColumn' => 'sort column',
            'sortMetaDirection' => 'sort direction',
        ];
    }
}
