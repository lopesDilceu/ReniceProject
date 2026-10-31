import { Head, router, usePage } from '@inertiajs/react';
import { Search } from 'lucide-react';
import { useEffect, useState } from 'react';
import Pagination from '@/components/pagination';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { formatDate } from '@/lib/format';
import customers from '@/routes/admin/customers';
import type { SimplePaginated } from '@/types';

type Customer = {
    id: number;
    name: string;
    email: string;
    email_verified: boolean;
    is_admin: boolean;
    orders_count: number;
    created_at: string;
};

type Props = {
    customers: SimplePaginated<Customer>;
    filters: { q: string };
};

export default function AdminCustomersIndex({
    customers: list,
    filters,
}: Props) {
    const { auth } = usePage().props;
    const [search, setSearch] = useState(filters.q);

    useEffect(() => {
        if (search === filters.q) {
            return;
        }

        const timeout = setTimeout(
            () =>
                router.get(
                    customers.index.url(),
                    { q: search || undefined },
                    {
                        preserveState: true,
                        preserveScroll: true,
                        replace: true,
                    },
                ),
            300,
        );

        return () => clearTimeout(timeout);
    }, [search, filters]);

    return (
        <>
            <Head title="Clientes · Admin" />

            <div className="space-y-6">
                <h1 className="text-2xl font-semibold">Clientes</h1>

                <div className="relative max-w-md">
                    <Search
                        className="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                        aria-hidden
                    />
                    <Input
                        type="search"
                        value={search}
                        onChange={(event) => setSearch(event.target.value)}
                        placeholder="Nome ou e-mail"
                        aria-label="Buscar clientes"
                        className="pl-9"
                    />
                </div>

                <div className="overflow-x-auto rounded-xl border bg-background">
                    <table className="w-full text-sm">
                        <thead className="border-b text-left text-muted-foreground">
                            <tr>
                                <th className="p-3 font-medium">Nome</th>
                                <th className="p-3 font-medium">Desde</th>
                                <th className="p-3 text-right font-medium">
                                    Pedidos
                                </th>
                                <th className="p-3 text-right font-medium">
                                    Acesso
                                </th>
                            </tr>
                        </thead>
                        <tbody className="divide-y">
                            {list.data.map((customer) => (
                                <tr key={customer.id}>
                                    <td className="p-3">
                                        <p className="font-medium">
                                            {customer.name}
                                        </p>
                                        <p className="text-muted-foreground">
                                            {customer.email}
                                            {!customer.email_verified && (
                                                <Badge
                                                    variant="outline"
                                                    className="ml-2"
                                                >
                                                    não confirmado
                                                </Badge>
                                            )}
                                        </p>
                                    </td>
                                    <td className="p-3 whitespace-nowrap">
                                        {formatDate(customer.created_at)}
                                    </td>
                                    <td className="p-3 text-right tabular-nums">
                                        {customer.orders_count}
                                    </td>
                                    <td className="p-3">
                                        <div className="flex items-center justify-end gap-2">
                                            {customer.is_admin && (
                                                <Badge>Admin</Badge>
                                            )}
                                            {customer.id !== auth.user?.id && (
                                                <Button
                                                    variant="ghost"
                                                    size="sm"
                                                    onClick={() =>
                                                        router.patch(
                                                            customers.toggleAdmin.url(
                                                                customer.id,
                                                            ),
                                                            {},
                                                            {
                                                                preserveScroll: true,
                                                            },
                                                        )
                                                    }
                                                >
                                                    {customer.is_admin
                                                        ? 'Remover admin'
                                                        : 'Tornar admin'}
                                                </Button>
                                            )}
                                        </div>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>

                <Pagination links={list.links} />
            </div>
        </>
    );
}
