import { Link } from '@inertiajs/react';
import ProductImage from '@/components/catalog/product-image';
import RatingStars from '@/components/catalog/rating-stars';
import { Badge } from '@/components/ui/badge';
import { formatMoney } from '@/lib/format';
import { show } from '@/routes/products';
import type { Product } from '@/types';

export default function ProductCard({ product }: { product: Product }) {
    return (
        <Link
            href={show(product.slug)}
            prefetch
            className="group flex flex-col overflow-hidden rounded-xl border bg-card transition-shadow hover:shadow-md focus-visible:ring-[3px] focus-visible:ring-ring focus-visible:outline-none"
        >
            <div className="relative">
                <ProductImage
                    src={product.image_url}
                    alt={product.name}
                    className="transition-transform duration-300 group-hover:scale-105"
                />
                {!product.available && (
                    <Badge
                        variant="secondary"
                        className="absolute top-3 left-3"
                    >
                        Esgotado
                    </Badge>
                )}
                {product.low_stock && (
                    <Badge className="absolute top-3 left-3">
                        Últimas unidades
                    </Badge>
                )}
            </div>

            <div className="flex flex-1 flex-col gap-2 p-4">
                <h2 className="leading-snug font-medium">{product.name}</h2>
                <p className="line-clamp-2 text-sm text-muted-foreground">
                    {product.excerpt}
                </p>
                <div className="mt-auto flex items-end justify-between pt-2">
                    <span className="text-lg font-semibold">
                        {formatMoney(product.price_cents)}
                    </span>
                    {product.rating !== null && (
                        <span className="flex items-center gap-1 text-xs text-muted-foreground">
                            <RatingStars rating={product.rating} />(
                            {product.reviews_count})
                        </span>
                    )}
                </div>
            </div>
        </Link>
    );
}
