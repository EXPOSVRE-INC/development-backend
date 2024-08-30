<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'query' => 'string|min:3',
            'type' => 'string',
            'status' => 'array',
            'post_type' => 'array',
            'currency' => 'string',
            'interests' => 'array',
            'min_price' => 'string',
            'max_price' => 'string',
        ];
    }
}
