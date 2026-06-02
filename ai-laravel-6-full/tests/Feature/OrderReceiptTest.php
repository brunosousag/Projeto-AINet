<?php

use App\Mail\OrderClosedMail;
use App\Models\Color;
use App\Models\Customer;
use App\Models\Order;
use App\Models\TshirtImage;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;

test('closing an order generates a receipt file', function () {
    Mail::fake();
    $receiptPath = storage_path('app/private/pdf_receipts/receipt_1.pdf');
    $designPath = storage_path('app/public/tshirt_images/receipt_test_design.jpg');
    $basePath = storage_path('app/public/tshirt_base/f0f0f0.jpg');
    $originalReceipt = is_file($receiptPath) ? file_get_contents($receiptPath) : null;
    $originalDesign = is_file($designPath) ? file_get_contents($designPath) : null;
    $originalBase = is_file($basePath) ? file_get_contents($basePath) : null;

    $admin = User::factory()->create(['user_type' => 'A']);
    $customerUser = User::factory()->create(['user_type' => 'C']);

    try {
        if (! is_dir(dirname($designPath))) {
            mkdir(dirname($designPath), 0775, true);
        }

        if (! is_dir(dirname($basePath))) {
            mkdir(dirname($basePath), 0775, true);
        }

        file_put_contents($designPath, UploadedFile::fake()->image('design.jpg', 40, 40)->getContent());
        file_put_contents($basePath, UploadedFile::fake()->image('shirt.jpg', 40, 40)->getContent());

        Customer::create([
            'id' => $customerUser->id,
            'nif' => '123456789',
            'address' => 'Customer address',
        ]);

        $color = Color::create([
            'code' => 'f0f0f0',
            'name' => 'White',
        ]);

        $image = TshirtImage::create([
            'customer_id' => null,
            'category_id' => null,
            'name' => 'Catalog image',
            'description' => 'Test image',
            'image_url' => basename($designPath),
        ]);

        $order = Order::create([
            'status' => 'pending',
            'customer_id' => $customerUser->id,
            'date' => now()->toDateString(),
            'total_price' => 12.50,
            'nif' => '123456789',
            'address' => 'Customer address',
            'payment_type' => 'MB WAY',
            'payment_ref' => '912345678',
        ]);

        $order->items()->create([
            'tshirt_image_id' => $image->id,
            'color_code' => $color->code,
            'size' => 'M',
            'qty' => 1,
            'unit_price' => 12.50,
            'sub_total' => 12.50,
        ]);

        $this->actingAs($admin)
            ->patch(route('orders.close', ['order' => $order]))
            ->assertRedirect();

        $order->refresh();

        expect($order->status)->toBe('closed');
        expect($order->receipt_url)->toBeString()->not->toBeEmpty();
        expect(storage_path("app/private/pdf_receipts/{$order->receipt_url}"))->toBeFile();
        expect(file_get_contents(storage_path("app/private/pdf_receipts/{$order->receipt_url}")))
            ->toContain('/Subtype /Image')
            ->toContain('Images embedded from Base64 data URIs');
        Mail::assertSent(OrderClosedMail::class, fn (OrderClosedMail $mail): bool => $mail->order->is($order));
    } finally {
        if ($originalReceipt !== null) {
            file_put_contents($receiptPath, $originalReceipt);
        } elseif (is_file($receiptPath)) {
            unlink($receiptPath);
        }

        if ($originalDesign !== null) {
            file_put_contents($designPath, $originalDesign);
        } elseif (is_file($designPath)) {
            unlink($designPath);
        }

        if ($originalBase !== null) {
            file_put_contents($basePath, $originalBase);
        } elseif (is_file($basePath)) {
            unlink($basePath);
        }
    }
});

test('customers can list their own receipts', function () {
    $customerUser = User::factory()->create(['user_type' => 'C']);
    $otherCustomerUser = User::factory()->create(['user_type' => 'C']);

    Customer::create([
        'id' => $customerUser->id,
        'nif' => '123456789',
        'address' => 'Customer address',
    ]);

    Customer::create([
        'id' => $otherCustomerUser->id,
        'nif' => '987654321',
        'address' => 'Other address',
    ]);

    Order::create([
        'status' => 'closed',
        'customer_id' => $customerUser->id,
        'date' => now()->toDateString(),
        'total_price' => 14.50,
        'nif' => '123456789',
        'address' => 'Customer address',
        'payment_type' => 'MB WAY',
        'payment_ref' => '912345678',
        'receipt_url' => 'receipt_customer.pdf',
    ]);

    Order::create([
        'status' => 'closed',
        'customer_id' => $otherCustomerUser->id,
        'date' => now()->toDateString(),
        'total_price' => 99.99,
        'nif' => '987654321',
        'address' => 'Other address',
        'payment_type' => 'Visa',
        'payment_ref' => '4111111111111111',
        'receipt_url' => 'receipt_other_customer.pdf',
    ]);

    $this->actingAs($customerUser)
        ->get(route('orders.receipts'))
        ->assertOk()
        ->assertSee('My receipts')
        ->assertSee('14.50 EUR')
        ->assertDontSee('99.99 EUR');
});
