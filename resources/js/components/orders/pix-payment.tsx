import { router, usePoll } from '@inertiajs/react';
import { Check, Copy, QrCode } from 'lucide-react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { formatDateTime, formatMoney } from '@/lib/format';
import pix from '@/routes/orders/pix';
import type { Payment } from '@/types';

type Props = {
    orderId: number;
    payment: Payment | null;
    canSimulate: boolean;
};

/**
 * Pix QR code and "copia e cola" for an unpaid order. While mounted it
 * reloads the order every few seconds, so the page turns to "Pago" as soon
 * as the provider's webhook confirms the payment.
 */
export default function PixPayment({ orderId, payment, canSimulate }: Props) {
    const [copied, setCopied] = useState(false);

    usePoll(5000, { only: ['order', 'payment'] });

    if (!payment?.pix_code || !payment.pix_qr_image) {
        return (
            <section className="space-y-3 rounded-xl border p-6 text-center">
                <QrCode
                    className="mx-auto size-8 text-muted-foreground"
                    aria-hidden
                />
                <p className="text-sm text-muted-foreground">
                    O Pix deste pedido ainda não foi gerado.
                </p>
                <Button
                    onClick={() =>
                        router.post(
                            pix.store.url(orderId),
                            {},
                            { preserveScroll: true },
                        )
                    }
                >
                    Gerar Pix
                </Button>
            </section>
        );
    }

    const copy = async () => {
        await navigator.clipboard.writeText(payment.pix_code ?? '');
        setCopied(true);
        setTimeout(() => setCopied(false), 2000);
    };

    return (
        <section className="space-y-4 rounded-xl border p-6">
            <div className="space-y-1 text-center">
                <h2 className="font-medium">Pague com Pix</h2>
                <p className="text-sm text-muted-foreground">
                    {formatMoney(payment.amount_cents)} · válido até{' '}
                    <time dateTime={payment.expires_at}>
                        {formatDateTime(payment.expires_at)}
                    </time>
                </p>
            </div>

            <img
                src={payment.pix_qr_image}
                alt="QR code do Pix"
                className="mx-auto size-56 rounded-lg bg-white p-2"
            />

            <div className="flex gap-2">
                <Input
                    readOnly
                    value={payment.pix_code}
                    aria-label="Pix copia e cola"
                    onFocus={(event) => event.target.select()}
                    className="font-mono text-xs"
                />
                <Button variant="outline" onClick={copy}>
                    {copied ? (
                        <Check className="size-4" aria-hidden />
                    ) : (
                        <Copy className="size-4" aria-hidden />
                    )}
                    {copied ? 'Copiado' : 'Copiar'}
                </Button>
            </div>

            <p
                className="text-center text-xs text-muted-foreground"
                role="status"
            >
                A confirmação aparece aqui automaticamente.
            </p>

            {canSimulate && (
                <div className="rounded-lg border border-dashed p-3 text-center text-sm">
                    <p className="mb-2 text-muted-foreground">
                        Ambiente de demonstração: nenhum dinheiro é cobrado.
                    </p>
                    <Button
                        variant="secondary"
                        onClick={() =>
                            router.post(
                                pix.simulate.url(orderId),
                                {},
                                { preserveScroll: true },
                            )
                        }
                    >
                        Simular pagamento
                    </Button>
                </div>
            )}
        </section>
    );
}
