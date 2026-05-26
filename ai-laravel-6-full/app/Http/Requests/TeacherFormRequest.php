<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Gate;

class TeacherFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if (strtolower($this->getMethod()) == 'post') {
            return Gate::check('create', \App\Models\Teacher::class);
        } else {
            return Gate::check('update', $this->route('teacher'));
        }
    }

    protected function prepareForValidation(): void
    {
        // Adds the user information (from the teacher route parameter) to the Request
        // if it is a post, user = null
        if (strtolower($this->getMethod()) == 'post') {
            $this->merge([
                'user' => null,
            ]);
        } else {
            $this->merge([
                'user' => $this->route('teacher')->user,
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
            'department' => 'required|max:20|exists:departments,abbreviation',
            'office' => 'required|string|max:50',
            'extension' => 'required|string|max:20',
            'locker' => 'required|string|max:20',
            'admin' => 'required|boolean',
            'photo_file' => 'sometimes|image|max:4096', // maxsize = 4Mb
        ];
    }
}
