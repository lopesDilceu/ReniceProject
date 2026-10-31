import { Form, Head, router } from '@inertiajs/react';
import { Camera, MapPin, Phone, Trash2 } from 'lucide-react';
import { useState } from 'react';
import AccountNav from '@/components/account/account-nav';
import AddressForm from '@/components/address-form';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { useInitials } from '@/hooks/use-initials';
import { maskPhone } from '@/lib/format';
import { update as updateAccount } from '@/routes/account';
import avatar from '@/routes/account/avatar';
import password from '@/routes/account/password';
import addresses from '@/routes/addresses';
import phones from '@/routes/phones';
import type { Address } from '@/types';

type Props = {
    profile: {
        name: string;
        email: string;
        email_verified: boolean;
        birth_date: string | null;
        cpf: string;
        avatar_url: string | null;
    };
    phones: { id: number; number: string }[];
    addresses: Address[];
};

function Section({
    title,
    description,
    children,
}: {
    title: string;
    description?: string;
    children: React.ReactNode;
}) {
    return (
        <section className="grid gap-6 border-b py-8 last:border-b-0 md:grid-cols-[14rem_1fr]">
            <div className="space-y-1">
                <h2 className="font-semibold">{title}</h2>
                {description && (
                    <p className="text-sm text-muted-foreground">
                        {description}
                    </p>
                )}
            </div>
            <div className="space-y-4">{children}</div>
        </section>
    );
}

export default function AccountEdit({
    profile,
    phones: phoneList,
    addresses: addressList,
}: Props) {
    const getInitials = useInitials();
    const [phone, setPhone] = useState('');
    const [addingAddress, setAddingAddress] = useState(false);

    return (
        <>
            <Head title="Minha conta" />

            <div className="mx-auto max-w-4xl px-4 py-10">
                <h1 className="mb-6 text-3xl font-semibold tracking-tight">
                    Minha conta
                </h1>
                <AccountNav />

                <Section title="Foto" description="JPG, PNG ou WebP até 2 MB.">
                    <div className="flex items-center gap-4">
                        <Avatar className="size-16">
                            <AvatarImage
                                src={profile.avatar_url ?? undefined}
                                alt={profile.name}
                            />
                            <AvatarFallback className="text-lg">
                                {getInitials(profile.name)}
                            </AvatarFallback>
                        </Avatar>
                        <Form
                            {...avatar.store.form()}
                            options={{ preserveScroll: true }}
                            resetOnSuccess
                        >
                            {({ processing, errors }) => (
                                <div className="space-y-2">
                                    <label className="inline-flex cursor-pointer items-center gap-2 rounded-md border px-3 py-2 text-sm hover:bg-accent">
                                        {processing ? (
                                            <Spinner />
                                        ) : (
                                            <Camera className="size-4" />
                                        )}
                                        Escolher foto
                                        <input
                                            type="file"
                                            name="avatar"
                                            accept="image/jpeg,image/png,image/webp"
                                            className="sr-only"
                                            onChange={(event) =>
                                                event.currentTarget.form?.requestSubmit()
                                            }
                                        />
                                    </label>
                                    <InputError message={errors.avatar} />
                                </div>
                            )}
                        </Form>
                        {profile.avatar_url && (
                            <Button
                                variant="ghost"
                                size="sm"
                                onClick={() =>
                                    router.delete(avatar.destroy.url(), {
                                        preserveScroll: true,
                                    })
                                }
                            >
                                Remover
                            </Button>
                        )}
                    </div>
                </Section>

                <Section
                    title="Dados pessoais"
                    description="O CPF não pode ser alterado."
                >
                    <Form
                        {...updateAccount.form()}
                        options={{ preserveScroll: true }}
                        className="grid gap-4 sm:grid-cols-2"
                    >
                        {({ processing, errors }) => (
                            <>
                                <div className="grid gap-2 sm:col-span-2">
                                    <Label htmlFor="name">Nome completo</Label>
                                    <Input
                                        id="name"
                                        name="name"
                                        defaultValue={profile.name}
                                        required
                                        autoComplete="name"
                                    />
                                    <InputError message={errors.name} />
                                </div>
                                <div className="grid gap-2 sm:col-span-2">
                                    <Label
                                        htmlFor="email"
                                        className="flex items-center gap-2"
                                    >
                                        E-mail
                                        {!profile.email_verified && (
                                            <Badge variant="secondary">
                                                Não confirmado
                                            </Badge>
                                        )}
                                    </Label>
                                    <Input
                                        id="email"
                                        type="email"
                                        name="email"
                                        defaultValue={profile.email}
                                        required
                                        autoComplete="email"
                                    />
                                    <InputError message={errors.email} />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="birth_date">
                                        Nascimento
                                    </Label>
                                    <Input
                                        id="birth_date"
                                        type="date"
                                        name="birth_date"
                                        defaultValue={profile.birth_date ?? ''}
                                        required
                                    />
                                    <InputError message={errors.birth_date} />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="cpf">CPF</Label>
                                    <Input
                                        id="cpf"
                                        value={profile.cpf}
                                        disabled
                                        readOnly
                                    />
                                </div>
                                <div className="sm:col-span-2">
                                    <Button disabled={processing}>
                                        {processing && <Spinner />}
                                        Salvar dados
                                    </Button>
                                </div>
                            </>
                        )}
                    </Form>
                </Section>

                <Section title="Telefones">
                    {phoneList.length > 0 && (
                        <ul className="divide-y rounded-lg border">
                            {phoneList.map((item) => (
                                <li
                                    key={item.id}
                                    className="flex items-center justify-between px-4 py-2 text-sm"
                                >
                                    <span className="flex items-center gap-2">
                                        <Phone className="size-4 text-muted-foreground" />{' '}
                                        {item.number}
                                    </span>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        aria-label={`Remover ${item.number}`}
                                        onClick={() =>
                                            router.delete(
                                                phones.destroy.url(item.id),
                                                { preserveScroll: true },
                                            )
                                        }
                                    >
                                        <Trash2 className="size-4" />
                                    </Button>
                                </li>
                            ))}
                        </ul>
                    )}
                    <Form
                        {...phones.store.form()}
                        options={{ preserveScroll: true }}
                        onSuccess={() => setPhone('')}
                        className="flex items-start gap-2"
                    >
                        {({ processing, errors }) => (
                            <>
                                <div className="grid flex-1 gap-1">
                                    <Input
                                        name="number"
                                        inputMode="tel"
                                        placeholder="(38) 99999-0000"
                                        aria-label="Novo telefone"
                                        value={phone}
                                        onChange={(event) =>
                                            setPhone(
                                                maskPhone(event.target.value),
                                            )
                                        }
                                    />
                                    <InputError message={errors.number} />
                                </div>
                                <Button
                                    variant="secondary"
                                    disabled={processing}
                                >
                                    Adicionar
                                </Button>
                            </>
                        )}
                    </Form>
                </Section>

                <Section
                    title="Endereços"
                    description="Usados na hora de finalizar a compra."
                >
                    {addressList.length > 0 && (
                        <ul className="divide-y rounded-lg border">
                            {addressList.map((address) => (
                                <li
                                    key={address.id}
                                    className="flex items-start justify-between gap-4 px-4 py-3 text-sm"
                                >
                                    <span className="flex gap-2">
                                        <MapPin className="mt-0.5 size-4 shrink-0 text-muted-foreground" />
                                        <span>
                                            {address.street}, {address.number}
                                            {address.complement
                                                ? ` — ${address.complement}`
                                                : ''}
                                            <br />
                                            <span className="text-muted-foreground">
                                                {address.neighborhood},{' '}
                                                {address.city}/{address.state} ·
                                                CEP {address.cep}
                                            </span>
                                        </span>
                                    </span>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        aria-label="Remover endereço"
                                        onClick={() =>
                                            address.id &&
                                            router.delete(
                                                addresses.destroy.url(
                                                    address.id,
                                                ),
                                                { preserveScroll: true },
                                            )
                                        }
                                    >
                                        <Trash2 className="size-4" />
                                    </Button>
                                </li>
                            ))}
                        </ul>
                    )}
                    {addingAddress ? (
                        <div className="rounded-lg border p-4">
                            <AddressForm
                                onSaved={() => setAddingAddress(false)}
                            />
                        </div>
                    ) : (
                        <Button
                            variant="outline"
                            onClick={() => setAddingAddress(true)}
                        >
                            Adicionar endereço
                        </Button>
                    )}
                </Section>

                <Section title="Senha">
                    <Form
                        {...password.update.form()}
                        options={{ preserveScroll: true }}
                        resetOnSuccess
                        resetOnError
                        className="grid max-w-sm gap-4"
                    >
                        {({ processing, errors }) => (
                            <>
                                <div className="grid gap-2">
                                    <Label htmlFor="current_password">
                                        Senha atual
                                    </Label>
                                    <PasswordInput
                                        id="current_password"
                                        name="current_password"
                                        autoComplete="current-password"
                                    />
                                    <InputError
                                        message={errors.current_password}
                                    />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="password">Nova senha</Label>
                                    <PasswordInput
                                        id="password"
                                        name="password"
                                        autoComplete="new-password"
                                    />
                                    <InputError message={errors.password} />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="password_confirmation">
                                        Confirme a nova senha
                                    </Label>
                                    <PasswordInput
                                        id="password_confirmation"
                                        name="password_confirmation"
                                        autoComplete="new-password"
                                    />
                                </div>
                                <div>
                                    <Button disabled={processing}>
                                        {processing && <Spinner />}
                                        Alterar senha
                                    </Button>
                                </div>
                            </>
                        )}
                    </Form>
                </Section>
            </div>
        </>
    );
}
