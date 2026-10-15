import { Form, Head, Link } from '@inertiajs/react';
import { useState } from 'react';
import OnboardingController from '@/actions/App/Http/Controllers/OnboardingController';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { maskDocument, maskPhone } from '@/lib/masks';
import { dashboard, logout } from '@/routes';

type NicheOption = {
    value: string;
    label: string;
    description: string;
};

type Props = {
    niches: NicheOption[];
    isFirstCompany: boolean;
};

export default function Onboarding({ niches, isFirstCompany }: Props) {
    // Controlled inputs: the mask is re-applied on every keystroke.
    const [document, setDocument] = useState('');
    const [phone, setPhone] = useState('');

    return (
        <>
            <Head
                title={isFirstCompany ? 'Crie sua empresa' : 'Nova empresa'}
            />

            <Form
                {...OnboardingController.store.form()}
                disableWhileProcessing
                className="flex flex-col gap-6"
            >
                {({ processing, errors }) => (
                    <>
                        <div className="grid gap-2">
                            <Label htmlFor="name">Nome da empresa</Label>
                            <Input
                                id="name"
                                name="name"
                                required
                                autoFocus
                                autoComplete="organization"
                                placeholder="Ex.: Conserta Fácil Celulares"
                            />
                            <InputError message={errors.name} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="document">
                                CPF ou CNPJ{' '}
                                <span className="text-muted-foreground">
                                    (opcional)
                                </span>
                            </Label>
                            <Input
                                id="document"
                                name="document"
                                value={document}
                                onChange={(e) =>
                                    setDocument(maskDocument(e.target.value))
                                }
                                placeholder="00.000.000/0000-00"
                            />
                            <InputError message={errors.document} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="phone">
                                WhatsApp da loja{' '}
                                <span className="text-muted-foreground">
                                    (opcional)
                                </span>
                            </Label>
                            <Input
                                id="phone"
                                name="phone"
                                type="tel"
                                inputMode="tel"
                                autoComplete="tel"
                                value={phone}
                                onChange={(e) =>
                                    setPhone(maskPhone(e.target.value))
                                }
                                placeholder="(11) 98765-4321"
                            />
                            <InputError message={errors.phone} />
                        </div>

                        <fieldset className="grid gap-2">
                            <legend className="mb-2 text-sm font-medium">
                                Ramo de atuação
                            </legend>

                            {niches.map((niche, index) => (
                                <label
                                    key={niche.value}
                                    className="flex cursor-pointer gap-3 rounded-lg border p-3 transition-colors hover:bg-accent/50 has-checked:border-primary has-checked:bg-accent"
                                >
                                    <input
                                        type="radio"
                                        name="niche"
                                        value={niche.value}
                                        defaultChecked={index === 0}
                                        className="mt-1 accent-primary"
                                    />
                                    <span className="grid gap-0.5">
                                        <span className="text-sm font-medium">
                                            {niche.label}
                                        </span>
                                        <span className="text-xs text-muted-foreground">
                                            {niche.description}
                                        </span>
                                    </span>
                                </label>
                            ))}

                            <InputError message={errors.niche} />
                        </fieldset>

                        <Button
                            type="submit"
                            className="w-full"
                            data-test="create-company-button"
                        >
                            {processing && <Spinner />}
                            Criar empresa
                        </Button>

                        <div className="text-center text-sm text-muted-foreground">
                            {isFirstCompany ? (
                                <Link
                                    href={logout()}
                                    as="button"
                                    className="underline underline-offset-4"
                                >
                                    Sair
                                </Link>
                            ) : (
                                <Link
                                    href={dashboard()}
                                    className="underline underline-offset-4"
                                >
                                    Voltar ao painel
                                </Link>
                            )}
                        </div>
                    </>
                )}
            </Form>
        </>
    );
}

Onboarding.layout = (props: Props) => ({
    title: props.isFirstCompany ? 'Crie sua empresa' : 'Nova empresa',
    description: props.isFirstCompany
        ? 'Falta pouco! Conte um pouco sobre o seu negócio para começar.'
        : 'Cadastre outra empresa para gerenciar no mesmo login.',
});
