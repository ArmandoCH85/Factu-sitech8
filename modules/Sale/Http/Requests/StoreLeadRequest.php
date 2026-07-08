<?php

namespace Modules\Sale\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class StoreLeadRequest
 *
 * Validation for POST /crm/leads (create a new lead / opportunity).
 * Part of fase 2 of crm-comercial-kiss.
 */
class StoreLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => 'required|integer|exists:tenant.persons,id',
            'detail' => 'nullable|string|max:600',
            'total' => 'nullable|numeric|min:0|max:99999999.99',
            'crm_source' => 'nullable|string|in:web,referral,cold_call,import,other',
            'expected_close_date' => 'nullable|date|after_or_equal:today',
            'item_ids' => 'nullable|array',
            'item_ids.*' => 'integer|exists:tenant.items,id',
        ];
    }

    public function messages(): array
    {
        return [
            'customer_id.required' => 'Debe seleccionar un cliente.',
            'customer_id.exists' => 'El cliente seleccionado no existe.',
            'detail.required' => 'La descripción es obligatoria.',
            'total.numeric' => 'El monto debe ser numérico.',
            'total.min' => 'El monto no puede ser negativo.',
            'crm_source.in' => 'La fuente debe ser: web, referral, cold_call, import u other.',
            'expected_close_date.date' => 'La fecha de cierre esperada no es válida.',
            'expected_close_date.after_or_equal' => 'La fecha de cierre no puede ser en el pasado.',
        ];
    }
}