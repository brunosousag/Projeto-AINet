<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class ColorFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('manage-catalog');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
        ];

        if ($this->isMethod('post')) {
            $rules['code'] = [
                'required',
                'string',
                'max:50',
                'regex:/^[0-9a-fA-F]{3,8}$/',
                Rule::unique('colors', 'code'),
            ];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'code.regex' => 'The color code must be a hexadecimal value without #.',
        ];
    }
}
