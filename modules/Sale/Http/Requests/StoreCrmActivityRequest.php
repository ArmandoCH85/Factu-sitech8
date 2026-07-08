<?php

namespace Modules\Sale\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCrmActivityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => 'required|in:note,call,task,status_change,quotation',
            'sale_opportunity_id' => 'required|exists:tenant.sale_opportunities,id',
            'description' => 'required|string|max:65535',
            'due_date' => 'nullable|date|required_if:type,task',
            'payload' => 'nullable|array',
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'The activity type is required.',
            'type.in' => 'The activity type :input is not supported.',
            'sale_opportunity_id.required' => 'A sale opportunity must be selected.',
            'sale_opportunity_id.exists' => 'The selected opportunity does not exist.',
            'due_date.required_if' => 'Tasks must have a due date.',
        ];
    }
}
