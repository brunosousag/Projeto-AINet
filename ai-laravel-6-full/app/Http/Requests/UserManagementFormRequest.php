<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UserManagementFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('admin');
    }

    public function rules(): array
    {
        $managedUser = $this->route('user');
        $userId = $managedUser instanceof User ? $managedUser->id : null;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class, 'email')->ignore($userId),
            ],
            'password' => ['nullable', 'string', 'min:3', 'confirmed'],
            'user_type' => ['required', Rule::in(['C', 'F', 'A'])],
            'gender' => ['required', Rule::in(['M', 'F'])],
            'blocked' => ['nullable', 'boolean'],
            'photo_file' => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:4096'],
            'nif' => ['nullable', 'digits:9'],
            'address' => ['nullable', 'string', 'max:2000'],
            'default_payment_type' => ['nullable', Rule::in(['Visa', 'PayPal', 'MB WAY'])],
            'default_payment_ref' => ['nullable', 'string', 'max:255', 'required_with:default_payment_type'],
        ];
    }
}
