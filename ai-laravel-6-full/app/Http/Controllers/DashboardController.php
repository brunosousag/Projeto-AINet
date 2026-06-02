<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\TshirtImage;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        if (Gate::allows('manage-orders') || Gate::allows('manage-catalog')) {
            $user = auth()->user();
            abort_unless($user instanceof User, 403);

            return $this->managementDashboard($user);
        }

        $user = auth()->user();
        abort_unless($user instanceof User && $user->isCustomer(), 403);

        return $this->customerDashboard($user);
    }

    private function managementDashboard(User $user): View
    {
        $ordersQuery = fn () => Order::query()
            ->when($user->isEmployee(), fn ($query) => $query->where('status', 'pending'));

        $statusCounts = $ordersQuery()
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $latestOrders = $ordersQuery()
            ->with(['customer.user'])
            ->withCount('items')
            ->latest('date')
            ->latest('id')
            ->limit(8)
            ->get();

        $topImages = $user->isAdmin() ? OrderItem::query()
            ->select(
                'tshirt_image_id',
                DB::raw('SUM(qty) as sold_qty'),
                DB::raw('SUM(sub_total) as revenue')
            )
            ->with(['tshirtImage'])
            ->whereHas('order', fn ($query) => $query->where('status', 'closed'))
            ->groupBy('tshirt_image_id')
            ->orderByDesc('sold_qty')
            ->limit(5)
            ->get() : collect();

        return view('dashboard', [
            'mode' => 'management',
            'stats' => [
                'orders' => $ordersQuery()->count(),
                'pending_orders' => (int) ($statusCounts['pending'] ?? 0),
                'closed_orders' => (int) ($statusCounts['closed'] ?? 0),
                'canceled_orders' => (int) ($statusCounts['canceled'] ?? 0),
                'closed_revenue' => (float) $ordersQuery()->where('status', 'closed')->sum('total_price'),
                'pending_revenue' => (float) $ordersQuery()->where('status', 'pending')->sum('total_price'),
                'customers' => Customer::count(),
                'catalog_images' => TshirtImage::whereNull('customer_id')->count(),
                'personal_images' => TshirtImage::whereNotNull('customer_id')->count(),
                'cancellation_rate' => $this->cancellationRate(),
            ],
            'latestOrders' => $latestOrders,
            'topImages' => $topImages,
            'canSeeStatistics' => $user->isAdmin(),
            'monthlySales' => $user->isAdmin() ? $this->monthlySales() : collect(),
            'colorSales' => $user->isAdmin() ? $this->colorSales() : collect(),
            'sizeSales' => $user->isAdmin() ? $this->sizeSales() : collect(),
            'topCustomers' => $user->isAdmin() ? $this->topCustomers() : collect(),
            'categorySales' => $user->isAdmin() ? $this->categorySales() : collect(),
            'designTypeSales' => $user->isAdmin() ? $this->designTypeSales() : collect(),
            'cancellationReasons' => $user->isAdmin() ? $this->cancellationReasons() : collect(),
        ]);
    }

    private function customerDashboard(User $user): View
    {
        $ordersQuery = fn () => Order::query()->where('customer_id', $user->id);

        $statusCounts = $ordersQuery()
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $latestOrders = $ordersQuery()
            ->withCount('items')
            ->latest('date')
            ->latest('id')
            ->limit(8)
            ->get();

        return view('dashboard', [
            'mode' => 'customer',
            'stats' => [
                'orders' => $ordersQuery()->count(),
                'pending_orders' => (int) ($statusCounts['pending'] ?? 0),
                'closed_orders' => (int) ($statusCounts['closed'] ?? 0),
                'canceled_orders' => (int) ($statusCounts['canceled'] ?? 0),
                'closed_revenue' => (float) $ordersQuery()->where('status', 'closed')->sum('total_price'),
                'pending_revenue' => (float) $ordersQuery()->where('status', 'pending')->sum('total_price'),
                'customers' => 0,
                'catalog_images' => TshirtImage::whereNull('customer_id')->count(),
                'personal_images' => TshirtImage::where('customer_id', $user->id)->count(),
            ],
            'latestOrders' => $latestOrders,
            'topImages' => collect(),
            'canSeeStatistics' => false,
            'monthlySales' => collect(),
            'colorSales' => collect(),
            'sizeSales' => collect(),
            'topCustomers' => collect(),
            'categorySales' => collect(),
            'designTypeSales' => collect(),
            'cancellationReasons' => collect(),
        ]);
    }

    private function cancellationRate(): float
    {
        $orders = Order::count();

        return $orders > 0
            ? round(Order::where('status', 'canceled')->count() * 100 / $orders, 1)
            : 0.0;
    }

    private function monthlySales(): Collection
    {
        $rows = Order::query()
            ->where('status', 'closed')
            ->whereDate('date', '>=', now()->startOfMonth()->subMonths(11))
            ->get(['date', 'total_price'])
            ->groupBy(fn (Order $order): string => $order->date->format('Y-m'));

        return collect(range(11, 0))
            ->map(function (int $monthsAgo) use ($rows): array {
                $month = now()->startOfMonth()->subMonths($monthsAgo);
                $orders = $rows->get($month->format('Y-m'), collect());

                return [
                    'label' => $month->format('M Y'),
                    'orders' => $orders->count(),
                    'revenue' => (float) $orders->sum('total_price'),
                ];
            });
    }

    private function colorSales(): Collection
    {
        return OrderItem::query()
            ->select('color_code', DB::raw('SUM(qty) as sold_qty'))
            ->with('color')
            ->whereHas('order', fn ($query) => $query->where('status', 'closed'))
            ->groupBy('color_code')
            ->orderByDesc('sold_qty')
            ->limit(6)
            ->get();
    }

    private function sizeSales(): Collection
    {
        return OrderItem::query()
            ->select('size', DB::raw('SUM(qty) as sold_qty'))
            ->whereHas('order', fn ($query) => $query->where('status', 'closed'))
            ->groupBy('size')
            ->orderByDesc('sold_qty')
            ->get();
    }

    private function topCustomers(): Collection
    {
        return Order::query()
            ->select(
                'customer_id',
                DB::raw('COUNT(*) as orders_count'),
                DB::raw('SUM(total_price) as revenue')
            )
            ->with('customer.user')
            ->where('status', 'closed')
            ->groupBy('customer_id')
            ->orderByDesc('revenue')
            ->limit(5)
            ->get();
    }

    private function categorySales(): Collection
    {
        return DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('tshirt_images', 'tshirt_images.id', '=', 'order_items.tshirt_image_id')
            ->leftJoin('categories', 'categories.id', '=', 'tshirt_images.category_id')
            ->selectRaw("COALESCE(categories.name, 'Personal images') as label, SUM(order_items.qty) as sold_qty")
            ->where('orders.status', 'closed')
            ->groupBy('categories.name')
            ->orderByDesc('sold_qty')
            ->limit(6)
            ->get();
    }

    private function designTypeSales(): Collection
    {
        return DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('tshirt_images', 'tshirt_images.id', '=', 'order_items.tshirt_image_id')
            ->selectRaw("CASE WHEN tshirt_images.customer_id IS NULL THEN 'Catalog' ELSE 'Personal' END as label, SUM(order_items.qty) as sold_qty")
            ->where('orders.status', 'closed')
            ->groupByRaw("CASE WHEN tshirt_images.customer_id IS NULL THEN 'Catalog' ELSE 'Personal' END")
            ->orderByDesc('sold_qty')
            ->get();
    }

    private function cancellationReasons(): Collection
    {
        return Order::query()
            ->where('status', 'canceled')
            ->get(['reason_for_cancellation'])
            ->groupBy(fn (Order $order): string => trim((string) $order->reason_for_cancellation) ?: 'No reason provided')
            ->map(fn ($orders, string $label): array => [
                'label' => $label,
                'total' => $orders->count(),
            ])
            ->sortByDesc('total')
            ->take(5)
            ->values();
    }
}
