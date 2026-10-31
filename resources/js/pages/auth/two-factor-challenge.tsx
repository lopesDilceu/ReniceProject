import { Form, Head } from '@inertiajs/react';
import { useState } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { store } from '@/routes/two-factor/login';

/**
 * Second step of the password sign-in for accounts with two-factor on:
 * the 6-digit code from the authenticator app, or a one-time recovery code.
 */
export default function TwoFactorChallenge() {
    const [useRecoveryCode, setUseRecoveryCode] = useState(false);

    return (
        <>
            <Head title="Verificação em duas etapas" />

            <Form
                {...store.form()}
                resetOnError
                className="space-y-6"
                // Switching modes must not send the other field along.
                key={useRecoveryCode ? 'recovery' : 'code'}
            >
                {({ processing, errors }) => (
                    <>
                        {useRecoveryCode ? (
                            <div className="grid gap-2">
                                <Label htmlFor="recovery_code">
                                    Código de recuperação
                                </Label>
                                <Input
                                    id="recovery_code"
                                    name="recovery_code"
                                    autoComplete="off"
                                    autoFocus
                                    required
                                    placeholder="abcdefghij-klmnopqrst"
                                    className="font-mono"
                                />
                                <InputError message={errors.recovery_code} />
                            </div>
                        ) : (
                            <div className="grid gap-2">
                                <Label htmlFor="code">
                                    Código do aplicativo
                                </Label>
                                <Input
                                    id="code"
                                    name="code"
                                    inputMode="numeric"
                                    pattern="[0-9]{6}"
                                    maxLength={6}
                                    autoComplete="one-time-code"
                                    autoFocus
                                    required
                                    placeholder="000000"
                                    className="font-mono tracking-[0.5em]"
                                />
                                <InputError message={errors.code} />
                            </div>
                        )}

                        <Button className="w-full" disabled={processing}>
                            {processing && <Spinner />}
                            Continuar
                        </Button>

                        <button
                            type="button"
                            onClick={() => setUseRecoveryCode(!useRecoveryCode)}
                            className="w-full text-center text-sm text-muted-foreground underline-offset-4 hover:underline"
                        >
                            {useRecoveryCode
                                ? 'Usar o código do aplicativo'
                                : 'Perdeu o celular? Use um código de recuperação'}
                        </button>
                    </>
                )}
            </Form>
        </>
    );
}

TwoFactorChallenge.layout = {
    title: 'Verificação em duas etapas',
    description: 'Digite o código de 6 dígitos do seu aplicativo autenticador.',
};
