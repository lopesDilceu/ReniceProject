import { Head, Link } from '@inertiajs/react';
import { ArrowDownRight, ArrowUpRight } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { formatDate, formatMoney } from '@/lib/format';
import { cn } from '@/lib/utils';
import inventory from '@/routes/admin/inventory';
import orders from '@/routes/admin/orders';

type Comparison = { current: number; previous: number };

type Props = {
    revenue: Comparison;
    orders: Comparison;
    pendingPayment: number;
    toShip: number;
    newCustomers: number;
    lowStock: { id: number; name: string; stock: number }[];
    bestSellers: { name: string; units: number; revenue_cents: number }[];
    recentOrders: {
        id: number;
        customer: string;
        status_label: string;
        total_cents: number;
        created_at: string;
    }[];
};

function Trend({ current, previous }: Comparison) {
    if (previous === 0) {
        return null;
    }

    const change = Math.round(((current - previous) / previous) * 100);
    const Icon = change >= 0 ? ArrowUpRight : ArrowDownRight;

    return (
        <span
            className={cn(
                'inline-flex items-center text-xs',
                change >= 0 ? 'text-green-600' : 'text-red-600',
            )}
        >
            <Icon className="size-3" /> {Math.abs(change)}% vs. 30 dias
            anteriores
        </span>
    );
}

function Stat({
    label,
    value,
    children,
}: {
    label: string;
    value: string | number;
    children?: React.ReactNode;
}) {
    return (
        <div className="space-y-1 rounded-xl border bg-background p-5">
            <p className="text-sm text-muted-foreground">{label}</p>
            <p className="text-2xl font-semibold tabular-nums">{value}</p>
            {children}
        </div>
    );
}

export default function AdminDashboard(props: Props) {
    const maxUnits = Math.max(1, ...props.bestSellers.map((row) => row.units));

    return (
        <>
            <Head title="Visão geral · Admin" />

            <div className="space-y-8">
                <div>
                    <h1 className="text-2xl font-semibold">Visão geral</h1>
                    <p className="text-muted-foreground">Últimos 30 dias</p>
                </div>

                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <Stat
                        label="Faturamento"
                        value={formatMoney(props.revenue.current)}
                    >
                        <Trend {...props.revenue} />
                    </Stat>
                    <Stat label="Pedidos" value={props.orders.current}>
                        <Trend {...props.orders} />
                    </Stat>
                    <Stat label="Aguardando envio" value={props.toShip}>
                        <span className="text-xs text-muted-foreground">
                            {props.pendingPayment} aguardando pagamento
                        </span>
                    </Stat>
                    <Stat label="Novos clientes" value={props.newCustomers} />
                </div>

                <div className="grid gap-6 lg:grid-cols-2">
                    <section className="space-y-4 rounded-xl border bg-background p-5">
                        <h2 className="font-semibold">Mais vendidos</h2>
                        {props.bestSellers.length === 0 ? (
                            <p className="text-sm text-muted-foreground">
                                Nenhuma venda paga no período.
                            </p>
                        ) : (
                            <ul className="space-y-3">
                                {props.bestSellers.map((row) => (
                                    <li
                                        key={row.name}
                                        className="space-y-1 text-sm"
                                    >
                                        <div className="flex justify-between gap-4">
                                            <span>{row.name}</span>
                                            <span className="text-muted-foreground tabular-nums">
                                                {row.units} un. ·{' '}
                                                {formatMoney(row.revenue_cents)}
                                            </span>
                                        </div>
                                        <div className="h-2 overflow-hidden rounded-full bg-muted">
                                            <div
                                                className="h-full rounded-full bg-primary"
                                                style={{
                                                    width: `${(row.units / maxUnits) * 100}%`,
                                                }}
                                            />
                                        </div>
                                    </li>
                                ))}
                            </ul>
                        )}
                    </section>

                    <section className="space-y-4 rounded-xl border bg-background p-5">
                        <h2 className="font-semibold">Estoque baixo</h2>
                        {props.lowStock.length === 0 ? (
                            <p className="text-sm text-muted-foreground">
                                Todos os produtos com mais de 5 unidades.
                            </p>
                        ) : (
                            <ul className="divide-y text-sm">
                                {props.lowStock.map((product) => (
                                    <li
                                        key={product.id}
                                        className="flex items-center justify-between py-2"
                                    >
                                        <Link
                                            href={inventory.show(product.id)}
                                            className="hover:underline"
                                        >
                                            {product.name}
                                        </Link>
                                        <Badge
                                            variant={
                                                product.stock === 0
                                                    ? 'destructive'
                                                    : 'default'
                                            }
                                        >
                                            {product.stock} un.
                                        </Badge>
                                    </li>
                                ))}
                            </ul>
                        )}
                    </section>
                </div>

                <section className="space-y-4 rounded-xl border bg-background p-5">
                    <div className="flex items-center justify-between">
                        <h2 className="font-semibold">Pedidos recentes</h2>
                        <Link
                            href={orders.index()}
                            className="text-sm text-muted-foreground hover:text-foreground"
                        >
                            Ver todos
                        </Link>
                    </div>
                    <ul className="divide-y text-sm">
                        {props.recentOrders.map((order) => (
                            <li key={order.id}>
                                <Link
                                    href={orders.show(order.id)}
                                    className="flex items-center justify-between gap-4 py-2 hover:underline"
                                >
                                    <span>
                                        #{order.id} · {order.customer}
                                        <span className="ml-2 text-muted-foreground">
                                            {formatDate(order.created_at)}
                                        </span>
                                    </span>
                                    <span className="flex items-center gap-3">
                                        <Badge variant="secondary">
                                            {order.status_label}
                                        </Badge>
                                        <span className="font-medium tabular-nums">
                                            {formatMoney(order.total_cents)}
                                        </span>
                                    </span>
                                </Link>
                            </li>
                        ))}
                    </ul>
                </section>
            </div>
        </>
    );
}
