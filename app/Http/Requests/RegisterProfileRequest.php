<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterProfileRequest extends FormRequest
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
            'firstName' => 'string',
            'lastName' => 'string',
            'username' => 'string',
//            'birthDate' => 'string',
            'jobTitle' => 'string|nullable',
            'jobDescription' => 'string',
            'website' => 'string|nullable',
            'instagram' => 'string|nullable',
            'twitter' => 'string|nullable',
        ];
    }
}
