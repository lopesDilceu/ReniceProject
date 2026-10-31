import { Head, Link, router, usePage } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import InputError from '@/components/input-error';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { formatDate, formatMoney } from '@/lib/format';
import orders from '@/routes/admin/orders';
import type { Order } from '@/types';

type Props = {
    order: Order;
    customer: { name: string; email: string };
    nextStatuses: { value: string; label: string }[];
};

export default function AdminOrderShow({
    order,
    customer,
    nextStatuses,
}: Props) {
    const { errors } = usePage().props as { errors: Record<string, string> };
    const address = order.shipping_address;

    return (
        <>
            <Head title={`Pedido #${order.id} · Admin`} />

            <div className="space-y-6">
                <Link
                    href={orders.index()}
                    className="inline-flex items-center gap-2 text-sm text-muted-foreground hover:text-foreground"
                >
                    <ArrowLeft className="size-4" /> Pedidos
                </Link>

                <div className="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h1 className="text-2xl font-semibold">
                            Pedido #{order.id}
                        </h1>
                        <p className="text-muted-foreground">
                            {formatDate(order.created_at)} ·{' '}
                            <Badge variant="secondary">
                                {order.status_label}
                            </Badge>
                        </p>
                    </div>

                    {nextStatuses.length > 0 && (
                        <div className="space-y-2 text-right">
                            <p className="text-sm text-muted-foreground">
                                Mudar para
                            </p>
                            <div className="flex flex-wrap justify-end gap-2">
                                {nextStatuses.map((status) => (
                                    <Button
                                        key={status.value}
                                        variant={
                                            ['cancelled', 'refunded'].includes(
                                                status.value,
                                            )
                                                ? 'outline'
                                                : 'default'
                                        }
                                        onClick={() =>
                                            router.patch(
                                                orders.update.url(order.id),
                                                { status: status.value },
                                                { preserveScroll: true },
                                            )
                                        }
                                    >
                                        {status.label}
                                    </Button>
                                ))}
                            </div>
                            <InputError message={errors.status} />
                        </div>
                    )}
                </div>

                <div className="grid gap-6 lg:grid-cols-[1fr_18rem]">
                    <section className="rounded-xl border bg-background">
                        <ul className="divide-y">
                            {order.items.map((item) => (
                                <li
                                    key={item.id}
                                    className="flex justify-between gap-4 p-4 text-sm"
                                >
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

                    <aside className="space-y-4">
                        <section className="space-y-1 rounded-xl border bg-background p-4 text-sm">
                            <h2 className="font-medium">Cliente</h2>
                            <p>{customer.name}</p>
                            <p className="text-muted-foreground">
                                {customer.email}
                            </p>
                        </section>
                        <section className="space-y-1 rounded-xl border bg-background p-4 text-sm">
                            <h2 className="font-medium">Entrega</h2>
                            <p className="text-muted-foreground">
                                {address.street}, {address.number}
                                {address.complement
                                    ? ` — ${address.complement}`
                                    : ''}
                                <br />
                                {address.neighborhood}, {address.city}/
                                {address.state}
                                <br />
                                CEP {address.cep}
                            </p>
                        </section>
                    </aside>
                </div>
            </div>
        </>
    );
}
