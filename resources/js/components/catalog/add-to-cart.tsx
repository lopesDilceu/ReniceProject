import { Form, Link, usePage } from '@inertiajs/react';
import { ShoppingCart } from 'lucide-react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { login } from '@/routes';
import { store } from '@/routes/cart/items';
import type { Product } from '@/types';

export default function AddToCart({ product }: { product: Product }) {
    const { auth } = usePage().props;

    if (!product.available) {
        return (
            <Button size="lg" disabled className="w-full sm:w-auto">
                Esgotado
            </Button>
        );
    }

    if (!auth.user) {
        return (
            <Button size="lg" asChild className="w-full sm:w-auto">
                <Link href={login()}>
                    <ShoppingCart className="size-4" /> Entre para comprar
                </Link>
            </Button>
        );
    }

    return (
        <Form
            {...store.form()}
            options={{ preserveScroll: true }}
            className="space-y-2"
        >
            {({ processing, errors }) => (
                <>
                    <input type="hidden" name="product_id" value={product.id} />
                    <div className="flex items-end gap-3">
                        <div className="grid w-24 gap-2">
                            <Label htmlFor="quantity">Quantidade</Label>
                            <Input
                                id="quantity"
                                name="quantity"
                                type="number"
                                min={1}
                                max={99}
                                defaultValue={1}
                                required
                            />
                        </div>
                        <Button
                            size="lg"
                            disabled={processing}
                            className="flex-1 sm:flex-none"
                        >
                            {processing ? (
                                <Spinner />
                            ) : (
                                <ShoppingCart className="size-4" />
                            )}
                            Adicionar ao carrinho
                        </Button>
                    </div>
                    <InputError
                        message={errors.quantity ?? errors.product_id}
                    />
                </>
            )}
        </Form>
    );
}
