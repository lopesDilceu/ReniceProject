import { Form } from '@inertiajs/react';
import { useState } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { store } from '@/routes/addresses';

type ViaCepResponse = {
    erro?: boolean;
    logradouro: string;
    bairro: string;
    localidade: string;
    uf: string;
};

function maskCep(value: string): string {
    return value
        .replace(/\D/g, '')
        .slice(0, 8)
        .replace(/^(\d{5})(\d)/, '$1-$2');
}

/**
 * New delivery address. Typing a complete CEP fills street, district, city
 * and state from ViaCEP (a public Brazilian postal code API), called from
 * the browser; the server validates everything again.
 */
export default function AddressForm({ onSaved }: { onSaved?: () => void }) {
    const [cep, setCep] = useState('');
    const [lookup, setLookup] = useState<'idle' | 'loading' | 'not-found'>(
        'idle',
    );

    async function fillFromCep(value: string, form: HTMLFormElement | null) {
        const digits = value.replace(/\D/g, '');

        if (digits.length !== 8 || !form) {
            return;
        }

        setLookup('loading');

        try {
            const response = await fetch(
                `https://viacep.com.br/ws/${digits}/json/`,
            );
            const data = (await response.json()) as ViaCepResponse;

            if (data.erro) {
                setLookup('not-found');

                return;
            }

            const fields = form.elements;
            const set = (name: string, value: string) => {
                const input = fields.namedItem(name);

                if (input instanceof HTMLInputElement) {
                    input.value = value;
                }
            };

            set('street', data.logradouro);
            set('neighborhood', data.bairro);
            set('city', data.localidade);
            set('state', data.uf);
            setLookup('idle');

            const number = fields.namedItem('number');

            if (number instanceof HTMLInputElement) {
                number.focus();
            }
        } catch {
            // Offline or ViaCEP unavailable: the customer fills it in manually.
            setLookup('idle');
        }
    }

    return (
        <Form
            {...store.form()}
            options={{ preserveScroll: true }}
            onSuccess={onSaved}
            resetOnSuccess
            className="grid gap-4 sm:grid-cols-6"
        >
            {({ processing, errors }) => (
                <>
                    <div className="grid gap-2 sm:col-span-2">
                        <Label htmlFor="cep">CEP</Label>
                        <Input
                            id="cep"
                            name="cep"
                            inputMode="numeric"
                            autoComplete="postal-code"
                            placeholder="00000-000"
                            value={cep}
                            onChange={(event) => {
                                const masked = maskCep(event.target.value);
                                setCep(masked);
                                void fillFromCep(
                                    masked,
                                    event.currentTarget.form,
                                );
                            }}
                            required
                        />
                        {lookup === 'loading' && (
                            <p className="text-xs text-muted-foreground">
                                Buscando endereço...
                            </p>
                        )}
                        {lookup === 'not-found' && (
                            <p className="text-xs text-muted-foreground">
                                CEP não encontrado. Preencha manualmente.
                            </p>
                        )}
                        <InputError message={errors.cep} />
                    </div>

                    <div className="grid gap-2 sm:col-span-4">
                        <Label htmlFor="street">Rua</Label>
                        <Input
                            id="street"
                            name="street"
                            autoComplete="address-line1"
                            required
                        />
                        <InputError message={errors.street} />
                    </div>

                    <div className="grid gap-2 sm:col-span-2">
                        <Label htmlFor="number">Número</Label>
                        <Input
                            id="number"
                            name="number"
                            placeholder="123 ou S/N"
                            required
                        />
                        <InputError message={errors.number} />
                    </div>

                    <div className="grid gap-2 sm:col-span-4">
                        <Label htmlFor="complement">
                            Complemento (opcional)
                        </Label>
                        <Input
                            id="complement"
                            name="complement"
                            autoComplete="address-line2"
                        />
                        <InputError message={errors.complement} />
                    </div>

                    <div className="grid gap-2 sm:col-span-2">
                        <Label htmlFor="neighborhood">Bairro</Label>
                        <Input id="neighborhood" name="neighborhood" required />
                        <InputError message={errors.neighborhood} />
                    </div>

                    <div className="grid gap-2 sm:col-span-3">
                        <Label htmlFor="city">Cidade</Label>
                        <Input
                            id="city"
                            name="city"
                            autoComplete="address-level2"
                            required
                        />
                        <InputError message={errors.city} />
                    </div>

                    <div className="grid gap-2 sm:col-span-1">
                        <Label htmlFor="state">UF</Label>
                        <Input
                            id="state"
                            name="state"
                            maxLength={2}
                            autoComplete="address-level1"
                            className="uppercase"
                            required
                        />
                        <InputError message={errors.state} />
                    </div>

                    <div className="sm:col-span-6">
                        <Button
                            type="submit"
                            variant="secondary"
                            disabled={processing}
                        >
                            {processing && <Spinner />}
                            Salvar endereço
                        </Button>
                    </div>
                </>
            )}
        </Form>
    );
}
