/**
 * Masks a CPF as the user types: 12345678909 -> 123.456.789-09.
 * The server strips the mask before validating and storing.
 */
export function maskCpf(value: string): string {
    const digits = value.replace(/\D/g, '').slice(0, 11);

    return digits
        .replace(/^(\d{3})(\d)/, '$1.$2')
        .replace(/^(\d{3})\.(\d{3})(\d)/, '$1.$2.$3')
        .replace(/\.(\d{3})(\d{1,2})$/, '.$1-$2');
}

const brl = new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL',
});

/** Prices are stored in cents: 1125 -> "R$ 11,25". */
export function formatMoney(cents: number): string {
    return brl.format(cents / 100);
}

export function formatDate(iso: string): string {
    return new Intl.DateTimeFormat('pt-BR', { dateStyle: 'medium' }).format(
        new Date(iso),
    );
}

/** (38) 99999-0000 as the user types; the server keeps digits only. */
export function maskPhone(value: string): string {
    const digits = value.replace(/\D/g, '').slice(0, 11);

    if (digits.length <= 2) {
        return digits.replace(/^(\d{1,2})/, '($1');
    }

    const rest = digits.slice(2);
    const split = digits.length === 11 ? 5 : 4;

    return `(${digits.slice(0, 2)}) ${rest.slice(0, split)}${rest.length > split ? `-${rest.slice(split)}` : ''}`;
}
