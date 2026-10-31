import { Head } from '@inertiajs/react';
import AccountNav from '@/components/account/account-nav';
import PasskeySettings from '@/components/account/passkey-settings';
import type { Passkey } from '@/components/account/passkey-settings';
import TwoFactorSettings from '@/components/account/two-factor-settings';

type Props = {
    twoFactorEnabled: boolean;
    twoFactorPending: boolean;
    passkeys: Passkey[];
};

function Section({
    title,
    description,
    children,
}: {
    title: string;
    description: string;
    children: React.ReactNode;
}) {
    return (
        <section className="grid gap-6 border-b py-8 last:border-b-0 md:grid-cols-[14rem_1fr]">
            <div className="space-y-1">
                <h2 className="font-semibold">{title}</h2>
                <p className="text-sm text-muted-foreground">{description}</p>
            </div>
            <div>{children}</div>
        </section>
    );
}

export default function AccountSecurity({
    twoFactorEnabled,
    twoFactorPending,
    passkeys,
}: Props) {
    return (
        <>
            <Head title="Segurança" />

            <div className="mx-auto max-w-4xl px-4 py-10">
                <h1 className="mb-6 text-3xl font-semibold tracking-tight">
                    Minha conta
                </h1>
                <AccountNav />

                <Section
                    title="Passkeys"
                    description="Entre com a digital, o rosto ou o PIN do dispositivo, sem senha. A chave privada nunca sai do seu aparelho."
                >
                    <PasskeySettings passkeys={passkeys} />
                </Section>

                <Section
                    title="Verificação em duas etapas"
                    description="Ao entrar com senha, peça também um código do aplicativo autenticador."
                >
                    <TwoFactorSettings
                        enabled={twoFactorEnabled}
                        pending={twoFactorPending}
                    />
                </Section>
            </div>
        </>
    );
}
