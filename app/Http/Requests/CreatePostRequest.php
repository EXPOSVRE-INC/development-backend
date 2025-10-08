<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;


class CreatePostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true; // gate elsewhere if needed
    }

    public function rules(): array
    {
        return [
            // --- strings ------------------------------------------------------
            'title'                   => ['bail', 'required', 'string', 'max:255'],
            'description'             => ['nullable', 'string'],
            'currency'                => ['nullable', 'string', 'max:255'],
            'type'                    => ['nullable', 'string', 'max:255'],
            'typeOfPrice'             => ['nullable', 'string', 'max:255'],
            'status'                  => ['nullable', 'string', 'max:255'],
            'link'                    => ['nullable', 'string', 'max:255'],
            'subtitle'                => ['nullable', 'string', 'max:255'],
            'video_link'              => ['nullable', 'url', 'max:255'],

            // --- integers / ids ----------------------------------------------
            'collection_id'           => ['nullable', 'integer', 'min:1'],
            'song_id'                 => ['nullable', 'integer', 'min:1'],
            'owner_id'                => ['nullable', 'integer', 'min:1'],
            'views_count'             => ['nullable', 'integer', 'min:0'],
            'likes_count'             => ['nullable', 'integer', 'min:0'],
            'order_priority'          => ['nullable', 'integer', 'min:0'],
            'parent_id'               => ['nullable', 'integer', 'min:1'],

            // --- booleans (tinyint(1)) ---------------------------------------
            'collection_post'         => ['nullable', 'boolean'],
            'post_for_sale'           => ['nullable', 'boolean'],
            'unlimited_edition'       => ['nullable', 'boolean'],
            'physical_item'           => ['nullable', 'boolean'],
            'allow_to_comment'        => ['nullable', 'boolean'],
            'allow_views'             => ['nullable', 'boolean'],
            'exclusive_content'       => ['nullable', 'boolean'],
            'is_archived'             => ['nullable', 'boolean'],
            'nudity'                  => ['nullable', 'boolean'],
            'isFree'                  => ['nullable', 'boolean'],
            'shippingIncluded'        => ['nullable', 'boolean'],
            'ad'                      => ['nullable', 'boolean'],

            // --- numbers / money / percent -----------------------------------
            'limited_addition_number' => ['nullable', 'integer', 'min:0', 'max:2147483647'],
            'fixed_price'             => ['nullable', 'numeric', 'min:0'],
            'royalties_percentage'    => ['nullable', 'numeric', 'min:0', 'max:100'],
            'shippingPrice'           => ['nullable', 'numeric', 'min:0'],

            // --- dates --------------------------------------------------------
            'time_sale_from_date' => ['nullable', 'integer', 'min:0'],
            'time_sale_to_date'   => ['nullable', 'integer', 'min:0', 'gte:time_sale_from_date'],

            'publish_date'            => ['nullable', 'date'],

            // --- files (required at least one) -------------------------------
            'files'                   => ['required', 'array', 'min:1'],
            // adjust the inner shape to your payload. Examples:
            // 'files.*.id'            => ['required','string'],
            // 'files.*.url'           => ['required','url'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'                   => 'Title cannot be empty or whitespace.',
            'files.required'                   => "Post not created! At least one file attachment is required.",
            'files.min'                        => "Post not created! At least one file attachment is required.",
            'limited_addition_number.integer'  => 'The limited addition number must be an integer.',
            'limited_addition_number.max'      => 'The limited addition number is too large.',
            'limited_addition_number.min'      => 'The limited addition number cannot be negative.',
            'time_sale_to_date.after_or_equal' => 'Sale end time must be after or equal to sale start time.',
            'royalties_percentage.max'         => 'Royalties percentage cannot exceed 100.',
        ];
    }

    // Force JSON error shape: {"message":"Validation failed","errors":{...}}
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422)
        );
    }
}
