import { Head, Link, router } from '@inertiajs/react';
import { Bot } from 'lucide-react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import { formatMoney } from '@/lib/format';
import { show as cart } from '@/routes/cart';
import type { Address, CartItem } from '@/types';

type Props = {
    items: CartItem[];
    total_cents: number;
    address: Address;
    /** This page's signed URL; confirming posts back to it. */
    confirmUrl: string;
};

/**
 * A checkout an AI assistant prepared (MCP start-checkout). Nothing is
 * ordered until the customer confirms here, signed in to their account.
 */
export default function ConfirmCheckout({
    items,
    total_cents,
    address,
    confirmUrl,
}: Props) {
    const [processing, setProcessing] = useState(false);

    return (
        <>
            <Head title="Confirmar pedido" />

            <div className="mx-auto max-w-2xl space-y-8 px-4 py-10">
                <header className="space-y-2">
                    <p className="inline-flex items-center gap-2 text-sm text-muted-foreground">
                        <Bot className="size-4" aria-hidden />
                        Preparado por um assistente de IA
                    </p>
                    <h1 className="text-3xl font-semibold tracking-tight">
                        Confirme seu pedido
                    </h1>
                    <p className="text-muted-foreground">
                        Nada foi comprado ainda. Revise os itens e o endereço; o
                        pedido só é feito quando você confirmar.
                    </p>
                </header>

                <section className="rounded-xl border">
                    <ul className="divide-y">
                        {items.map((item) => (
                            <li
                                key={item.id}
                                className="flex justify-between gap-4 p-4 text-sm"
                            >
                                <span>
                                    {item.quantity} × {item.product.name}
                                </span>
                                <span className="font-medium">
                                    {formatMoney(item.subtotal_cents)}
                                </span>
                            </li>
                        ))}
                    </ul>
                    <div className="flex justify-between border-t p-4">
                        <span className="font-medium">Total</span>
                        <span className="text-xl font-semibold">
                            {formatMoney(total_cents)}
                        </span>
                    </div>
                </section>

                <section className="space-y-1 rounded-xl border p-4 text-sm">
                    <h2 className="font-medium">Entrega</h2>
                    <p className="text-muted-foreground">
                        {address.street}, {address.number}
                        {address.complement ? ` — ${address.complement}` : ''}
                        <br />
                        {address.neighborhood}, {address.city}/{address.state} ·
                        CEP {address.cep}
                    </p>
                </section>

                <p className="text-sm text-muted-foreground">
                    Pagamento via Pix, na próxima tela.
                </p>

                <div className="flex flex-col gap-2 sm:flex-row-reverse">
                    <Button
                        size="lg"
                        disabled={processing}
                        onClick={() =>
                            router.post(
                                confirmUrl,
                                {},
                                {
                                    onStart: () => setProcessing(true),
                                    onFinish: () => setProcessing(false),
                                },
                            )
                        }
                    >
                        {processing && <Spinner />}
                        Confirmar pedido
                    </Button>
                    <Button asChild size="lg" variant="outline">
                        <Link href={cart()}>Não, revisar o carrinho</Link>
                    </Button>
                </div>
            </div>
        </>
    );
}
