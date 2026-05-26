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
            'image_file' => 'required|image|mimes:png,jpg,jpeg,webp|max:4096',
        ];
    }
}
