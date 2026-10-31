import { Form, router } from '@inertiajs/react';
import { ShieldCheck } from 'lucide-react';
import { useEffect, useState } from 'react';
import InputError from '@/components/input-error';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import twoFactor from '@/routes/two-factor';

type Props = {
    enabled: boolean;
    /** Set up but not yet confirmed with a code (only on this page view). */
    pending: boolean;
};

async function getJson<T>(url: string): Promise<T> {
    const response = await fetch(url, {
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
    });

    if (!response.ok) {
        throw new Error(`HTTP ${response.status}`);
    }

    return (await response.json()) as T;
}

/**
 * TOTP two-factor through Fortify: enable → scan the QR code → confirm
 * with a code → save the recovery codes. Fortify discards a setup that is
 * not confirmed before the page is loaded again.
 */
export default function TwoFactorSettings({ enabled, pending }: Props) {
    const [setup, setSetup] = useState<{ svg: string; key: string } | null>(
        null,
    );
    const [recoveryCodes, setRecoveryCodes] = useState<string[] | null>(null);
    const [error, setError] = useState<string | null>(null);
    const [busy, setBusy] = useState(false);

    useEffect(() => {
        if (!pending) {
            return;
        }

        Promise.all([
            getJson<{ svg: string }>(twoFactor.qrCode.url()),
            getJson<{ secretKey: string }>(twoFactor.secretKey.url()),
        ])
            .then(([qr, secret]) =>
                setSetup({ svg: qr.svg, key: secret.secretKey }),
            )
            .catch(() =>
                setError(
                    'Não foi possível carregar o QR code. Recarregue a página e tente de novo.',
                ),
            );
    }, [pending]);

    const showRecoveryCodes = () =>
        getJson<string[]>(twoFactor.recoveryCodes.url())
            .then(setRecoveryCodes)
            .catch(() =>
                setError(
                    'Não foi possível carregar os códigos de recuperação.',
                ),
            );

    const post = (
        action: 'enable' | 'regenerate' | 'disable',
        onSuccess?: () => void,
    ) => {
        const options = {
            preserveScroll: true,
            onStart: () => setBusy(true),
            onFinish: () => setBusy(false),
            onSuccess,
        };

        if (action === 'enable') {
            router.post(twoFactor.enable.url(), {}, options);
        } else if (action === 'regenerate') {
            router.post(twoFactor.regenerateRecoveryCodes.url(), {}, options);
        } else {
            router.delete(twoFactor.disable.url(), options);
        }
    };

    return (
        <div className="space-y-4">
            {error && <InputError message={error} />}

            {!enabled && !pending && (
                <Button onClick={() => post('enable')} disabled={busy}>
                    {busy && <Spinner />}
                    Ativar verificação em duas etapas
                </Button>
            )}

            {pending && (
                <div className="space-y-4 rounded-lg border p-4">
                    <p className="text-sm">
                        1. Escaneie o QR code com um aplicativo autenticador
                        (Google Authenticator, Microsoft Authenticator,
                        1Password…).
                    </p>
                    {setup ? (
                        <>
                            <div
                                className="mx-auto w-fit rounded-lg bg-white p-3"
                                // SVG rendered by Fortify on our server.
                                dangerouslySetInnerHTML={{ __html: setup.svg }}
                            />
                            <p className="text-center text-xs text-muted-foreground">
                                Ou digite a chave:{' '}
                                <code className="font-mono break-all text-foreground">
                                    {setup.key}
                                </code>
                            </p>
                        </>
                    ) : (
                        !error && <Spinner className="mx-auto" />
                    )}

                    <Form
                        {...twoFactor.confirm.form()}
                        errorBag="confirmTwoFactorAuthentication"
                        options={{ preserveScroll: true }}
                        onSuccess={showRecoveryCodes}
                        resetOnError
                        className="grid max-w-xs gap-2"
                    >
                        {({ processing, errors }) => (
                            <>
                                <Label htmlFor="code">
                                    2. Digite o código de 6 dígitos
                                </Label>
                                <div className="flex gap-2">
                                    <Input
                                        id="code"
                                        name="code"
                                        inputMode="numeric"
                                        pattern="[0-9]{6}"
                                        maxLength={6}
                                        autoComplete="one-time-code"
                                        required
                                        className="font-mono tracking-[0.3em]"
                                    />
                                    <Button disabled={processing}>
                                        {processing && <Spinner />}
                                        Confirmar
                                    </Button>
                                </div>
                                <InputError message={errors.code} />
                            </>
                        )}
                    </Form>
                </div>
            )}

            {enabled && (
                <div className="space-y-4">
                    <p className="flex items-center gap-2 text-sm">
                        <ShieldCheck
                            className="size-4 text-green-600"
                            aria-hidden
                        />
                        <Badge variant="secondary">Ativa</Badge>
                        Ao entrar com senha, pediremos o código do aplicativo.
                    </p>

                    {recoveryCodes ? (
                        <div className="space-y-3 rounded-lg border p-4">
                            <p className="text-sm text-muted-foreground">
                                Guarde estes códigos em um gerenciador de
                                senhas. Cada um entra na sua conta uma única
                                vez, se você perder o celular.
                            </p>
                            <ul className="grid gap-1 font-mono text-sm sm:grid-cols-2">
                                {recoveryCodes.map((code) => (
                                    <li key={code}>{code}</li>
                                ))}
                            </ul>
                        </div>
                    ) : null}

                    <div className="flex flex-wrap gap-2">
                        {recoveryCodes ? (
                            <Button
                                variant="outline"
                                disabled={busy}
                                onClick={() =>
                                    post('regenerate', showRecoveryCodes)
                                }
                            >
                                Gerar novos códigos
                            </Button>
                        ) : (
                            <Button
                                variant="outline"
                                onClick={showRecoveryCodes}
                            >
                                Ver códigos de recuperação
                            </Button>
                        )}
                        <Button
                            variant="ghost"
                            disabled={busy}
                            onClick={() =>
                                post('disable', () => setRecoveryCodes(null))
                            }
                        >
                            Desativar
                        </Button>
                    </div>
                </div>
            )}
        </div>
    );
}
