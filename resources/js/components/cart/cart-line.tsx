import { Link, router } from '@inertiajs/react';
import { Minus, Plus, Trash2 } from 'lucide-react';
import ProductImage from '@/components/catalog/product-image';
import { Button } from '@/components/ui/button';
import { formatMoney } from '@/lib/format';
import { destroy, update } from '@/routes/cart/items';
import { show as product } from '@/routes/products';
import type { CartItem } from '@/types';

export default function CartLine({
    item,
    editable = true,
}: {
    item: CartItem;
    editable?: boolean;
}) {
    const setQuantity = (quantity: number) =>
        router.patch(
            update.url(item.id),
            { quantity },
            { preserveScroll: true },
        );

    return (
        <li className="flex gap-4 py-4">
            <Link href={product(item.product.slug)} className="w-20 shrink-0">
                <ProductImage
                    src={item.product.image_url}
                    alt={item.product.name}
                    className="rounded-lg border"
                />
            </Link>

            <div className="flex flex-1 flex-col gap-2">
                <div className="flex items-start justify-between gap-4">
                    <Link
                        href={product(item.product.slug)}
                        className="font-medium hover:underline"
                    >
                        {item.product.name}
                    </Link>
                    <span className="font-semibold whitespace-nowrap">
                        {formatMoney(item.subtotal_cents)}
                    </span>
                </div>

                <p className="text-sm text-muted-foreground">
                    {formatMoney(item.product.price_cents)} cada
                </p>

                {editable ? (
                    <div className="flex items-center gap-2">
                        <Button
                            variant="outline"
                            size="icon"
                            className="size-8"
                            aria-label="Diminuir quantidade"
                            disabled={item.quantity <= 1}
                            onClick={() => setQuantity(item.quantity - 1)}
                        >
                            <Minus className="size-3" />
                        </Button>
                        <span
                            className="w-8 text-center text-sm tabular-nums"
                            aria-live="polite"
                        >
                            {item.quantity}
                        </span>
                        <Button
                            variant="outline"
                            size="icon"
                            className="size-8"
                            aria-label="Aumentar quantidade"
                            disabled={item.quantity >= item.max_quantity}
                            onClick={() => setQuantity(item.quantity + 1)}
                        >
                            <Plus className="size-3" />
                        </Button>
                        <Button
                            variant="ghost"
                            size="sm"
                            className="ml-auto text-muted-foreground"
                            onClick={() =>
                                router.delete(destroy.url(item.id), {
                                    preserveScroll: true,
                                })
                            }
                        >
                            <Trash2 className="size-4" /> Remover
                        </Button>
                    </div>
                ) : (
                    <p className="text-sm text-muted-foreground">
                        Quantidade: {item.quantity}
                    </p>
                )}
            </div>
        </li>
    );
}
