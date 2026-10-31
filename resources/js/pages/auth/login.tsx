import { Form, Head } from '@inertiajs/react';
import PasskeyButton from '@/components/auth/passkey-button';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';

type Props = {
    status?: string;
    canResetPassword: boolean;
};

export default function Login({ status, canResetPassword }: Props) {
    return (
        <>
            <Head title="Entrar" />

            {status && (
                <div className="text-center text-sm font-medium text-green-600">
                    {status}
                </div>
            )}

            <PasskeyButton
                label="Entrar com passkey"
                separator="ou com e-mail e senha"
            />

            <Form
                {...store.form()}
                resetOnSuccess={['password']}
                className="flex flex-col gap-6"
            >
                {({ processing, errors }) => (
                    <>
                        <div className="grid gap-6">
                            <div className="grid gap-2">
                                <Label htmlFor="email">E-mail</Label>
                                <Input
                                    id="email"
                                    type="email"
                                    name="email"
                                    required
                                    autoFocus
                                    tabIndex={1}
                                    autoComplete="email"
                                    placeholder="voce@exemplo.com"
                                />
                                <InputError message={errors.email} />
                            </div>

                            <div className="grid gap-2">
                                <div className="flex items-center">
                                    <Label htmlFor="password">Senha</Label>
                                    {canResetPassword && (
                                        <TextLink
                                            href={request()}
                                            className="ml-auto text-sm"
                                            tabIndex={5}
                                        >
                                            Esqueceu a senha?
                                        </TextLink>
                                    )}
                                </div>
                                <PasswordInput
                                    id="password"
                                    name="password"
                                    required
                                    tabIndex={2}
                                    autoComplete="current-password"
                                />
                                <InputError message={errors.password} />
                            </div>

                            <div className="flex items-center gap-3">
                                <Checkbox
                                    id="remember"
                                    name="remember"
                                    tabIndex={3}
                                />
                                <Label htmlFor="remember">
                                    Manter conectado
                                </Label>
                            </div>

                            <Button
                                type="submit"
                                className="w-full"
                                tabIndex={4}
                                disabled={processing}
                            >
                                {processing && <Spinner />}
                                Entrar
                            </Button>
                        </div>

                        <p className="text-center text-sm text-muted-foreground">
                            Ainda não tem conta?{' '}
                            <TextLink href={register()} tabIndex={6}>
                                Criar conta
                            </TextLink>
                        </p>
                    </>
                )}
            </Form>
        </>
    );
}

Login.layout = {
    title: 'Entre na sua conta',
    description: 'Use o e-mail e a senha cadastrados',
};
