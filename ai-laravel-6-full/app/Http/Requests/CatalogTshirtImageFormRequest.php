<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class CatalogTshirtImageFormRequest extends FormRequest
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
        $imageRule = $this->isMethod('post')
            ? 'required|image|mimes:png,jpg,jpeg,webp|max:4096'
            : 'nullable|image|mimes:png,jpg,jpeg,webp|max:4096';

        return [
            'category_id' => 'nullable|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'image_file' => $imageRule,
        ];
    }
}
