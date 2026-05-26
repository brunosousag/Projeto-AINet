<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Illuminate\Support\Facades\Gate;

class CartConfirmationFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Gate::check('confirm-cart');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'student_number' => 'required|exists:students,number'
        ];
    }


    public function withValidator(Validator $validator): void
    {
        // This hook runs after the initial rules above are processed
        $validator->after(function (Validator $validator) {
            // Check if we already have errors from the standard rules
            // If you only want to run this when basic rules pass:
            if ($validator->errors()->any()) {
                return;
            }
            // Logic to check if the student number belongs to the currently authenticated user
            if ($this->user()?->type == 'S') {
                if ($this->student_number !== $this->user()?->student->number) {
                    $validator->errors()->add(
                        'student_number',
                        'Student number is not the same as the currently authenticated student number.'
                    );
                }
            }
        });
    }
}
