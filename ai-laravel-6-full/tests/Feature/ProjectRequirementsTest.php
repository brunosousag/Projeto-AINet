<?php

use App\Mail\OrderCanceledMail;
use App\Mail\OrderPlacedMail;
use App\Models\Category;
use App\Models\Color;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Price;
use App\Models\TshirtImage;
use App\Models\User;
use App\Services\CartService;
use App\Services\PaymentService;
use App\Services\TshirtImageSnapshotService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

function createProjectCustomer(): User
{
    $user = User::factory()->create(['user_type' => 'C']);

    Customer::create([
        'id' => $user->id,
        'nif' => '123456789',
        'address' => 'Customer address',
    ]);

    return $user;
}

function createProjectCatalogLine(): array
{
    Price::create([
        'unit_price_catalog' => 10,
        'unit_price_own' => 12,
        'unit_price_catalog_discount' => 8,
        'unit_price_own_discount' => 10,
        'qty_discount' => 5,
    ]);

    $color = Color::create([
        'code' => 'ffffff',
        'name' => 'White',
    ]);

    $image = TshirtImage::create([
        'customer_id' => null,
        'category_id' => null,
        'name' => 'Catalog image',
        'description' => 'Test image',
        'image_url' => 'placeholder.png',
    ]);

    return [$color, $image];
}

test('checkout calls the external payment api and sends the pending email', function () {
    Mail::fake();
    Http::fake(['*' => Http::response([], 201)]);

    $customer = createProjectCustomer();
    [$color, $image] = createProjectCatalogLine();

    $this->actingAs($customer)
        ->post(route('cart.add'), [
            'tshirt_image_id' => $image->id,
            'color_code' => $color->code,
            'size' => 'M',
            'qty' => 1,
            'preview_top' => 14,
            'preview_width' => 62,
            'preview_height' => 58,
            'preview_opacity' => 91,
        ])
        ->assertRedirect();

    $this->post(route('checkout.store'), [
        'nif' => '123456789',
        'address' => 'Customer address',
        'payment_type' => 'Visa',
        'payment_ref' => '4111111111111111',
    ])->assertRedirect();

    $order = Order::sole();
    $item = $order->items()->sole();

    Http::assertSent(fn ($request): bool => $request['type'] === 'Visa'
        && $request['reference'] === '4111111111111111'
        && (float) $request['value'] === 10.0);
    expect($item->design_settings)->toBe([
        'preview_top' => 14,
        'preview_width' => 62,
        'preview_height' => 58,
        'preview_opacity' => 91,
    ]);
    Mail::assertSent(OrderPlacedMail::class, fn (OrderPlacedMail $mail): bool => $mail->order->is($order));
});

test('a refused payment does not create an order', function () {
    Http::fake(['*' => Http::response(['message' => 'Insufficient funds'], 422)]);

    $customer = createProjectCustomer();
    [$color, $image] = createProjectCatalogLine();

    $this->actingAs($customer)
        ->post(route('cart.add'), [
            'tshirt_image_id' => $image->id,
            'color_code' => $color->code,
            'size' => 'M',
            'qty' => 1,
        ]);

    $this->post(route('checkout.store'), [
        'nif' => '123456789',
        'address' => 'Customer address',
        'payment_type' => 'Visa',
        'payment_ref' => '4999999999999999',
    ])->assertSessionHasErrors('payment_ref');

    expect(Order::count())->toBe(0);
});

test('cart updates preserve the selected size and design resize settings', function () {
    [$color, $image] = createProjectCatalogLine();
    $otherColor = Color::create([
        'code' => '000000',
        'name' => 'Black',
    ]);

    $this->post(route('cart.add'), [
        'tshirt_image_id' => $image->id,
        'color_code' => $color->code,
        'size' => 'M',
        'qty' => 1,
        'preview_top' => 14,
        'preview_width' => 62,
        'preview_height' => 58,
        'preview_opacity' => 91,
    ])->assertRedirect();

    $line = array_key_first(session('cart'));

    $this->put(route('cart.update', ['line' => $line]), [
        'color_code' => $otherColor->code,
        'size' => 'XL',
        'qty' => 2,
        'preview_width' => 90,
    ])->assertRedirect();

    $item = app(CartService::class)->summary()['lines']->sole();

    expect($item['color']->code)->toBe($otherColor->code);
    expect($item['size'])->toBe('M');
    expect($item['qty'])->toBe(2);
    expect($item['settings'])->toBe([
        'preview_top' => 14,
        'preview_width' => 62,
        'preview_height' => 58,
        'preview_opacity' => 91,
    ]);

    $this->get(route('cart.show'))
        ->assertOk()
        ->assertDontSee('type="range"', false)
        ->assertDontSee('name="size"', false);
});

test('visa cards must start with four', function () {
    Http::fake();

    $customer = createProjectCustomer();
    [$color, $image] = createProjectCatalogLine();

    $this->actingAs($customer)
        ->post(route('cart.add'), [
            'tshirt_image_id' => $image->id,
            'color_code' => $color->code,
            'size' => 'M',
            'qty' => 1,
        ]);

    $this->post(route('checkout.store'), [
        'nif' => '123456789',
        'address' => 'Customer address',
        'payment_type' => 'Visa',
        'payment_ref' => '5111111111111111',
    ])->assertSessionHasErrors('payment_ref');

    Http::assertNothingSent();
});

test('payment totals outside the supported range are rejected locally', function () {
    Http::fake();

    expect(fn () => app(PaymentService::class)->process('Visa', '4111111111111111', 1000000))
        ->toThrow(ValidationException::class);

    Http::assertNothingSent();
});

test('employees cannot list or download receipts', function () {
    $employee = User::factory()->create(['user_type' => 'F']);
    $customer = createProjectCustomer();

    $order = Order::create([
        'status' => 'closed',
        'customer_id' => $customer->id,
        'date' => now()->toDateString(),
        'total_price' => 10,
        'nif' => '123456789',
        'address' => 'Customer address',
        'payment_type' => 'MB WAY',
        'payment_ref' => '912345678',
        'receipt_url' => 'receipt_private.pdf',
    ]);

    $this->actingAs($employee)
        ->get(route('orders.receipts'))
        ->assertForbidden();

    $this->get(route('orders.receipt', ['order' => $order]))
        ->assertForbidden();
});

test('employees only see pending orders and cannot cancel them', function () {
    $employee = User::factory()->create(['user_type' => 'F']);
    $customer = createProjectCustomer();

    $pending = Order::create([
        'status' => 'pending',
        'customer_id' => $customer->id,
        'date' => now()->toDateString(),
        'total_price' => 10,
        'nif' => '123456789',
        'address' => 'Customer address',
        'payment_type' => 'MB WAY',
        'payment_ref' => '912345678',
    ]);

    $closed = Order::create([
        'status' => 'closed',
        'customer_id' => $customer->id,
        'date' => now()->toDateString(),
        'total_price' => 20,
        'nif' => '123456789',
        'address' => 'Customer address',
        'payment_type' => 'MB WAY',
        'payment_ref' => '912345678',
    ]);

    $response = $this->actingAs($employee)
        ->get(route('orders.index'))
        ->assertOk();

    expect($response->viewData('orders')->pluck('id')->all())->toBe([$pending->id]);

    $this->patch(route('orders.cancel', ['order' => $pending]))
        ->assertForbidden();

    $this->get(route('orders.show', ['order' => $closed]))
        ->assertForbidden();
});

test('customers can update their personal image preview settings', function () {
    Storage::fake('local');
    $customer = createProjectCustomer();

    Storage::disk('local')->put('tshirt_images_private/original.png', 'image');

    $image = TshirtImage::create([
        'customer_id' => $customer->id,
        'category_id' => null,
        'name' => 'Original image',
        'description' => null,
        'image_url' => 'original.png',
    ]);

    $this->actingAs($customer)
        ->put(route('personal-tshirt-images.update', ['personal_tshirt_image' => $image]), [
            'name' => 'Updated image',
            'description' => 'Updated description',
            'preview_top' => 18,
            'preview_width' => 52,
            'preview_height' => 44,
            'preview_opacity' => 85,
        ])
        ->assertRedirect(route('personal-tshirt-images.index'));

    expect($image->refresh()->custom)->toBe([
        'preview_top' => 18,
        'preview_width' => 52,
        'preview_height' => 44,
        'preview_opacity' => 85,
    ]);
});

test('customers can list personal images and add them to the cart', function () {
    $customer = createProjectCustomer();
    Color::create([
        'code' => 'ffffff',
        'name' => 'White',
    ]);

    TshirtImage::create([
        'customer_id' => $customer->id,
        'category_id' => null,
        'name' => 'Personal image',
        'description' => null,
        'image_url' => 'personal.png',
    ]);

    $this->actingAs($customer)
        ->get(route('personal-tshirt-images.index'))
        ->assertOk()
        ->assertSeeText('Personal image')
        ->assertSee('name="size"', false)
        ->assertSeeText('XS')
        ->assertSeeText('XL')
        ->assertSee('name="qty"', false);
});

test('creating a color stores its t-shirt base image', function () {
    Storage::fake('public');
    $admin = User::factory()->create(['user_type' => 'A']);

    $this->actingAs($admin)
        ->post(route('colors.store'), [
            'code' => 'abcdef',
            'name' => 'Test color',
            'base_image_file' => UploadedFile::fake()->image('shirt.jpg'),
        ])
        ->assertRedirect(route('colors.index'));

    Storage::disk('public')->assertExists('tshirt_base/abcdef.jpg');
});

test('catalog filters categories without rendering the visual category browser', function () {
    Category::create([
        'name' => 'Nature',
        'image_url' => 'nature.png',
    ]);

    $this->get(route('shop.index'))
        ->assertOk()
        ->assertDontSeeText('Browse categories')
        ->assertSeeText('All categories')
        ->assertSeeText('Nature')
        ->assertDontSee('name="color_code"', false);
});

test('catalog renders color swatches over a t-shirt preview', function () {
    [$color, $image] = createProjectCatalogLine();

    $this->get(route('shop.tshirt-details', ['tshirtImage' => $image]))
        ->assertOk()
        ->assertSee('type="radio"', false)
        ->assertSee('name="color_code"', false)
        ->assertSee('name="preview_width"', false)
        ->assertSee('name="preview_height"', false)
        ->assertSee("storage/tshirt_base/{$color->code}.jpg", false)
        ->assertSee('data-print-area', false);
});

test('administrators create colors with a visual color picker', function () {
    $admin = User::factory()->create(['user_type' => 'A']);

    $this->actingAs($admin)
        ->get(route('colors.create'))
        ->assertOk()
        ->assertSee('type="color"', false)
        ->assertSeeText('Color picker');
});

test('administrators preview and position catalog designs before uploading', function () {
    $admin = User::factory()->create(['user_type' => 'A']);
    Color::create([
        'code' => 'ffffff',
        'name' => 'White',
    ]);

    $this->actingAs($admin)
        ->get(route('tshirt-images.create'))
        ->assertOk()
        ->assertSeeText('T-shirt preview')
        ->assertSee('name="color_code"', false)
        ->assertSee('name="preview_top"', false)
        ->assertSee('name="preview_width"', false)
        ->assertSee('data-print-area', false);
});

test('administrators can render the statistics dashboard', function () {
    $admin = User::factory()->create(['user_type' => 'A']);

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSeeText('Monthly closed sales')
        ->assertSeeText('Best-selling colors')
        ->assertSeeText('Sizes sold')
        ->assertSeeText('Top customers')
        ->assertSeeText('Best-selling categories')
        ->assertSeeText('Catalog vs personal designs')
        ->assertSeeText('Cancellation reasons');
});

test('customers can render the personal image editor', function () {
    $customer = createProjectCustomer();
    Color::create([
        'code' => 'ffffff',
        'name' => 'White',
    ]);

    $image = TshirtImage::create([
        'customer_id' => $customer->id,
        'category_id' => null,
        'name' => 'Editable image',
        'description' => null,
        'image_url' => 'editable.png',
    ]);

    $this->actingAs($customer)
        ->get(route('personal-tshirt-images.edit', ['personal_tshirt_image' => $image]))
        ->assertOk()
        ->assertSeeText('Edit image')
        ->assertSee('preview_top')
        ->assertSee('preview_opacity')
        ->assertSee('image-preview-selected', false)
        ->assertSee('x-bind:src="previewImage"', false)
        ->assertSee('type="radio"', false);
});

test('canceling an order sends its customer an email', function () {
    Mail::fake();
    $admin = User::factory()->create(['user_type' => 'A']);
    $customer = createProjectCustomer();

    $order = Order::create([
        'status' => 'pending',
        'customer_id' => $customer->id,
        'date' => now()->toDateString(),
        'total_price' => 10,
        'nif' => '123456789',
        'address' => 'Customer address',
        'payment_type' => 'MB WAY',
        'payment_ref' => '912345678',
    ]);

    $this->actingAs($admin)
        ->patch(route('orders.cancel', ['order' => $order]), [
            'reason_for_cancellation' => 'Unavailable design',
        ])
        ->assertRedirect();

    expect($order->refresh()->status)->toBe('canceled');
    Mail::assertSent(OrderCanceledMail::class, fn (OrderCanceledMail $mail): bool => $mail->order->is($order));
});

test('administrators cannot edit customer private profiles', function () {
    $admin = User::factory()->create(['user_type' => 'A']);
    $customer = createProjectCustomer();
    $customer->customer->update([
        'default_payment_type' => 'Visa',
        'default_payment_ref' => '4111111111111111',
    ]);

    $this->actingAs($admin)
        ->get(route('users.show', ['user' => $customer]))
        ->assertOk()
        ->assertDontSee('Customer address')
        ->assertDontSee('4111111111111111');

    $this->get(route('users.edit', ['user' => $customer]))
        ->assertForbidden();

    $this->patch(route('users.change-type', ['user' => $customer]), [
        'user_type' => 'F',
    ])->assertForbidden();
});

test('deleting a customer user also soft deletes the customer profile', function () {
    $admin = User::factory()->create(['user_type' => 'A']);
    $customer = createProjectCustomer();

    $this->actingAs($admin)
        ->delete(route('users.destroy', ['user' => $customer]))
        ->assertRedirect(route('users.index'));

    $this->assertSoftDeleted('users', ['id' => $customer->id]);
    $this->assertSoftDeleted('customers', ['id' => $customer->id]);
});

test('order design snapshots remain unchanged after editing the catalog', function () {
    [$color, $image] = createProjectCatalogLine();
    $customer = createProjectCustomer();
    $order = Order::create([
        'status' => 'pending',
        'customer_id' => $customer->id,
        'date' => now()->toDateString(),
        'total_price' => 10,
        'nif' => '123456789',
        'address' => 'Customer address',
        'payment_type' => 'MB WAY',
        'payment_ref' => '912345678',
    ]);
    $item = $order->items()->create([
        'tshirt_image_id' => $image->id,
        'color_code' => $color->code,
        'size' => 'M',
        'qty' => 1,
        'unit_price' => 10,
        'sub_total' => 10,
    ]);

    app(TshirtImageSnapshotService::class)->preserveExistingOrders($image);
    $image->update(['name' => 'Changed catalog image']);

    expect($item->refresh()->design_name)->toBe('Catalog image');
});

test('staff open personal images through an authorized order only', function () {
    $customer = createProjectCustomer();
    $otherCustomer = createProjectCustomer();
    $employee = User::factory()->create(['user_type' => 'F']);
    $filename = 'test_private_'.Str::random(12).'.png';
    $path = storage_path("app/private/tshirt_images_private/{$filename}");

    if (! is_dir(dirname($path))) {
        mkdir(dirname($path), 0777, true);
    }
    file_put_contents($path, 'image');

    try {
        $color = Color::create(['code' => 'ffffff', 'name' => 'White']);
        $image = TshirtImage::create([
            'customer_id' => $customer->id,
            'category_id' => null,
            'name' => 'Private design',
            'description' => null,
            'image_url' => $filename,
        ]);
        $order = Order::create([
            'status' => 'pending',
            'customer_id' => $customer->id,
            'date' => now()->toDateString(),
            'total_price' => 12,
            'nif' => '123456789',
            'address' => 'Customer address',
            'payment_type' => 'MB WAY',
            'payment_ref' => '912345678',
        ]);
        $item = $order->items()->create([
            'tshirt_image_id' => $image->id,
            'color_code' => $color->code,
            'size' => 'M',
            'qty' => 1,
            'unit_price' => 12,
            'sub_total' => 12,
            'custom' => ['design' => app(TshirtImageSnapshotService::class)->for($image)],
        ]);
        $image->delete();

        $this->actingAs($customer)
            ->get(route('tshirt-images.private-image', ['tshirtImage' => $image]))
            ->assertOk();

        $this->actingAs($employee)
            ->get(route('tshirt-images.private-image', ['tshirtImage' => $image]))
            ->assertForbidden();

        $this->get(route('orders.items.image', ['order' => $order, 'item' => $item]))
            ->assertOk();

        $this->actingAs($otherCustomer)
            ->get(route('orders.items.image', ['order' => $order, 'item' => $item]))
            ->assertForbidden();
    } finally {
        if (is_file($path)) {
            unlink($path);
        }
    }
});

test('administrators can remove collaborator photos safely', function () {
    Storage::fake('public');
    $admin = User::factory()->create(['user_type' => 'A']);
    $employee = User::factory()->create([
        'user_type' => 'F',
        'photo_url' => 'employee.png',
    ]);
    Storage::disk('public')->put('photos/employee.png', 'image');

    $this->actingAs($admin)
        ->delete(route('users.photo.destroy', ['user' => $employee]))
        ->assertRedirect();

    expect($employee->refresh()->photo_url)->toBeNull();
    Storage::disk('public')->assertMissing('photos/employee.png');
});
