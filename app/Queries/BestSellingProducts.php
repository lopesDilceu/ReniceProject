<?php

namespace App\Queries;

use App\Enums\OrderStatus;
use App\Models\Product;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * Products ranked by units sold, then revenue. Replaces v1's
 * vw_ProdutosMaisVendidos, which used COUNT(*) and so ranked a single order
 * of ten units below two orders of one unit each (#15).
 *
 * Only paid orders count as sales: pending, cancelled and refunded orders
 * are excluded.
 */
class BestSellingProducts
{
    /**
     * Statuses that represent money actually received.
     *
     * @var list<OrderStatus>
     */
    private const SOLD = [OrderStatus::Paid, OrderStatus::Shipped, OrderStatus::Delivered];

    /**
     * @return Collection<int, BestSeller>
     */
    public function get(int $limit = 10, ?CarbonInterface $since = null): Collection
    {
        return Product::withTrashed()
            ->select('products.*')
            ->selectRaw('SUM(order_items.quantity) AS units_sold')
            ->selectRaw('SUM(order_items.quantity * order_items.unit_price_cents) AS revenue_cents')
            ->join('order_items', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereIn('orders.status', array_map(fn (OrderStatus $status) => $status->value, self::SOLD))
            ->when($since, fn ($query) => $query->where('orders.created_at', '>=', $since))
            ->groupBy('products.id')
            ->orderByDesc('units_sold')
            ->orderByDesc('revenue_cents')
            ->orderBy('products.id')
            ->limit($limit)
            ->get()
            ->map(fn (Product $product) => new BestSeller(
                product: $product,
                unitsSold: (int) $product->getAttribute('units_sold'),
                revenueCents: (int) $product->getAttribute('revenue_cents'),
            ))
            ->values();
    }
}
