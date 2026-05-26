<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class DepartmentFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if (strtolower($this->getMethod()) == 'post') {
            return Gate::check('create', \App\Models\Department::class);
        } else {
            return Gate::check('update', $this->route('department'));
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'name_pt' => 'required|string|max:255',
        ];
        if (strtolower($this->getMethod()) == 'post') {
            // This will merge 2 arrays:
            // (adds the "abbreviation" rule to the $rules array)
            $rules = array_merge($rules, [
                'abbreviation' => 'required|string|max:20|unique:departments,abbreviation',
            ]);
        }
        return $rules;
    }
}
