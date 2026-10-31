import { Head, Link, router } from '@inertiajs/react';
import { Search } from 'lucide-react';
import { useEffect, useState } from 'react';
import LiveUpdates, { LiveIndicator } from '@/components/live-updates';
import type { OrderUpdated } from '@/components/live-updates';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { formatDate, formatMoney } from '@/lib/format';
import { cn } from '@/lib/utils';
import orders from '@/routes/admin/orders';
import type { SimplePaginated } from '@/types';
import Pagination from '@/components/pagination';

type Row = {
    id: number;
    customer: string;
    status: string;
    status_label: string;
    total_cents: number;
    items_count: number;
    created_at: string;
};

type Props = {
    orders: SimplePaginated<Row>;
    filters: { status: string | null; q: string };
    statuses: { value: string; label: string }[];
};

function visit(filters: Props['filters']) {
    router.get(
        orders.index.url(),
        { status: filters.status ?? undefined, q: filters.q || undefined },
        { preserveState: true, preserveScroll: true, replace: true },
    );
}

export default function AdminOrdersIndex({
    orders: list,
    filters,
    statuses,
}: Props) {
    const [search, setSearch] = useState(filters.q);

    useEffect(() => {
        if (search === filters.q) {
            return;
        }

        const timeout = setTimeout(() => visit({ ...filters, q: search }), 300);

        return () => clearTimeout(timeout);
    }, [search, filters]);

    const tabs = [{ value: null, label: 'Todos' }, ...statuses];

    return (
        <>
            <Head title="Pedidos · Admin" />

            <LiveUpdates<OrderUpdated>
                channel="admin"
                events={['order.updated']}
                onEvent={() => router.reload({ only: ['orders'] })}
            />

            <div className="space-y-6">
                <div className="flex items-center justify-between gap-4">
                    <h1 className="text-2xl font-semibold">Pedidos</h1>
                    <LiveIndicator />
                </div>

                <div
                    className="flex flex-wrap gap-2"
                    role="tablist"
                    aria-label="Filtrar por status"
                >
                    {tabs.map((tab) => (
                        <button
                            key={tab.label}
                            type="button"
                            role="tab"
                            aria-selected={filters.status === tab.value}
                            onClick={() =>
                                visit({ ...filters, status: tab.value })
                            }
                            className={cn(
                                'rounded-full border px-3 py-1 text-sm transition-colors hover:bg-accent',
                                filters.status === tab.value &&
                                    'border-primary bg-primary text-primary-foreground hover:bg-primary',
                            )}
                        >
                            {tab.label}
                        </button>
                    ))}
                </div>

                <div className="relative max-w-md">
                    <Search
                        className="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                        aria-hidden
                    />
                    <Input
                        type="search"
                        value={search}
                        onChange={(event) => setSearch(event.target.value)}
                        placeholder="Número do pedido, nome ou e-mail"
                        aria-label="Buscar pedidos"
                        className="pl-9"
                    />
                </div>

                <div className="overflow-x-auto rounded-xl border bg-background">
                    <table className="w-full text-sm">
                        <thead className="border-b text-left text-muted-foreground">
                            <tr>
                                <th className="p-3 font-medium">Pedido</th>
                                <th className="p-3 font-medium">Cliente</th>
                                <th className="p-3 font-medium">Data</th>
                                <th className="p-3 font-medium">Status</th>
                                <th className="p-3 text-right font-medium">
                                    Total
                                </th>
                            </tr>
                        </thead>
                        <tbody className="divide-y">
                            {list.data.map((order) => (
                                <tr
                                    key={order.id}
                                    className="hover:bg-accent/50"
                                >
                                    <td className="p-3">
                                        <Link
                                            href={orders.show(order.id)}
                                            className="font-medium hover:underline"
                                        >
                                            #{order.id}
                                        </Link>
                                        <span className="ml-2 text-muted-foreground">
                                            {order.items_count}{' '}
                                            {order.items_count === 1
                                                ? 'item'
                                                : 'itens'}
                                        </span>
                                    </td>
                                    <td className="p-3">{order.customer}</td>
                                    <td className="p-3 whitespace-nowrap">
                                        {formatDate(order.created_at)}
                                    </td>
                                    <td className="p-3">
                                        <Badge variant="secondary">
                                            {order.status_label}
                                        </Badge>
                                    </td>
                                    <td className="p-3 text-right font-medium tabular-nums">
                                        {formatMoney(order.total_cents)}
                                    </td>
                                </tr>
                            ))}
                            {list.data.length === 0 && (
                                <tr>
                                    <td
                                        colSpan={5}
                                        className="p-8 text-center text-muted-foreground"
                                    >
                                        Nenhum pedido encontrado.
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>

                <Pagination links={list.links} />
            </div>
        </>
    );
}
