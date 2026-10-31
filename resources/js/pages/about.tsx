import { Head } from '@inertiajs/react';
import { ExternalLink } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

const repository = 'https://github.com/lopesDilceu/ReniceProject';

const timeline = [
    {
        period: '2024',
        title: 'Projeto acadêmico',
        description:
            'Criado para as disciplinas de Programação II e Banco de Dados II do curso de Sistemas de Informação da Unimontes, a partir de uma empresa fictícia de um trabalho de contabilidade.',
        stack: ['Laravel 11', 'Blade', 'Bootstrap', 'jQuery', 'MySQL'],
    },
    {
        period: '2026',
        title: 'Modernização',
        description:
            'Auditoria do código original, upgrade do framework, banco versionado com regras de negócio testadas e reescrita do frontend, documentados passo a passo em issues, pull requests e registros de decisão.',
        stack: [
            'Laravel 13',
            'PHP 8.5',
            'Inertia',
            'React',
            'TypeScript',
            'PostgreSQL',
        ],
    },
];

const documents = [
    {
        title: 'Auditoria inicial',
        href: `${repository}/blob/main/docs/audit/initial-audit.md`,
    },
    { title: 'Roadmap', href: `${repository}/blob/main/docs/roadmap.md` },
    {
        title: 'Decisões de arquitetura',
        href: `${repository}/blob/main/docs/adr/README.md`,
    },
    {
        title: 'Métricas antes e depois',
        href: `${repository}/blob/main/docs/metrics.md`,
    },
];

export default function About() {
    return (
        <>
            <Head title="Sobre o projeto" />

            <div className="mx-auto max-w-3xl space-y-12 px-4 py-16">
                <header className="space-y-4">
                    <h1 className="text-3xl font-semibold tracking-tight">
                        Sobre o projeto
                    </h1>
                    <p className="text-lg text-muted-foreground">
                        A Renice começou como trabalho de faculdade e está sendo
                        reconstruída, em público, com o padrão de um sistema que
                        iria para produção.
                    </p>
                </header>

                <ol className="space-y-6">
                    {timeline.map((step) => (
                        <li key={step.period}>
                            <Card>
                                <CardHeader>
                                    <p className="text-sm font-medium text-primary">
                                        {step.period}
                                    </p>
                                    <CardTitle>{step.title}</CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <p className="text-muted-foreground">
                                        {step.description}
                                    </p>
                                    <div className="flex flex-wrap gap-2">
                                        {step.stack.map((item) => (
                                            <Badge
                                                key={item}
                                                variant="secondary"
                                            >
                                                {item}
                                            </Badge>
                                        ))}
                                    </div>
                                </CardContent>
                            </Card>
                        </li>
                    ))}
                </ol>

                <section className="space-y-4">
                    <h2 className="text-xl font-semibold">Documentação</h2>
                    <ul className="grid gap-3 sm:grid-cols-2">
                        {documents.map((document) => (
                            <li key={document.href}>
                                <a
                                    href={document.href}
                                    target="_blank"
                                    rel="noreferrer"
                                    className="flex items-center justify-between rounded-lg border p-4 text-sm font-medium transition-colors hover:bg-accent"
                                >
                                    {document.title}
                                    <ExternalLink
                                        className="size-4 text-muted-foreground"
                                        aria-hidden
                                    />
                                </a>
                            </li>
                        ))}
                    </ul>
                </section>

                <p className="text-sm text-muted-foreground">
                    Autores da versão original: Dilceu Lopes e Renan Iago
                    Figueiredo Silva Ciríaco.
                </p>
            </div>
        </>
    );
}
