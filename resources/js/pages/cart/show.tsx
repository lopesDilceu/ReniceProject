import { Head, Link, usePage } from '@inertiajs/react';
import { ShoppingCart } from 'lucide-react';
import CartLine from '@/components/cart/cart-line';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { formatMoney } from '@/lib/format';
import { show as checkout } from '@/routes/checkout';
import { index as products } from '@/routes/products';
import type { CartItem } from '@/types';

type Props = {
    items: CartItem[];
    total_cents: number;
};

export default function CartShow({ items, total_cents }: Props) {
    const { errors } = usePage().props as { errors: Record<string, string> };
    const units = items.reduce((sum, item) => sum + item.quantity, 0);

    return (
        <>
            <Head title="Carrinho" />

            <div className="mx-auto max-w-4xl space-y-8 px-4 py-10">
                <h1 className="text-3xl font-semibold tracking-tight">
                    Carrinho
                </h1>

                {items.length === 0 ? (
                    <div className="flex flex-col items-center gap-4 rounded-xl border border-dashed py-16 text-center">
                        <ShoppingCart
                            className="size-10 text-muted-foreground"
                            aria-hidden
                        />
                        <p className="font-medium">Seu carrinho está vazio</p>
                        <Button asChild>
                            <Link href={products()}>Ver produtos</Link>
                        </Button>
                    </div>
                ) : (
                    <div className="grid gap-8 md:grid-cols-[1fr_18rem]">
                        <ul className="divide-y rounded-xl border px-4">
                            {items.map((item) => (
                                <CartLine key={item.id} item={item} />
                            ))}
                        </ul>

                        <aside className="h-fit space-y-4 rounded-xl border p-5">
                            <div className="flex justify-between text-sm text-muted-foreground">
                                <span>
                                    {units} {units === 1 ? 'item' : 'itens'}
                                </span>
                            </div>
                            <div className="flex items-baseline justify-between">
                                <span className="font-medium">Total</span>
                                <span className="text-2xl font-semibold">
                                    {formatMoney(total_cents)}
                                </span>
                            </div>
                            <InputError message={errors.quantity} />
                            <Button asChild size="lg" className="w-full">
                                <Link href={checkout()}>Finalizar compra</Link>
                            </Button>
                            <Link
                                href={products()}
                                className="block text-center text-sm text-muted-foreground hover:text-foreground"
                            >
                                Continuar comprando
                            </Link>
                        </aside>
                    </div>
                )}
            </div>
        </>
    );
}
