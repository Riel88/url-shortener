<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUrlRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'url' => ['required', 'string', 'url', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'url.required' => 'URL is required.',
            'url.url'      => 'The provided string is not a valid URL.',
            'url.max'      => 'URL must not exceed 2048 characters.',
        ];
    }
}