import { Form, Head } from '@inertiajs/react';
import PasskeyButton from '@/components/auth/passkey-button';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import passkey from '@/routes/passkey';
import { store } from '@/routes/password/confirm';

export default function ConfirmPassword() {
    return (
        <>
            <Head title="Confirme sua senha" />

            <PasskeyButton
                routes={{
                    options: passkey.confirmOptions.url(),
                    submit: passkey.confirm.url(),
                }}
                label="Confirmar com passkey"
                separator="ou com a senha"
            />

            <Form {...store.form()} resetOnSuccess={['password']}>
                {({ processing, errors }) => (
                    <div className="space-y-6">
                        <div className="grid gap-2">
                            <Label htmlFor="password">Senha</Label>
                            <PasswordInput
                                id="password"
                                name="password"
                                autoComplete="current-password"
                                autoFocus
                            />
                            <InputError message={errors.password} />
                        </div>

                        <Button className="w-full" disabled={processing}>
                            {processing && <Spinner />}
                            Confirmar
                        </Button>
                    </div>
                )}
            </Form>
        </>
    );
}

ConfirmPassword.layout = {
    title: 'Confirme sua senha',
    description:
        'Esta é uma área protegida. Confirme sua senha para continuar.',
};
