<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutFormRequest;
use App\Models\Order;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        abort_unless($user, 403);

        $status = $request->query('status');
        $search = trim((string) $request->query('search', ''));

        $orders = Order::query()
            ->with(['customer.user'])
            ->withCount('items')
            ->when(! $user->isAdmin() && ! $user->isEmployee(), fn ($query) => $query->where('customer_id', $user->id))
            ->when(in_array($status, ['pending', 'closed', 'canceled'], true), fn ($query) => $query->where('status', $status))
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
            ],
            'canManageOrders' => Gate::allows('manage-orders'),
        ]);
    }

    public function show(Request $request, Order $order): View
    {
        $this->authorizeOrderAccess($request, $order);

        return view('orders.show', [
            'order' => $order->load(['customer.user', 'items.tshirtImage.category', 'items.color']),
            'canManageOrders' => Gate::allows('manage-orders'),
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

    public function store(CheckoutFormRequest $request, CartService $cartService): RedirectResponse
    {
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

        $order = DB::transaction(function () use ($cart, $customer, $validated, $request): Order {
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

        return redirect()
            ->route('orders.show', ['order' => $order])
            ->with('alert-type', 'success')
            ->with('alert-msg', "Encomenda #{$order->id} criada com sucesso.");
    }

    public function cancel(Request $request, Order $order): RedirectResponse
    {
        $this->authorizeOrderAccess($request, $order);

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

        return back()
            ->with('alert-type', 'success')
            ->with('alert-msg', "Encomenda #{$order->id} cancelada.");
    }

    public function close(Request $request, Order $order): RedirectResponse
    {
        abort_unless(Gate::allows('manage-orders'), 403);

        if ($order->status !== 'pending') {
            return back()
                ->with('alert-type', 'warning')
                ->with('alert-msg', 'Só encomendas pendentes podem ser fechadas.');
        }

        $order->update(['status' => 'closed']);

        return back()
            ->with('alert-type', 'success')
            ->with('alert-msg', "Encomenda #{$order->id} fechada.");
    }

    public function receipt(Request $request, Order $order): BinaryFileResponse
    {
        $this->authorizeOrderAccess($request, $order);

        abort_unless($order->receipt_url, 404);

        $path = storage_path("app/private/pdf_receipts/{$order->receipt_url}");
        abort_unless(is_file($path), 404);

        return response()->file($path);
    }

    private function authorizeOrderAccess(Request $request, Order $order): void
    {
        $user = $request->user();

        abort_unless(
            $user && (Gate::allows('manage-orders') || ($user->isCustomer() && $order->customer_id === $user->id)),
            403
        );
    }
}
