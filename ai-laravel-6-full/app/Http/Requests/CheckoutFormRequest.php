<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Validator;

class CheckoutFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('checkout');
    }

    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'nif' => 'required|digits:9',
            'address' => 'required|string|max:1000',
            'payment_type' => 'required|in:Visa,PayPal,MB WAY',
            'payment_ref' => 'required|string|max:255',
            'notes' => 'nullable|string|max:2000',
            'save_defaults' => 'nullable|boolean',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $paymentType = $this->string('payment_type')->toString();
                $paymentRef = $this->string('payment_ref')->toString();

                if ($paymentType === 'Visa' && ! preg_match('/^\d{16}$/', $paymentRef)) {
                    $validator->errors()->add('payment_ref', 'Visa reference must have exactly 16 digits.');
                }

                if ($paymentType === 'MB WAY' && ! preg_match('/^9\d{8}$/', $paymentRef)) {
                    $validator->errors()->add('payment_ref', 'MB WAY reference must be a valid Portuguese phone number.');
                }

                if ($paymentType === 'PayPal' && ! filter_var($paymentRef, FILTER_VALIDATE_EMAIL)) {
                    $validator->errors()->add('payment_ref', 'PayPal reference must be a valid email address.');
                }
            },
        ];
    }
}
