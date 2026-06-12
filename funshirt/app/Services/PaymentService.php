<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    public function process(string $type, string $reference, mixed $value): void
    {
        $amount = (float) $value;
        if ($amount < 0.01 || $amount > 999999.99 || round($amount, 2) !== $amount) {
            throw ValidationException::withMessages([
                'payment_ref' => 'The order total is not valid for payment.',
            ]);
        }

        try {
            $response = Http::acceptJson()
                ->connectTimeout(2)
                ->timeout(5)
                ->post(config('services.payments.url'), [
                    'type' => $type,
                    'reference' => $reference,
                    'value' => $amount,
                ]);
        } catch (ConnectionException) {
            throw ValidationException::withMessages([
                'payment_ref' => 'The payment service is temporarily unavailable. Please try again.',
            ]);
        }

        if ($response->status() !== 201) {
            throw ValidationException::withMessages([
                'payment_ref' => $response->json('message')
                    ?? $response->json('error')
                    ?? 'The payment was refused. Please check the payment details.',
            ]);
        }
    }
}
