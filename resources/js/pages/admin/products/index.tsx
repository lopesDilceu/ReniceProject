import { Head, Link, router } from '@inertiajs/react';
import { Boxes, Pencil, Plus, RotateCcw, Search, Trash2 } from 'lucide-react';
import { useEffect, useState } from 'react';
import ProductImage from '@/components/catalog/product-image';
import Pagination from '@/components/pagination';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { formatMoney } from '@/lib/format';
import inventory from '@/routes/admin/inventory';
import products from '@/routes/admin/products';
import type { AdminProduct, Paginated } from '@/types';

type Props = {
    products: Paginated<AdminProduct>;
    filters: { q: string; removed: boolean };
};

function visit(filters: Props['filters']) {
    router.get(
        products.index.url(),
        { q: filters.q || undefined, removed: filters.removed ? 1 : undefined },
        { preserveState: true, preserveScroll: true, replace: true },
    );
}

function StockBadge({ stock }: { stock: number }) {
    if (stock === 0) {
        return <Badge variant="destructive">Sem estoque</Badge>;
    }

    return (
        <Badge variant={stock <= 5 ? 'default' : 'secondary'}>
            {stock} un.
        </Badge>
    );
}

export default function AdminProductsIndex({ products: list, filters }: Props) {
    const [search, setSearch] = useState(filters.q);

    useEffect(() => {
        if (search === filters.q) {
            return;
        }

        const timeout = setTimeout(() => visit({ ...filters, q: search }), 300);

        return () => clearTimeout(timeout);
    }, [search, filters]);

    return (
        <>
            <Head title="Produtos · Admin" />

            <div className="space-y-6">
                <div className="flex flex-wrap items-center justify-between gap-4">
                    <h1 className="text-2xl font-semibold">Produtos</h1>
                    <Button asChild>
                        <Link href={products.create()}>
                            <Plus className="size-4" /> Novo produto
                        </Link>
                    </Button>
                </div>

                <div className="flex flex-col gap-4 sm:flex-row sm:items-center">
                    <div className="relative flex-1">
                        <Search
                            className="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                            aria-hidden
                        />
                        <Input
                            type="search"
                            value={search}
                            onChange={(event) => setSearch(event.target.value)}
                            placeholder="Buscar por nome"
                            aria-label="Buscar produtos"
                            className="pl-9"
                        />
                    </div>
                    <div className="flex items-center gap-2">
                        <Checkbox
                            id="removed"
                            checked={filters.removed}
                            onCheckedChange={(checked) =>
                                visit({ ...filters, removed: checked === true })
                            }
                        />
                        <Label htmlFor="removed">Mostrar removidos</Label>
                    </div>
                </div>

                <div className="overflow-x-auto rounded-xl border bg-background">
                    <table className="w-full text-sm">
                        <thead className="border-b text-left text-muted-foreground">
                            <tr>
                                <th className="p-3 font-medium">Produto</th>
                                <th className="p-3 font-medium">Preço</th>
                                <th className="p-3 font-medium">Estoque</th>
                                <th className="p-3 text-right font-medium">
                                    <span className="sr-only">Ações</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody className="divide-y">
                            {list.data.map((product) => (
                                <tr
                                    key={product.id}
                                    className={
                                        product.deleted
                                            ? 'opacity-60'
                                            : undefined
                                    }
                                >
                                    <td className="p-3">
                                        <div className="flex items-center gap-3">
                                            <ProductImage
                                                src={product.image_url}
                                                alt=""
                                                className="size-10 rounded-md border"
                                            />
                                            <span className="font-medium">
                                                {product.name}
                                            </span>
                                        </div>
                                    </td>
                                    <td className="p-3 whitespace-nowrap">
                                        {formatMoney(product.price_cents)}
                                    </td>
                                    <td className="p-3">
                                        <StockBadge stock={product.stock} />
                                    </td>
                                    <td className="p-3">
                                        <div className="flex justify-end gap-1">
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                asChild
                                            >
                                                <Link
                                                    href={inventory.show(
                                                        product.id,
                                                    )}
                                                    aria-label={`Estoque de ${product.name}`}
                                                >
                                                    <Boxes className="size-4" />
                                                </Link>
                                            </Button>
                                            {product.deleted ? (
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    aria-label={`Restaurar ${product.name}`}
                                                    onClick={() =>
                                                        router.patch(
                                                            products.restore.url(
                                                                product.id,
                                                            ),
                                                            {},
                                                            {
                                                                preserveScroll: true,
                                                            },
                                                        )
                                                    }
                                                >
                                                    <RotateCcw className="size-4" />
                                                </Button>
                                            ) : (
                                                <>
                                                    <Button
                                                        variant="ghost"
                                                        size="icon"
                                                        asChild
                                                    >
                                                        <Link
                                                            href={products.edit(
                                                                product.id,
                                                            )}
                                                            aria-label={`Editar ${product.name}`}
                                                        >
                                                            <Pencil className="size-4" />
                                                        </Link>
                                                    </Button>
                                                    <Button
                                                        variant="ghost"
                                                        size="icon"
                                                        aria-label={`Remover ${product.name}`}
                                                        onClick={() =>
                                                            router.delete(
                                                                products.destroy.url(
                                                                    product.id,
                                                                ),
                                                                {
                                                                    preserveScroll: true,
                                                                },
                                                            )
                                                        }
                                                    >
                                                        <Trash2 className="size-4" />
                                                    </Button>
                                                </>
                                            )}
                                        </div>
                                    </td>
                                </tr>
                            ))}
                            {list.data.length === 0 && (
                                <tr>
                                    <td
                                        colSpan={4}
                                        className="p-8 text-center text-muted-foreground"
                                    >
                                        Nenhum produto encontrado.
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>

                <Pagination links={list.meta.links} />
            </div>
        </>
    );
}
