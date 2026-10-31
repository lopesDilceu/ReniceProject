import { Form, Head, Link } from '@inertiajs/react';
import { MapPin, Plus } from 'lucide-react';
import { useState } from 'react';
import AddressForm from '@/components/address-form';
import CartLine from '@/components/cart/cart-line';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import { formatMoney } from '@/lib/format';
import { cn } from '@/lib/utils';
import { show as cart } from '@/routes/cart';
import { store } from '@/routes/checkout';
import type { Address, CartItem } from '@/types';

type Props = {
    items: CartItem[];
    total_cents: number;
    addresses: Address[];
};

export default function CheckoutShow({ items, total_cents, addresses }: Props) {
    const [addressId, setAddressId] = useState<number | undefined>(
        addresses[0]?.id,
    );
    const [adding, setAdding] = useState(addresses.length === 0);
    const selected = addresses.some((address) => address.id === addressId)
        ? addressId
        : addresses[0]?.id;

    return (
        <>
            <Head title="Finalizar compra" />

            <div className="mx-auto max-w-5xl space-y-8 px-4 py-10">
                <h1 className="text-3xl font-semibold tracking-tight">
                    Finalizar compra
                </h1>

                <div className="grid gap-8 md:grid-cols-[1fr_20rem]">
                    <section className="space-y-4">
                        <h2 className="text-lg font-semibold">
                            Endereço de entrega
                        </h2>

                        {addresses.length > 0 && (
                            <fieldset
                                className="grid gap-3"
                                aria-label="Endereços salvos"
                            >
                                {addresses.map((address) => (
                                    <label
                                        key={address.id}
                                        className={cn(
                                            'flex cursor-pointer gap-3 rounded-xl border p-4 transition-colors',
                                            selected === address.id &&
                                                'border-primary bg-primary/5',
                                        )}
                                    >
                                        <input
                                            type="radio"
                                            name="address_choice"
                                            className="mt-1 accent-[var(--primary)]"
                                            checked={selected === address.id}
                                            onChange={() =>
                                                setAddressId(address.id)
                                            }
                                        />
                                        <span className="text-sm">
                                            <span className="font-medium">
                                                {address.street},{' '}
                                                {address.number}
                                                {address.complement
                                                    ? ` — ${address.complement}`
                                                    : ''}
                                            </span>
                                            <br />
                                            <span className="text-muted-foreground">
                                                {address.neighborhood},{' '}
                                                {address.city}/{address.state} ·
                                                CEP {address.cep}
                                            </span>
                                        </span>
                                    </label>
                                ))}
                            </fieldset>
                        )}

                        {adding ? (
                            <div className="space-y-4 rounded-xl border p-5">
                                <h3 className="flex items-center gap-2 font-medium">
                                    <MapPin className="size-4" /> Novo endereço
                                </h3>
                                <AddressForm onSaved={() => setAdding(false)} />
                            </div>
                        ) : (
                            <Button
                                variant="outline"
                                onClick={() => setAdding(true)}
                            >
                                <Plus className="size-4" /> Adicionar endereço
                            </Button>
                        )}

                        <h2 className="pt-4 text-lg font-semibold">Itens</h2>
                        <ul className="divide-y rounded-xl border px-4">
                            {items.map((item) => (
                                <CartLine
                                    key={item.id}
                                    item={item}
                                    editable={false}
                                />
                            ))}
                        </ul>
                        <Link
                            href={cart()}
                            className="text-sm text-muted-foreground hover:text-foreground"
                        >
                            Alterar itens no carrinho
                        </Link>
                    </section>

                    <aside className="h-fit space-y-4 rounded-xl border p-5">
                        <div className="flex items-baseline justify-between">
                            <span className="font-medium">Total</span>
                            <span className="text-2xl font-semibold">
                                {formatMoney(total_cents)}
                            </span>
                        </div>
                        <p className="text-xs text-muted-foreground">
                            O pagamento via Pix chega na próxima fase do
                            projeto. Por enquanto o pedido fica como "Aguardando
                            pagamento".
                        </p>

                        <Form {...store.form()} disableWhileProcessing>
                            {({ processing, errors }) => (
                                <div className="space-y-3">
                                    <input
                                        type="hidden"
                                        name="address_id"
                                        value={selected ?? ''}
                                    />
                                    <InputError
                                        message={
                                            errors.address_id ?? errors.cart
                                        }
                                    />
                                    <Button
                                        size="lg"
                                        className="w-full"
                                        disabled={processing || !selected}
                                    >
                                        {processing && <Spinner />}
                                        Fazer pedido
                                    </Button>
                                </div>
                            )}
                        </Form>
                    </aside>
                </div>
            </div>
        </>
    );
}
