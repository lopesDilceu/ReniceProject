import { Head, Link } from '@inertiajs/react';
import { CircleCheck } from 'lucide-react';
import OrderTimeline from '@/components/orders/order-timeline';
import ReviewForm from '@/components/orders/review-form';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { formatDate, formatMoney } from '@/lib/format';
import { index as products } from '@/routes/products';
import type { Order } from '@/types';

type Props = {
    order: Order;
    reviewableProductIds: number[];
};

export default function OrderShow({ order, reviewableProductIds }: Props) {
    const address = order.shipping_address;

    return (
        <>
            <Head title={`Pedido #${order.id}`} />

            <div className="mx-auto max-w-3xl space-y-8 px-4 py-10">
                <header className="space-y-3 text-center">
                    <CircleCheck
                        className="mx-auto size-12 text-primary"
                        aria-hidden
                    />
                    <h1 className="text-3xl font-semibold tracking-tight">
                        Pedido #{order.id}
                    </h1>
                    <p className="text-muted-foreground">
                        Feito em {formatDate(order.created_at)} ·{' '}
                        <Badge variant="secondary">{order.status_label}</Badge>
                    </p>
                </header>

                <section className="rounded-xl border">
                    <ul className="divide-y">
                        {order.items.map((item) => (
                            <li key={item.id} className="space-y-3 p-4 text-sm">
                                <div className="flex justify-between gap-4">
                                    <span>
                                        {item.quantity} × {item.product_name}
                                        <span className="block text-muted-foreground">
                                            {formatMoney(item.unit_price_cents)}{' '}
                                            cada
                                        </span>
                                    </span>
                                    <span className="font-medium">
                                        {formatMoney(item.subtotal_cents)}
                                    </span>
                                </div>
                                {item.product_slug &&
                                    reviewableProductIds.includes(
                                        item.product_id,
                                    ) && (
                                        <ReviewForm
                                            productSlug={item.product_slug}
                                        />
                                    )}
                            </li>
                        ))}
                    </ul>
                    <div className="flex justify-between border-t p-4">
                        <span className="font-medium">Total</span>
                        <span className="text-xl font-semibold">
                            {formatMoney(order.total_cents)}
                        </span>
                    </div>
                </section>

                <section className="space-y-4 rounded-xl border p-4">
                    <h2 className="text-sm font-medium">Acompanhamento</h2>
                    <OrderTimeline timeline={order.timeline} />
                </section>

                <section className="space-y-1 rounded-xl border p-4 text-sm">
                    <h2 className="font-medium">Entrega</h2>
                    <p className="text-muted-foreground">
                        {address.street}, {address.number}
                        {address.complement ? ` — ${address.complement}` : ''}
                        <br />
                        {address.neighborhood}, {address.city}/{address.state} ·
                        CEP {address.cep}
                    </p>
                </section>

                <div className="text-center">
                    <Button asChild variant="outline">
                        <Link href={products()}>Continuar comprando</Link>
                    </Button>
                </div>
            </div>
        </>
    );
}
