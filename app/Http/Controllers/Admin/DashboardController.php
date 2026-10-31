<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Queries\BestSeller;
use App\Queries\BestSellingProducts;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Store overview for the last 30 days, compared with the 30 days before.
 * Revenue counts only paid, shipped and delivered orders.
 */
class DashboardController extends Controller
{
    private const SOLD = [OrderStatus::Paid, OrderStatus::Shipped, OrderStatus::Delivered];

    public function __invoke(Request $request, BestSellingProducts $bestSellers): Response
    {
        $now = now();
        $periodStart = $now->copy()->subDays(30);
        $previousStart = $now->copy()->subDays(60);

        return Inertia::render('admin/dashboard', [
            'revenue' => [
                'current' => $this->revenue($periodStart, $now),
                'previous' => $this->revenue($previousStart, $periodStart),
            ],
            'orders' => [
                'current' => Order::query()->whereBetween('created_at', [$periodStart, $now])->count(),
                'previous' => Order::query()->whereBetween('created_at', [$previousStart, $periodStart])->count(),
            ],
            'pendingPayment' => Order::query()->where('status', OrderStatus::PendingPayment)->count(),
            'toShip' => Order::query()->where('status', OrderStatus::Paid)->count(),
            'newCustomers' => User::query()->whereBetween('created_at', [$periodStart, $now])->count(),
            'lowStock' => Product::query()
                ->where('stock', '<=', 5)
                ->orderBy('stock')
                ->limit(5)
                ->get(['id', 'name', 'stock']),
            'bestSellers' => $bestSellers->get(limit: 5, since: $periodStart)
                ->map(fn (BestSeller $row) => [
                    'name' => $row->product->name,
                    'units' => $row->unitsSold,
                    'revenue_cents' => $row->revenueCents,
                ]),
            'recentOrders' => Order::query()
                ->with('user:id,name')
                ->latest('id')
                ->limit(5)
                ->get()
                ->map(fn (Order $order) => [
                    ...(new OrderResource($order))->resolve($request),
                    'customer' => $order->user->name,
                ]),
        ]);
    }

    private function revenue(CarbonInterface $from, CarbonInterface $to): int
    {
        return (int) Order::query()
            ->whereIn('status', self::SOLD)
            ->whereBetween('created_at', [$from, $to])
            ->sum('total_cents');
    }
}
