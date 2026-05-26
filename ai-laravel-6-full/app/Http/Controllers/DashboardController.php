<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\TshirtImage;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        if (Gate::allows('manage-orders') || Gate::allows('manage-catalog')) {
            return $this->managementDashboard();
        }

        $user = auth()->user();
        abort_unless($user instanceof User && $user->isCustomer(), 403);

        return $this->customerDashboard($user);
    }

    private function managementDashboard(): View
    {
        $statusCounts = Order::query()
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $latestOrders = Order::query()
            ->with(['customer.user'])
            ->withCount('items')
            ->latest('date')
            ->latest('id')
            ->limit(8)
            ->get();

        $topImages = OrderItem::query()
            ->select(
                'tshirt_image_id',
                DB::raw('SUM(qty) as sold_qty'),
                DB::raw('SUM(sub_total) as revenue')
            )
            ->with(['tshirtImage'])
            ->groupBy('tshirt_image_id')
            ->orderByDesc('sold_qty')
            ->limit(5)
            ->get();

        return view('dashboard', [
            'mode' => 'management',
            'stats' => [
                'orders' => Order::count(),
                'pending_orders' => (int) ($statusCounts['pending'] ?? 0),
                'closed_orders' => (int) ($statusCounts['closed'] ?? 0),
                'canceled_orders' => (int) ($statusCounts['canceled'] ?? 0),
                'closed_revenue' => (float) Order::where('status', 'closed')->sum('total_price'),
                'pending_revenue' => (float) Order::where('status', 'pending')->sum('total_price'),
                'customers' => Customer::count(),
                'catalog_images' => TshirtImage::whereNull('customer_id')->count(),
                'personal_images' => TshirtImage::whereNotNull('customer_id')->count(),
            ],
            'latestOrders' => $latestOrders,
            'topImages' => $topImages,
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
        ]);
    }
}
