import { Form, Head } from '@inertiajs/react';
import InputError from '@/components/input-error';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { login } from '@/routes';
import { email } from '@/routes/password';

export default function ForgotPassword({ status }: { status?: string }) {
    return (
        <>
            <Head title="Esqueci a senha" />

            {status && (
                <div className="text-center text-sm font-medium text-green-600">
                    {status}
                </div>
            )}

            <div className="space-y-6">
                <Form {...email.form()} className="space-y-6">
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="email">E-mail</Label>
                                <Input
                                    id="email"
                                    type="email"
                                    name="email"
                                    autoComplete="email"
                                    autoFocus
                                    placeholder="voce@exemplo.com"
                                />
                                <InputError message={errors.email} />
                            </div>

                            <Button className="w-full" disabled={processing}>
                                {processing && <Spinner />}
                                Enviar link de redefinição
                            </Button>
                        </>
                    )}
                </Form>

                <p className="text-center text-sm text-muted-foreground">
                    Lembrou?{' '}
                    <TextLink href={login()}>Voltar para o login</TextLink>
                </p>
            </div>
        </>
    );
}

ForgotPassword.layout = {
    title: 'Esqueceu a senha?',
    description: 'Enviaremos um link para você criar uma nova senha',
};
