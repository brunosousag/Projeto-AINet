<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class PersonalTshirtImageFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('customer');
    }

    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'image_file' => [
                $this->isMethod('post') ? 'required' : 'nullable',
                'image',
                'mimes:png,jpg,jpeg,webp',
                'max:4096',
            ],
            'preview_top' => 'required|integer|min:0|max:70',
            'preview_width' => 'required|integer|min:10|max:90',
            'preview_height' => 'required|integer|min:10|max:90',
            'preview_opacity' => 'required|integer|min:10|max:100',
        ];
    }
}
