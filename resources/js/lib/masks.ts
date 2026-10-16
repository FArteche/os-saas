/**
 * Input masks for Brazilian documents. The backend strips them again,
 * so these only affect what the user sees while typing.
 */

/** CPF (000.000.000-00) or CNPJ (00.000.000/0000-00), including the alphanumeric CNPJ. */
export function maskDocument(value: string): string {
    const clean = value
        .toUpperCase()
        .replace(/[^0-9A-Z]/g, '')
        .slice(0, 14);

    // Letters only exist in CNPJs, and CPFs have at most 11 digits.
    if (clean.length <= 11 && /^\d*$/.test(clean)) {
        return clean
            .replace(/^(\d{3})(\d)/, '$1.$2')
            .replace(/^(\d{3})\.(\d{3})(\d)/, '$1.$2.$3')
            .replace(/\.(\d{3})(\d)/, '.$1-$2');
    }

    return clean
        .replace(/^(\w{2})(\w)/, '$1.$2')
        .replace(/^(\w{2})\.(\w{3})(\w)/, '$1.$2.$3')
        .replace(/\.(\w{3})(\w)/, '.$1/$2')
        .replace(/(\w{4})(\w)/, '$1-$2');
}

/**
 * (00) 0000-0000 or (00) 00000-0000.
 * Drops the +55 country code of numbers pasted from WhatsApp ("+55 11 98765-4321").
 * "55" is also a valid DDD, so it is only dropped when there are more than 11 digits.
 */
export function maskPhone(value: string): string {
    let digits = value.replace(/\D/g, '');

    if (digits.length > 11 && digits.startsWith('55')) {
        digits = digits.slice(2);
    }

    digits = digits.slice(0, 11);

    if (digits.length <= 2) {
        return digits.replace(/^(\d+)/, '($1');
    }

    const middle = digits.length === 11 ? 5 : 4;

    return digits
        .replace(/^(\d{2})(\d)/, '($1) $2')
        .replace(new RegExp(`(\\d{${middle}})(\\d{1,4})$`), '$1-$2');
}
