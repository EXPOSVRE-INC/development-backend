<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchPostRequest extends FormRequest
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
//            'query' => 'string|min:3',
            'status' => 'array',
            'types' => 'array',
            'currency' => 'string|required',
            'price_min' => 'integer|required',
            'price_max' => 'integer|required',
            'interests' => 'array'
        ];
    }
}
