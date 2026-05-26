<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Gate;

class StudentFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if (strtolower($this->getMethod()) == 'post') {
            return Gate::check('create', \App\Models\Student::class);
        } else {
            return Gate::check('update', $this->route('student'));
        }
    }

    protected function prepareForValidation(): void
    {
        // Adds the user information (from the student route parameter) to the Request
        // if it is a post, user = null
        if (strtolower($this->getMethod()) == 'post') {
            $this->merge([
                'user' => null,
            ]);
        } else {
            $this->merge([
                'user' => $this->route('student')->user,
            ]);
        }
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($this->user?->id)
            ],
            'gender' => 'required|in:M,F',
            'course' => 'required|max:20|exists:courses,abbreviation',
            'number' => 'required|integer|between:1000000,9999999',
            'photo_file' => 'sometimes|image|max:4096', // maxsize = 4Mb
        ];
    }
}
