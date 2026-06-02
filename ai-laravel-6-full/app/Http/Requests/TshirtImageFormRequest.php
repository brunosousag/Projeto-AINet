<?php

namespace App\Http\Requests;

use App\Models\TshirtImage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class TshirtImageFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        $tshirtImage = $this->route('tshirtImage');

        return $tshirtImage instanceof TshirtImage
            ? Gate::allows('update', $tshirtImage)
            : Gate::allows('create', TshirtImage::class);
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
            'preview_top' => 'required|integer|min:0|max:70',
            'preview_width' => 'required|integer|min:10|max:90',
            'preview_height' => 'required|integer|min:10|max:90',
            'preview_opacity' => 'required|integer|min:10|max:100',
        ];
    }
}
