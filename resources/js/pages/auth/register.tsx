import { Form, Head } from '@inertiajs/react';
import { useState } from 'react';
import InputError from '@/components/input-error';
import PasswordInput from '@/components/password-input';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { maskCpf } from '@/lib/format';
import { login } from '@/routes';
import { store } from '@/routes/register';

type Props = {
    passwordRules: string;
};

export default function Register({ passwordRules }: Props) {
    const [cpf, setCpf] = useState('');

    return (
        <>
            <Head title="Criar conta" />

            <Form
                {...store.form()}
                resetOnSuccess={['password', 'password_confirmation']}
                disableWhileProcessing
                className="flex flex-col gap-6"
            >
                {({ processing, errors }) => (
                    <>
                        <div className="grid gap-5">
                            <div className="grid gap-2">
                                <Label htmlFor="name">Nome completo</Label>
                                <Input
                                    id="name"
                                    name="name"
                                    required
                                    autoFocus
                                    autoComplete="name"
                                />
                                <InputError message={errors.name} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="email">E-mail</Label>
                                <Input
                                    id="email"
                                    type="email"
                                    name="email"
                                    required
                                    autoComplete="email"
                                    placeholder="voce@exemplo.com"
                                />
                                <InputError message={errors.email} />
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                <div className="grid gap-2">
                                    <Label htmlFor="cpf">CPF</Label>
                                    <Input
                                        id="cpf"
                                        name="cpf"
                                        required
                                        inputMode="numeric"
                                        placeholder="000.000.000-00"
                                        value={cpf}
                                        onChange={(event) =>
                                            setCpf(maskCpf(event.target.value))
                                        }
                                    />
                                    <InputError message={errors.cpf} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="birth_date">
                                        Nascimento
                                    </Label>
                                    <Input
                                        id="birth_date"
                                        type="date"
                                        name="birth_date"
                                        required
                                        autoComplete="bday"
                                    />
                                    <InputError message={errors.birth_date} />
                                </div>
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="password">Senha</Label>
                                <PasswordInput
                                    id="password"
                                    name="password"
                                    required
                                    autoComplete="new-password"
                                    passwordrules={passwordRules}
                                />
                                <InputError message={errors.password} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="password_confirmation">
                                    Confirme a senha
                                </Label>
                                <PasswordInput
                                    id="password_confirmation"
                                    name="password_confirmation"
                                    required
                                    autoComplete="new-password"
                                    passwordrules={passwordRules}
                                />
                                <InputError
                                    message={errors.password_confirmation}
                                />
                            </div>

                            <Button type="submit" className="mt-2 w-full">
                                {processing && <Spinner />}
                                Criar conta
                            </Button>
                        </div>

                        <p className="text-center text-sm text-muted-foreground">
                            Já tem conta?{' '}
                            <TextLink href={login()}>Entrar</TextLink>
                        </p>
                    </>
                )}
            </Form>
        </>
    );
}

Register.layout = {
    title: 'Crie sua conta',
    description: 'Endereço e telefone você informa depois, na hora da compra',
};
