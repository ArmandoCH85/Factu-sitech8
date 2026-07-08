<?php

namespace Modules\Sale\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChangeStageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'stage_code' => 'required|string|exists:tenant.crm_pipeline_stages,code',
            'lost_reason' => 'required_if:stage_code,lost|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'stage_code.required' => 'A stage code is required.',
            'stage_code.exists' => 'The selected stage code is invalid.',
            'lost_reason.required_if' => 'A lost reason is required when marking an opportunity as lost.',
        ];
    }
}
