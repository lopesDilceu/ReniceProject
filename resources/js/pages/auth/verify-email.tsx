import { Form, Head, Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import { logout } from '@/routes';
import { send } from '@/routes/verification';

export default function VerifyEmail({ status }: { status?: string }) {
    return (
        <>
            <Head title="Confirme seu e-mail" />

            {status === 'verification-link-sent' && (
                <div className="text-center text-sm font-medium text-green-600">
                    Enviamos um novo link de confirmação para o seu e-mail.
                </div>
            )}

            <Form {...send.form()} className="space-y-4 text-center">
                {({ processing }) => (
                    <>
                        <Button
                            disabled={processing}
                            variant="secondary"
                            className="w-full"
                        >
                            {processing && <Spinner />}
                            Reenviar e-mail de confirmação
                        </Button>

                        <Link
                            href={logout()}
                            as="button"
                            className="text-sm text-muted-foreground underline underline-offset-4 hover:text-foreground"
                        >
                            Sair
                        </Link>
                    </>
                )}
            </Form>
        </>
    );
}

VerifyEmail.layout = {
    title: 'Confirme seu e-mail',
    description:
        'Clique no link que enviamos para o seu e-mail para ativar a conta.',
};
