import { Star } from 'lucide-react';
import { cn } from '@/lib/utils';

type Props = {
    rating: number;
    className?: string;
};

export default function RatingStars({ rating, className }: Props) {
    return (
        <span
            className={cn('inline-flex items-center gap-0.5', className)}
            role="img"
            aria-label={`${rating.toLocaleString('pt-BR')} de 5 estrelas`}
        >
            {[1, 2, 3, 4, 5].map((star) => (
                <Star
                    key={star}
                    aria-hidden
                    className={cn(
                        'size-4',
                        star <= Math.round(rating)
                            ? 'fill-amber-400 text-amber-400'
                            : 'text-muted-foreground/40',
                    )}
                />
            ))}
        </span>
    );
}
