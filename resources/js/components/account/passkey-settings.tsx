import { router } from '@inertiajs/react';
import { usePasskeyRegister } from '@laravel/passkeys/react';
import { KeyRound, Trash2 } from 'lucide-react';
import { useState } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { formatDateTime } from '@/lib/format';
import passkeyRoutes from '@/routes/passkey';

export type Passkey = {
    id: number;
    name: string;
    authenticator: string | null;
    created_at: string;
    last_used_at: string | null;
};

/** "Chrome no Windows", a default name the user can change. */
function deviceName(): string {
    const ua = navigator.userAgent;
    const browser = [
        [/Edg/, 'Edge'],
        [/OPR|Opera/, 'Opera'],
        [/Firefox|FxiOS/, 'Firefox'],
        [/Chrome|CriOS/, 'Chrome'],
        [/Safari/, 'Safari'],
    ].find(([pattern]) => (pattern as RegExp).test(ua))?.[1];
    const system = [
        [/iPhone/, 'iPhone'],
        [/iPad/, 'iPad'],
        [/Android/, 'Android'],
        [/Mac/, 'Mac'],
        [/Windows/, 'Windows'],
        [/Linux/, 'Linux'],
    ].find(([pattern]) => (pattern as RegExp).test(ua))?.[1];

    return [browser, system].filter(Boolean).join(' no ');
}

export default function PasskeySettings({ passkeys }: { passkeys: Passkey[] }) {
    const [name, setName] = useState(deviceName);
    const [confirmingDelete, setConfirmingDelete] = useState<number | null>(
        null,
    );

    const { register, isLoading, error, isSupported } = usePasskeyRegister({
        onSuccess: () => router.reload({ only: ['passkeys'] }),
    });

    return (
        <div className="space-y-4">
            {passkeys.length > 0 ? (
                <ul className="divide-y rounded-lg border">
                    {passkeys.map((passkey) => (
                        <li
                            key={passkey.id}
                            className="flex items-center justify-between gap-4 p-3 text-sm"
                        >
                            <div className="flex items-center gap-3">
                                <KeyRound
                                    className="size-4 text-muted-foreground"
                                    aria-hidden
                                />
                                <div>
                                    <p className="font-medium">
                                        {passkey.name}
                                    </p>
                                    <p className="text-muted-foreground">
                                        {passkey.authenticator &&
                                            `${passkey.authenticator} · `}
                                        {passkey.last_used_at
                                            ? `usada em ${formatDateTime(passkey.last_used_at)}`
                                            : `criada em ${formatDateTime(passkey.created_at)}`}
                                    </p>
                                </div>
                            </div>
                            {confirmingDelete === passkey.id ? (
                                <div className="flex gap-1">
                                    <Button
                                        size="sm"
                                        variant="destructive"
                                        onClick={() =>
                                            router.delete(
                                                passkeyRoutes.destroy.url(
                                                    passkey.id,
                                                ),
                                                {
                                                    preserveScroll: true,
                                                    onFinish: () =>
                                                        setConfirmingDelete(
                                                            null,
                                                        ),
                                                },
                                            )
                                        }
                                    >
                                        Remover
                                    </Button>
                                    <Button
                                        size="sm"
                                        variant="ghost"
                                        onClick={() =>
                                            setConfirmingDelete(null)
                                        }
                                    >
                                        Cancelar
                                    </Button>
                                </div>
                            ) : (
                                <Button
                                    size="icon"
                                    variant="ghost"
                                    aria-label={`Remover a passkey ${passkey.name}`}
                                    onClick={() =>
                                        setConfirmingDelete(passkey.id)
                                    }
                                >
                                    <Trash2 className="size-4" />
                                </Button>
                            )}
                        </li>
                    ))}
                </ul>
            ) : (
                <p className="text-sm text-muted-foreground">
                    Nenhuma passkey cadastrada.
                </p>
            )}

            {isSupported ? (
                <form
                    className="grid max-w-sm gap-2"
                    onSubmit={(event) => {
                        event.preventDefault();

                        if (name.trim()) {
                            void register(name.trim());
                        }
                    }}
                >
                    <Label htmlFor="passkey-name">Nome deste dispositivo</Label>
                    <div className="flex gap-2">
                        <Input
                            id="passkey-name"
                            value={name}
                            maxLength={255}
                            onChange={(event) => setName(event.target.value)}
                            required
                        />
                        <Button disabled={isLoading}>
                            {isLoading && <Spinner />}
                            Adicionar passkey
                        </Button>
                    </div>
                    <InputError message={error ?? undefined} />
                </form>
            ) : (
                <p className="text-sm text-muted-foreground">
                    Este navegador não suporta passkeys.
                </p>
            )}
        </div>
    );
}
