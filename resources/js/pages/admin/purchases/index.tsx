import { Form, Head } from '@inertiajs/react';
import InputError from '@/components/input-error';
import Pagination from '@/components/pagination';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { formatDate, formatMoney } from '@/lib/format';
import purchases from '@/routes/admin/purchases';
import type { SimplePaginated, SupplierPurchase } from '@/types';

type Props = {
    purchases: SimplePaginated<SupplierPurchase>;
    products: { id: number; name: string }[];
};

export default function AdminPurchasesIndex({
    purchases: list,
    products,
}: Props) {
    return (
        <>
            <Head title="Compras · Admin" />

            <div className="space-y-6">
                <div>
                    <h1 className="text-2xl font-semibold">
                        Compras de fornecedor
                    </h1>
                    <p className="text-muted-foreground">
                        Cada compra entra no estoque. O custo fica registrado
                        aqui e nunca altera o preço de venda.
                    </p>
                </div>

                <Form
                    {...purchases.store.form()}
                    options={{ preserveScroll: true }}
                    resetOnSuccess
                    className="grid gap-4 rounded-xl border bg-background p-5 sm:grid-cols-6"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2 sm:col-span-3">
                                <Label htmlFor="product_id">Produto</Label>
                                <select
                                    id="product_id"
                                    name="product_id"
                                    required
                                    defaultValue=""
                                    className="h-9 rounded-md border bg-background px-3 text-sm"
                                >
                                    <option value="" disabled>
                                        Escolha um produto
                                    </option>
                                    {products.map((product) => (
                                        <option
                                            key={product.id}
                                            value={product.id}
                                        >
                                            {product.name}
                                        </option>
                                    ))}
                                </select>
                                <InputError message={errors.product_id} />
                            </div>
                            <div className="grid gap-2 sm:col-span-3">
                                <Label htmlFor="supplier">Fornecedor</Label>
                                <Input
                                    id="supplier"
                                    name="supplier"
                                    required
                                    maxLength={100}
                                />
                                <InputError message={errors.supplier} />
                            </div>
                            <div className="grid gap-2 sm:col-span-2">
                                <Label htmlFor="quantity">Quantidade</Label>
                                <Input
                                    id="quantity"
                                    name="quantity"
                                    type="number"
                                    min={1}
                                    required
                                />
                                <InputError message={errors.quantity} />
                            </div>
                            <div className="grid gap-2 sm:col-span-2">
                                <Label htmlFor="unit_cost">
                                    Custo unitário (R$)
                                </Label>
                                <Input
                                    id="unit_cost"
                                    name="unit_cost"
                                    inputMode="decimal"
                                    placeholder="4,50"
                                    required
                                />
                                <InputError
                                    message={
                                        errors.unit_cost ??
                                        errors.unit_cost_cents
                                    }
                                />
                            </div>
                            <div className="grid gap-2 sm:col-span-2">
                                <Label htmlFor="purchased_at">Data</Label>
                                <Input
                                    id="purchased_at"
                                    name="purchased_at"
                                    type="date"
                                />
                                <InputError message={errors.purchased_at} />
                            </div>
                            <div className="sm:col-span-6">
                                <Button disabled={processing}>
                                    {processing && <Spinner />}
                                    Registrar compra
                                </Button>
                            </div>
                        </>
                    )}
                </Form>

                <div className="overflow-x-auto rounded-xl border bg-background">
                    <table className="w-full text-sm">
                        <thead className="border-b text-left text-muted-foreground">
                            <tr>
                                <th className="p-3 font-medium">Data</th>
                                <th className="p-3 font-medium">Produto</th>
                                <th className="p-3 font-medium">Fornecedor</th>
                                <th className="p-3 text-right font-medium">
                                    Qtd.
                                </th>
                                <th className="p-3 text-right font-medium">
                                    Custo un.
                                </th>
                                <th className="p-3 text-right font-medium">
                                    Total
                                </th>
                            </tr>
                        </thead>
                        <tbody className="divide-y">
                            {list.data.map((purchase) => (
                                <tr key={purchase.id}>
                                    <td className="p-3 whitespace-nowrap">
                                        {formatDate(purchase.purchased_at)}
                                    </td>
                                    <td className="p-3">{purchase.product}</td>
                                    <td className="p-3">{purchase.supplier}</td>
                                    <td className="p-3 text-right tabular-nums">
                                        {purchase.quantity}
                                    </td>
                                    <td className="p-3 text-right">
                                        {formatMoney(purchase.unit_cost_cents)}
                                    </td>
                                    <td className="p-3 text-right font-medium">
                                        {formatMoney(purchase.total_cents)}
                                    </td>
                                </tr>
                            ))}
                            {list.data.length === 0 && (
                                <tr>
                                    <td
                                        colSpan={6}
                                        className="p-8 text-center text-muted-foreground"
                                    >
                                        Nenhuma compra registrada.
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
