<?php

namespace Modules\Sale\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MarkLostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'lost_reason' => 'required|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'lost_reason.required' => 'A lost reason is required.',
        ];
    }
}
