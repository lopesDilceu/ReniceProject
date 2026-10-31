import { Head, Link, router } from '@inertiajs/react';
import { Trash2 } from 'lucide-react';
import RatingStars from '@/components/catalog/rating-stars';
import Pagination from '@/components/pagination';
import { Button } from '@/components/ui/button';
import { formatDate } from '@/lib/format';
import reviews from '@/routes/admin/reviews';
import { show as product } from '@/routes/products';
import type { SimplePaginated } from '@/types';

type Review = {
    id: number;
    rating: number;
    comment: string | null;
    product: string;
    product_slug: string;
    author: string;
    author_public: string;
    created_at: string;
};

export default function AdminReviewsIndex({
    reviews: list,
}: {
    reviews: SimplePaginated<Review>;
}) {
    return (
        <>
            <Head title="Avaliações · Admin" />

            <div className="space-y-6">
                <div>
                    <h1 className="text-2xl font-semibold">Avaliações</h1>
                    <p className="text-muted-foreground">
                        Remova avaliações ofensivas ou fora do tema. Só quem
                        recebeu o produto consegue avaliar.
                    </p>
                </div>

                <ul className="divide-y rounded-xl border bg-background">
                    {list.data.map((review) => (
                        <li key={review.id} className="flex gap-4 p-4">
                            <div className="flex-1 space-y-1">
                                <div className="flex flex-wrap items-center gap-2 text-sm">
                                    <RatingStars rating={review.rating} />
                                    <Link
                                        href={product(review.product_slug)}
                                        className="font-medium hover:underline"
                                    >
                                        {review.product}
                                    </Link>
                                    <span className="text-muted-foreground">
                                        · {formatDate(review.created_at)}
                                    </span>
                                </div>
                                {review.comment && (
                                    <p className="text-sm">{review.comment}</p>
                                )}
                                <p className="text-xs text-muted-foreground">
                                    {review.author} (aparece como “
                                    {review.author_public}”)
                                </p>
                            </div>
                            <Button
                                variant="ghost"
                                size="icon"
                                aria-label="Remover avaliação"
                                onClick={() =>
                                    router.delete(
                                        reviews.destroy.url(review.id),
                                        { preserveScroll: true },
                                    )
                                }
                            >
                                <Trash2 className="size-4" />
                            </Button>
                        </li>
                    ))}
                    {list.data.length === 0 && (
                        <li className="p-8 text-center text-muted-foreground">
                            Nenhuma avaliação ainda.
                        </li>
                    )}
                </ul>

                <Pagination links={list.links} />
            </div>
        </>
    );
}
