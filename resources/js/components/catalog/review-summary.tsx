import { Minus, Plus, Sparkles } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { formatDate } from '@/lib/format';

export type ReviewSummaryData = {
    summary: string;
    pros: string[];
    cons: string[];
    reviews_count: number;
    updated_at: string;
};

/**
 * The AI summary of a product's reviews, labeled as such. Shown only when
 * one exists; the reviews themselves stay right below.
 */
export default function ReviewSummary({ data }: { data: ReviewSummaryData }) {
    return (
        <section
            aria-labelledby="review-summary-title"
            className="space-y-4 rounded-xl border bg-muted/30 p-5"
        >
            <div className="flex flex-wrap items-center justify-between gap-2">
                <h3
                    id="review-summary-title"
                    className="flex items-center gap-2 font-semibold"
                >
                    <Sparkles className="size-4 text-primary" aria-hidden />O
                    que os clientes dizem
                </h3>
                <Badge variant="outline">Resumo gerado por IA</Badge>
            </div>

            <p className="text-sm">{data.summary}</p>

            {(data.pros.length > 0 || data.cons.length > 0) && (
                <div className="grid gap-4 text-sm sm:grid-cols-2">
                    {data.pros.length > 0 && (
                        <ul className="space-y-1" aria-label="Pontos positivos">
                            {data.pros.map((pro) => (
                                <li key={pro} className="flex gap-2">
                                    <Plus
                                        className="mt-0.5 size-4 shrink-0 text-green-600"
                                        aria-hidden
                                    />
                                    {pro}
                                </li>
                            ))}
                        </ul>
                    )}
                    {data.cons.length > 0 && (
                        <ul className="space-y-1" aria-label="Pontos negativos">
                            {data.cons.map((con) => (
                                <li key={con} className="flex gap-2">
                                    <Minus
                                        className="mt-0.5 size-4 shrink-0 text-red-600"
                                        aria-hidden
                                    />
                                    {con}
                                </li>
                            ))}
                        </ul>
                    )}
                </div>
            )}

            <p className="text-xs text-muted-foreground">
                Com base em {data.reviews_count} avaliações · atualizado em{' '}
                {formatDate(data.updated_at)}. Pode conter imprecisões: leia as
                avaliações abaixo.
            </p>
        </section>
    );
}
