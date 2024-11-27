<?php

namespace App\Http\Requests\Vouchers;

use Illuminate\Foundation\Http\FormRequest;

class GetVouchersRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'page' => ['required', 'int', 'gt:0'],
            'paginate' => ['required', 'int', 'gt:0'],
            'series' => ['nullable', 'string'],
            'number' => ['nullable', 'string'],
            'voucher_type' => ['nullable', 'string'],
            'currency' => ['nullable', 'string'],
            'start_date' => ['required', 'date', 'before_or_equal:end_date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ];
    }

    public function messages(): array
    {
        return [
            'start_date.required' => 'El rango de fechas es obligatorio y debe incluir la fecha de inicio.',
            'end_date.required' => 'El rango de fechas es obligatorio y debe incluir la fecha de fin.',
        ];
    }

    /**
     * Obtener los datos de la consulta desde los parámetros de la URL.
     */
    public function filters(): array
    {
        return $this->only([
            'series',
            'number',
            'voucher_type',
            'currency',
            'start_date',
            'end_date',
        ]);
    }
}
