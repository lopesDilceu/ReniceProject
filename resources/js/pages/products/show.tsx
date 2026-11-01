import { Head, Link } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import AddToCart from '@/components/catalog/add-to-cart';
import ProductImage from '@/components/catalog/product-image';
import RatingStars from '@/components/catalog/rating-stars';
import ReviewSummary from '@/components/catalog/review-summary';
import type { ReviewSummaryData } from '@/components/catalog/review-summary';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import { formatDate, formatMoney } from '@/lib/format';
import { index } from '@/routes/products';
import type { Product, Review } from '@/types';

type Props = {
    product: Product;
    ratingDistribution: { stars: number; count: number }[];
    reviews: Review[];
    reviewSummary: ReviewSummaryData | null;
};

function RatingSummary({
    product,
    distribution,
}: {
    product: Product;
    distribution: Props['ratingDistribution'];
}) {
    if (product.rating === null) {
        return (
            <p className="text-sm text-muted-foreground">
                Este produto ainda não foi avaliado.
            </p>
        );
    }

    return (
        <div className="flex flex-col gap-6 sm:flex-row sm:items-center">
            <div className="text-center">
                <p className="text-4xl font-semibold">
                    {product.rating.toLocaleString('pt-BR')}
                </p>
                <RatingStars rating={product.rating} />
                <p className="text-sm text-muted-foreground">
                    {product.reviews_count}{' '}
                    {product.reviews_count === 1 ? 'avaliação' : 'avaliações'}
                </p>
            </div>
            <ul className="flex-1 space-y-1.5">
                {distribution.map(({ stars, count }) => (
                    <li key={stars} className="flex items-center gap-3 text-sm">
                        <span className="w-16 shrink-0 whitespace-nowrap text-muted-foreground">
                            {stars} {stars === 1 ? 'estrela' : 'estrelas'}
                        </span>
                        <div className="h-2 flex-1 overflow-hidden rounded-full bg-muted">
                            <div
                                className="h-full rounded-full bg-amber-400"
                                style={{
                                    width: `${(count / product.reviews_count) * 100}%`,
                                }}
                            />
                        </div>
                        <span className="w-6 text-right text-muted-foreground">
                            {count}
                        </span>
                    </li>
                ))}
            </ul>
        </div>
    );
}

export default function ProductsShow({
    product,
    ratingDistribution,
    reviews,
    reviewSummary,
}: Props) {
    return (
        <>
            <Head title={product.name} />

            <div className="mx-auto max-w-6xl space-y-12 px-4 py-10">
                <Link
                    href={index()}
                    className="inline-flex items-center gap-2 text-sm text-muted-foreground hover:text-foreground"
                >
                    <ArrowLeft className="size-4" /> Todos os produtos
                </Link>

                <div className="grid gap-10 md:grid-cols-2">
                    <ProductImage
                        src={product.image_url}
                        alt={product.name}
                        className="rounded-xl border"
                    />

                    <div className="space-y-6">
                        <div className="space-y-2">
                            <h1 className="text-3xl font-semibold tracking-tight">
                                {product.name}
                            </h1>
                            {product.rating !== null && (
                                <a
                                    href="#avaliacoes"
                                    className="flex items-center gap-2 text-sm text-muted-foreground hover:text-foreground"
                                >
                                    <RatingStars rating={product.rating} />
                                    {product.reviews_count}{' '}
                                    {product.reviews_count === 1
                                        ? 'avaliação'
                                        : 'avaliações'}
                                </a>
                            )}
                        </div>

                        <p className="text-3xl font-semibold">
                            {formatMoney(product.price_cents)}
                        </p>

                        <div>
                            {!product.available && (
                                <Badge variant="secondary">Esgotado</Badge>
                            )}
                            {product.low_stock && (
                                <Badge>Últimas unidades</Badge>
                            )}
                            {product.available && !product.low_stock && (
                                <Badge variant="outline">Em estoque</Badge>
                            )}
                        </div>

                        <AddToCart product={product} />

                        {product.description && (
                            <>
                                <Separator />
                                <p className="leading-relaxed text-muted-foreground">
                                    {product.description}
                                </p>
                            </>
                        )}
                    </div>
                </div>

                <section id="avaliacoes" className="scroll-mt-24 space-y-8">
                    <h2 className="text-2xl font-semibold">Avaliações</h2>

                    <RatingSummary
                        product={product}
                        distribution={ratingDistribution}
                    />

                    {reviewSummary && <ReviewSummary data={reviewSummary} />}

                    {reviews.length > 0 && (
                        <ul className="divide-y rounded-xl border">
                            {reviews.map((review) => (
                                <li key={review.id} className="space-y-2 p-5">
                                    <div className="flex items-center justify-between gap-4">
                                        <RatingStars rating={review.rating} />
                                        <time
                                            dateTime={review.created_at}
                                            className="text-xs text-muted-foreground"
                                        >
                                            {formatDate(review.created_at)}
                                        </time>
                                    </div>
                                    {review.comment && <p>{review.comment}</p>}
                                    <p className="text-sm text-muted-foreground">
                                        {review.author}
                                    </p>
                                </li>
                            ))}
                        </ul>
                    )}
                </section>
            </div>
        </>
    );
}
