import { Head, Link } from '@inertiajs/react';
import { ChevronRight, Package } from 'lucide-react';
import AccountNav from '@/components/account/account-nav';
import Pagination from '@/components/pagination';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { formatDate, formatMoney } from '@/lib/format';
import { show } from '@/routes/orders';
import { index as products } from '@/routes/products';
import type { Order, Paginated } from '@/types';

type OrderSummary = Omit<Order, 'items'> & { items_count: number };

export default function OrdersIndex({
    orders,
}: {
    orders: Paginated<OrderSummary>;
}) {
    return (
        <>
            <Head title="Meus pedidos" />

            <div className="mx-auto max-w-4xl space-y-6 px-4 py-10">
                <h1 className="text-3xl font-semibold tracking-tight">
                    Minha conta
                </h1>
                <AccountNav />

                {orders.data.length === 0 ? (
                    <div className="flex flex-col items-center gap-4 rounded-xl border border-dashed py-16 text-center">
                        <Package
                            className="size-10 text-muted-foreground"
                            aria-hidden
                        />
                        <p className="font-medium">
                            Você ainda não fez nenhum pedido
                        </p>
                        <Button asChild>
                            <Link href={products()}>Ver produtos</Link>
                        </Button>
                    </div>
                ) : (
                    <ul className="divide-y rounded-xl border">
                        {orders.data.map((order) => (
                            <li key={order.id}>
                                <Link
                                    href={show(order.id)}
                                    className="flex items-center gap-4 p-4 transition-colors hover:bg-accent/50"
                                >
                                    <div className="flex-1 space-y-1">
                                        <p className="font-medium">
                                            Pedido #{order.id}{' '}
                                            <Badge
                                                variant="secondary"
                                                className="ml-2"
                                            >
                                                {order.status_label}
                                            </Badge>
                                        </p>
                                        <p className="text-sm text-muted-foreground">
                                            {formatDate(order.created_at)} ·{' '}
                                            {order.items_count}{' '}
                                            {order.items_count === 1
                                                ? 'produto'
                                                : 'produtos'}
                                        </p>
                                    </div>
                                    <span className="font-semibold">
                                        {formatMoney(order.total_cents)}
                                    </span>
                                    <ChevronRight
                                        className="size-4 text-muted-foreground"
                                        aria-hidden
                                    />
                                </Link>
                            </li>
                        ))}
                    </ul>
                )}

                <Pagination links={orders.meta.links} />
            </div>
        </>
    );
}
