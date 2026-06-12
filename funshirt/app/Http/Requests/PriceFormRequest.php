<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class PriceFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('manage-catalog');
    }

    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'unit_price_catalog' => 'required|numeric|min:0|max:99999.99',
            'unit_price_own' => 'required|numeric|min:0|max:99999.99',
            'unit_price_catalog_discount' => 'required|numeric|min:0|max:99999.99',
            'unit_price_own_discount' => 'required|numeric|min:0|max:99999.99',
            'qty_discount' => 'required|integer|min:1|max:999',
        ];
    }
}
