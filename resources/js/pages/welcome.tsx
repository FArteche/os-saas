import { Head, Link, usePage } from '@inertiajs/react';
import { ClipboardCheck, Link2, Smartphone } from 'lucide-react';
import AppLogoIcon from '@/components/app-logo-icon';
import { Button } from '@/components/ui/button';
import { dashboard, login, register } from '@/routes';

const features = [
    {
        icon: ClipboardCheck,
        title: 'Ordens de serviço organizadas',
        description:
            'Entrada com fotos e checklist, orçamento e histórico de cada aparelho.',
    },
    {
        icon: Link2,
        title: 'Aprovação por link',
        description:
            'O cliente recebe o orçamento no WhatsApp e aprova pelo celular.',
    },
    {
        icon: Smartphone,
        title: 'Status sem ligação',
        description:
            'O cliente acompanha o andamento sozinho, sem precisar perguntar.',
    },
];

export default function Welcome() {
    const { auth, name } = usePage().props;

    return (
        <>
            <Head title="Bem-vindo" />

            <div className="flex min-h-svh flex-col bg-background text-foreground">
                <header className="mx-auto flex w-full max-w-5xl items-center justify-between p-6">
                    <div className="flex items-center gap-2 font-semibold">
                        <AppLogoIcon className="size-7 fill-current" />
                        {name}
                    </div>

                    <nav className="flex items-center gap-2">
                        {auth.user ? (
                            <Button asChild>
                                <Link href={dashboard()}>Ir para o painel</Link>
                            </Button>
                        ) : (
                            <>
                                <Button variant="ghost" asChild>
                                    <Link href={login()}>Entrar</Link>
                                </Button>
                                <Button asChild>
                                    <Link href={register()}>Criar conta</Link>
                                </Button>
                            </>
                        )}
                    </nav>
                </header>

                <main className="mx-auto flex w-full max-w-5xl flex-1 flex-col justify-center gap-12 px-6 py-12">
                    <section className="max-w-2xl space-y-4">
                        <h1 className="text-4xl font-semibold tracking-tight md:text-5xl">
                            Chega de controlar a assistência no caderno.
                        </h1>
                        <p className="text-lg text-muted-foreground">
                            Ordens de serviço, orçamentos e o status de cada
                            aparelho em um só lugar, com aprovação do cliente
                            pelo celular.
                        </p>
                        {!auth.user && (
                            <Button size="lg" asChild>
                                <Link href={register()}>Começar agora</Link>
                            </Button>
                        )}
                    </section>

                    <section className="grid gap-4 md:grid-cols-3">
                        {features.map((feature) => (
                            <div
                                key={feature.title}
                                className="space-y-2 rounded-xl border p-5"
                            >
                                <feature.icon className="size-6 text-muted-foreground" />
                                <h2 className="font-medium">{feature.title}</h2>
                                <p className="text-sm text-muted-foreground">
                                    {feature.description}
                                </p>
                            </div>
                        ))}
                    </section>
                </main>
            </div>
        </>
    );
}
