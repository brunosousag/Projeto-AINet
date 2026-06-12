<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutFormRequest;
use App\Mail\OrderCanceledMail;
use App\Mail\OrderClosedMail;
use App\Mail\OrderPlacedMail;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\CartService;
use App\Services\PaymentService;
use App\Services\ReceiptService;
use App\Services\TshirtImageSnapshotService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        abort_unless($user, 403);

        $status = $request->query('status');
        $search = trim((string) $request->query('search', ''));
        $dateFrom = (string) $request->query('date_from', '');
        $dateTo = (string) $request->query('date_to', '');

        $orders = Order::query()
            ->with(['customer.user'])
            ->withCount('items')
            ->when($user->isCustomer(), fn ($query) => $query->where('customer_id', $user->id))
            ->when($user->isEmployee(), fn ($query) => $query->where('status', 'pending'))
            ->when(! $user->isEmployee() && in_array($status, ['pending', 'closed', 'canceled'], true), fn ($query) => $query->where('status', $status))
            ->when($user->isAdmin() && $this->isIsoDate($dateFrom), fn ($query) => $query->whereDate('date', '>=', $dateFrom))
            ->when($user->isAdmin() && $this->isIsoDate($dateTo), fn ($query) => $query->whereDate('date', '<=', $dateTo))
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('id', $search)
                        ->orWhere('nif', 'like', "%$search%")
                        ->orWhereHas('customer.user', fn ($query) => $query->where('name', 'like', "%$search%")
                            ->orWhere('email', 'like', "%$search%"));
                });
            })
            ->latest('date')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('orders.index', [
            'orders' => $orders,
            'filters' => [
                'status' => $status,
                'search' => $search,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
            'canManageOrders' => Gate::allows('manage-orders'),
            'canFilterDates' => $user->isAdmin(),
        ]);
    }

    public function receipts(Request $request): View
    {
        $user = $request->user();
        abort_unless($user && ($user->isAdmin() || $user->isCustomer()), 403);

        $search = trim((string) $request->query('search', ''));

        $orders = Order::query()
            ->with(['customer.user'])
            ->withCount('items')
            ->where('status', 'closed')
            ->whereNotNull('receipt_url')
            ->when($user->isCustomer(), fn ($query) => $query->where('customer_id', $user->id))
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('id', $search)
                        ->orWhere('nif', 'like', "%$search%")
                        ->orWhereHas('customer.user', fn ($query) => $query->where('name', 'like', "%$search%")
                            ->orWhere('email', 'like', "%$search%"));
                });
            })
            ->latest('date')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('orders.receipts', [
            'orders' => $orders,
            'search' => $search,
            'canManageOrders' => $user->isAdmin(),
        ]);
    }

    public function show(Request $request, Order $order): View
    {
        $this->authorizeOrderAccess($request, $order);

        return view('orders.show', [
            'order' => $order->load(['customer.user', 'items.tshirtImage.category', 'items.color']),
            'canManageOrders' => Gate::allows('manage-orders'),
            'canViewReceipt' => $this->canAccessReceipt($request, $order),
            'canCancelOrder' => $this->canCancelOrder($request, $order),
        ]);
    }

    public function checkout(Request $request, CartService $cartService): View|RedirectResponse
    {
        Gate::authorize('checkout');

        $cart = $cartService->summary();
        if ($cart['lines']->isEmpty()) {
            return redirect()
                ->route('cart.show')
                ->with('alert-type', 'warning')
                ->with('alert-msg', 'O carrinho está vazio.');
        }

        $customer = $request->user()->customer;

        return view('orders.checkout', [
            'cart' => $cart,
            'customer' => $customer,
            'paymentTypes' => ['Visa', 'PayPal', 'MB WAY'],
        ]);
    }

    public function store(
        CheckoutFormRequest $request,
        CartService $cartService,
        PaymentService $paymentService,
        TshirtImageSnapshotService $snapshotService
    ): RedirectResponse {
        $cart = $cartService->summary();
        if ($cart['lines']->isEmpty()) {
            return redirect()
                ->route('cart.show')
                ->with('alert-type', 'warning')
                ->with('alert-msg', 'O carrinho está vazio.');
        }

        $user = $request->user();
        $customer = $user->customer;
        abort_unless($customer, 403);

        abort_if(
            $cart['lines']->contains(fn (array $line): bool => $line['tshirt_image']->customer_id !== null
                && $line['tshirt_image']->customer_id !== $customer->id),
            403
        );

        $validated = $request->validated();
        $paymentService->process(
            $validated['payment_type'],
            $validated['payment_ref'],
            $cart['total'],
        );

        $order = DB::transaction(function () use ($cart, $customer, $validated, $request, $snapshotService): Order {
            $order = Order::create([
                'status' => 'pending',
                'customer_id' => $customer->id,
                'date' => now()->toDateString(),
                'total_price' => $cart['total'],
                'notes' => $validated['notes'] ?? null,
                'nif' => $validated['nif'],
                'address' => $validated['address'],
                'payment_type' => $validated['payment_type'],
                'payment_ref' => $validated['payment_ref'],
            ]);

            foreach ($cart['lines'] as $line) {
                $order->items()->create([
                    'tshirt_image_id' => $line['tshirt_image']->id,
                    'color_code' => $line['color']->code,
                    'size' => $line['size'],
                    'qty' => $line['qty'],
                    'unit_price' => $line['unit_price'],
                    'sub_total' => $line['sub_total'],
                    'custom' => [
                        'design' => $snapshotService->for($line['tshirt_image'], $line['settings']),
                    ],
                ]);
            }

            if ($request->boolean('save_defaults')) {
                $customer->update([
                    'nif' => $validated['nif'],
                    'address' => $validated['address'],
                    'default_payment_type' => $validated['payment_type'],
                    'default_payment_ref' => $validated['payment_ref'],
                ]);
            }

            return $order;
        });

        $cartService->clear();
        $this->sendMailSafely($order, new OrderPlacedMail($order));

        return redirect()
            ->route('orders.show', ['order' => $order])
            ->with('alert-type', 'success')
            ->with('alert-msg', "Encomenda #{$order->id} criada com sucesso.");
    }

    public function cancel(Request $request, Order $order): RedirectResponse
    {
        abort_unless($this->canCancelOrder($request, $order), 403);

        if ($order->status !== 'pending') {
            return back()
                ->with('alert-type', 'warning')
                ->with('alert-msg', 'Só encomendas pendentes podem ser canceladas.');
        }

        $validated = $request->validate([
            'reason_for_cancellation' => 'nullable|string|max:2000',
        ]);

        $order->update([
            'status' => 'canceled',
            'reason_for_cancellation' => $validated['reason_for_cancellation'] ?? null,
        ]);
        $this->sendMailSafely($order, new OrderCanceledMail($order->refresh()));

        return back()
            ->with('alert-type', 'success')
            ->with('alert-msg', "Encomenda #{$order->id} cancelada.");
    }

    public function close(Request $request, Order $order, ReceiptService $receiptService): RedirectResponse
    {
        abort_unless(Gate::allows('manage-orders'), 403);

        if ($order->status !== 'pending') {
            return back()
                ->with('alert-type', 'warning')
                ->with('alert-msg', 'Só encomendas pendentes podem ser fechadas.');
        }

        DB::transaction(function () use ($order, $receiptService): void {
            $order->forceFill(['status' => 'closed'])->save();

            $order->forceFill([
                'receipt_url' => $receiptService->generate($order),
            ])->save();
        });
        $this->sendMailSafely($order, new OrderClosedMail($order->refresh()));

        $redirect = $request->user()->isEmployee()
            ? redirect()->route('orders.index')
            : back();

        return $redirect
            ->with('alert-type', 'success')
            ->with('alert-msg', "Encomenda #{$order->id} fechada.");
    }

    public function receipt(Request $request, Order $order): BinaryFileResponse
    {
        abort_unless($this->canAccessReceipt($request, $order), 403);

        abort_unless($order->status === 'closed' && $order->receipt_url, 404);

        $path = Storage::disk('local')->path("pdf_receipts/{$order->receipt_url}");
        abort_unless(is_file($path), 404);

        return response()->file($path);
    }

    public function itemImage(Request $request, Order $order, OrderItem $item): BinaryFileResponse
    {
        $this->authorizeOrderAccess($request, $order);
        abort_unless($item->order_id === $order->id, 404);

        $filename = data_get($item->custom, 'design.image_url', $item->tshirtImage?->image_url);
        $isPersonal = data_get($item->custom, 'design.is_personal', $item->tshirtImage?->customer_id !== null);
        abort_unless($isPersonal && $filename && basename($filename) === $filename, 404);

        $path = Storage::disk('local')->path("tshirt_images_private/{$filename}");
        abort_unless(is_file($path), 404);

        return response()->file($path);
    }

    private function authorizeOrderAccess(Request $request, Order $order): void
    {
        $user = $request->user();

        abort_unless(
            $user && (
                $user->isAdmin()
                || ($user->isEmployee() && $order->status === 'pending')
                || ($user->isCustomer() && $order->customer_id === $user->id)
            ),
            403
        );
    }

    private function canAccessReceipt(Request $request, Order $order): bool
    {
        $user = $request->user();

        return $user && ($user->isAdmin() || ($user->isCustomer() && $order->customer_id === $user->id));
    }

    private function canCancelOrder(Request $request, Order $order): bool
    {
        $user = $request->user();

        return $user && ($user->isAdmin() || ($user->isCustomer() && $order->customer_id === $user->id));
    }

    private function isIsoDate(string $value): bool
    {
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1;
    }

    private function sendMailSafely(Order $order, object $mail): void
    {
        $email = $order->customer?->user?->email;
        if (! $email) {
            return;
        }

        try {
            Mail::to($email)->send($mail);
        } catch (Throwable $exception) {
            Log::warning('Could not send order email.', [
                'order_id' => $order->id,
                'exception' => $exception->getMessage(),
            ]);
        }
    }
}
