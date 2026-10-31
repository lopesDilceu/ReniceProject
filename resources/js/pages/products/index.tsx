import { Head, router } from '@inertiajs/react';
import { PackageSearch, Search } from 'lucide-react';
import { useEffect, useState } from 'react';
import ProductCard from '@/components/catalog/product-card';
import Pagination from '@/components/pagination';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { index } from '@/routes/products';
import type { Paginated, Product } from '@/types';

type Filters = {
    q: string | null;
    sort: string;
    available: boolean;
};

type Props = {
    products: Paginated<Product>;
    filters: Filters;
    sortOptions: { value: string; label: string }[];
};

function visit(filters: Filters) {
    router.get(
        index.url(),
        {
            q: filters.q || undefined,
            sort: filters.sort === 'featured' ? undefined : filters.sort,
            available: filters.available ? 1 : undefined,
        },
        { preserveState: true, preserveScroll: true, replace: true },
    );
}

export default function ProductsIndex({
    products,
    filters,
    sortOptions,
}: Props) {
    const [search, setSearch] = useState(filters.q ?? '');

    // Search as the user types, 300 ms after the last keystroke.
    useEffect(() => {
        if (search === (filters.q ?? '')) {
            return;
        }

        const timeout = setTimeout(() => visit({ ...filters, q: search }), 300);

        return () => clearTimeout(timeout);
    }, [search, filters]);

    const { total } = products.meta;

    return (
        <>
            <Head title="Produtos" />

            <div className="mx-auto max-w-6xl space-y-8 px-4 py-10">
                <header className="space-y-1">
                    <h1 className="text-3xl font-semibold tracking-tight">
                        Produtos
                    </h1>
                    <p className="text-muted-foreground">
                        Copos de 300ml e 500ml com gelo pequeno, médio ou
                        grande.
                    </p>
                </header>

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
                            placeholder="Buscar copos..."
                            aria-label="Buscar produtos"
                            className="pl-9"
                        />
                    </div>

                    <Select
                        value={filters.sort}
                        onValueChange={(sort) => visit({ ...filters, sort })}
                    >
                        <SelectTrigger
                            className="sm:w-52"
                            aria-label="Ordenar por"
                        >
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            {sortOptions.map((option) => (
                                <SelectItem
                                    key={option.value}
                                    value={option.value}
                                >
                                    {option.label}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>

                    <div className="flex items-center gap-2">
                        <Checkbox
                            id="available"
                            checked={filters.available}
                            onCheckedChange={(checked) =>
                                visit({
                                    ...filters,
                                    available: checked === true,
                                })
                            }
                        />
                        <Label
                            htmlFor="available"
                            className="whitespace-nowrap"
                        >
                            Somente disponíveis
                        </Label>
                    </div>
                </div>

                <p className="text-sm text-muted-foreground" aria-live="polite">
                    {total === 1 ? '1 produto' : `${total} produtos`}
                </p>

                {products.data.length > 0 ? (
                    <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        {products.data.map((product) => (
                            <ProductCard key={product.id} product={product} />
                        ))}
                    </div>
                ) : (
                    <div className="flex flex-col items-center gap-3 rounded-xl border border-dashed py-16 text-center">
                        <PackageSearch
                            className="size-10 text-muted-foreground"
                            aria-hidden
                        />
                        <p className="font-medium">Nenhum produto encontrado</p>
                        <p className="text-sm text-muted-foreground">
                            Tente outra busca ou remova os filtros.
                        </p>
                    </div>
                )}

                <Pagination links={products.meta.links} />
            </div>
        </>
    );
}
