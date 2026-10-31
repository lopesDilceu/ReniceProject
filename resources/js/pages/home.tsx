import { Head, Link } from '@inertiajs/react';
import { ArrowRight, CupSoda, Snowflake, Sparkles } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { sobre } from '@/routes';
import { index as products } from '@/routes/products';

const highlights = [
    {
        icon: CupSoda,
        title: 'Prontos para beber',
        description:
            'Nossos copos são comercializados prontos: é só servir a bebida.',
    },
    {
        icon: Snowflake,
        title: 'Gelo no tamanho certo',
        description:
            'Gelo pequeno, médio ou grande, em copos de 300ml e 500ml.',
    },
    {
        icon: Sparkles,
        title: 'Conforto no consumo',
        description:
            'Nossa missão é proporcionar o melhor conforto na hora de consumir sua bebida.',
    },
];

export default function Home() {
    return (
        <>
            <Head title="Copos com gelo" />

            <section className="relative isolate overflow-hidden">
                <img
                    src="/images/hero-ice.webp"
                    alt=""
                    width={1280}
                    height={853}
                    fetchPriority="high"
                    className="absolute inset-0 -z-10 size-full object-cover"
                />
                <div className="absolute inset-0 -z-10 bg-gradient-to-r from-slate-950/80 via-slate-950/50 to-transparent" />

                <div className="mx-auto max-w-6xl px-4 py-24 sm:py-32">
                    <div className="max-w-xl text-white">
                        <p className="text-sm font-medium tracking-widest text-cyan-200 uppercase">
                            Renice
                        </p>
                        <h1 className="mt-3 text-4xl font-semibold tracking-tight sm:text-5xl">
                            Copos com gelo, prontos para a sua bebida.
                        </h1>
                        <p className="mt-6 text-lg text-slate-200">
                            Loja especializada na confecção e venda de copos com
                            gelo.
                        </p>
                        <div className="mt-10 flex flex-wrap gap-3">
                            <Button size="lg" asChild>
                                <Link href={products()}>
                                    Ver produtos{' '}
                                    <ArrowRight className="size-4" />
                                </Link>
                            </Button>
                            <Button size="lg" variant="secondary" asChild>
                                <Link href={sobre.url()}>Sobre o projeto</Link>
                            </Button>
                        </div>
                    </div>
                </div>
            </section>

            <section className="mx-auto max-w-6xl px-4 py-16">
                <div className="grid gap-6 sm:grid-cols-3">
                    {highlights.map(({ icon: Icon, title, description }) => (
                        <Card key={title}>
                            <CardContent className="space-y-3">
                                <Icon
                                    className="size-6 text-primary"
                                    aria-hidden
                                />
                                <h2 className="font-semibold">{title}</h2>
                                <p className="text-sm text-muted-foreground">
                                    {description}
                                </p>
                            </CardContent>
                        </Card>
                    ))}
                </div>
            </section>
        </>
    );
}
