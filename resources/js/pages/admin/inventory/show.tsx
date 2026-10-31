import { Form, Head, Link } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import InputError from '@/components/input-error';
import Pagination from '@/components/pagination';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { formatDate } from '@/lib/format';
import { cn } from '@/lib/utils';
import inventory from '@/routes/admin/inventory';
import products from '@/routes/admin/products';
import type { AdminProduct, InventoryMovement, SimplePaginated } from '@/types';

type Props = {
    product: AdminProduct;
    movements: SimplePaginated<InventoryMovement>;
};

export default function AdminInventoryShow({ product, movements }: Props) {
    return (
        <>
            <Head title={`Estoque de ${product.name} · Admin`} />

            <div className="space-y-6">
                <Link
                    href={products.index()}
                    className="inline-flex items-center gap-2 text-sm text-muted-foreground hover:text-foreground"
                >
                    <ArrowLeft className="size-4" /> Produtos
                </Link>

                <div className="flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <h1 className="text-2xl font-semibold">
                            {product.name}
                        </h1>
                        <p className="text-muted-foreground">
                            Histórico de estoque
                        </p>
                    </div>
                    <div className="text-right">
                        <p className="text-sm text-muted-foreground">
                            Em estoque
                        </p>
                        <p className="text-3xl font-semibold tabular-nums">
                            {product.stock}
                        </p>
                    </div>
                </div>

                {!product.deleted && (
                    <Form
                        {...inventory.store.form(product.id)}
                        options={{ preserveScroll: true }}
                        resetOnSuccess
                        className="grid gap-4 rounded-xl border bg-background p-5 sm:grid-cols-[8rem_1fr_auto] sm:items-start"
                    >
                        {({ processing, errors }) => (
                            <>
                                <div className="grid gap-2">
                                    <Label htmlFor="quantity">Ajuste</Label>
                                    <Input
                                        id="quantity"
                                        name="quantity"
                                        type="number"
                                        placeholder="-2 ou 10"
                                        required
                                    />
                                    <InputError message={errors.quantity} />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="note">Motivo</Label>
                                    <Input
                                        id="note"
                                        name="note"
                                        placeholder="Contagem física, avaria..."
                                        required
                                        maxLength={255}
                                    />
                                    <InputError message={errors.note} />
                                </div>
                                <Button
                                    className="sm:mt-[1.375rem]"
                                    disabled={processing}
                                >
                                    {processing && <Spinner />}
                                    Registrar ajuste
                                </Button>
                            </>
                        )}
                    </Form>
                )}

                <div className="overflow-x-auto rounded-xl border bg-background">
                    <table className="w-full text-sm">
                        <thead className="border-b text-left text-muted-foreground">
                            <tr>
                                <th className="p-3 font-medium">Data</th>
                                <th className="p-3 font-medium">Tipo</th>
                                <th className="p-3 text-right font-medium">
                                    Quantidade
                                </th>
                                <th className="p-3 text-right font-medium">
                                    Saldo
                                </th>
                                <th className="p-3 font-medium">Origem</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y">
                            {movements.data.map((movement) => (
                                <tr key={movement.id}>
                                    <td className="p-3 whitespace-nowrap">
                                        {formatDate(movement.created_at)}
                                    </td>
                                    <td className="p-3">
                                        <Badge variant="secondary">
                                            {movement.type_label}
                                        </Badge>
                                    </td>
                                    <td
                                        className={cn(
                                            'p-3 text-right font-medium tabular-nums',
                                            movement.quantity > 0
                                                ? 'text-green-600'
                                                : 'text-red-600',
                                        )}
                                    >
                                        {movement.quantity > 0
                                            ? `+${movement.quantity}`
                                            : movement.quantity}
                                    </td>
                                    <td className="p-3 text-right tabular-nums">
                                        {movement.stock_after}
                                    </td>
                                    <td className="p-3 text-muted-foreground">
                                        {[
                                            movement.source,
                                            movement.user,
                                            movement.note,
                                        ]
                                            .filter(Boolean)
                                            .join(' · ')}
                                    </td>
                                </tr>
                            ))}
                            {movements.data.length === 0 && (
                                <tr>
                                    <td
                                        colSpan={5}
                                        className="p-8 text-center text-muted-foreground"
                                    >
                                        Nenhuma movimentação ainda.
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>

                <Pagination links={movements.links} />
            </div>
        </>
    );
}
