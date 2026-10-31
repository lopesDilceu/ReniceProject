import { CupSoda } from 'lucide-react';
import { cn } from '@/lib/utils';

type Props = {
    src: string | null;
    alt: string;
    className?: string;
};

/**
 * Product photo, or a branded placeholder while the catalog has no images
 * (v1's photos were runtime uploads and were not preserved).
 */
export default function ProductImage({ src, alt, className }: Props) {
    if (src) {
        return (
            <img
                src={src}
                alt={alt}
                loading="lazy"
                className={cn('aspect-square w-full object-cover', className)}
            />
        );
    }

    return (
        <div
            role="img"
            aria-label={alt}
            className={cn(
                'flex aspect-square w-full items-center justify-center bg-gradient-to-br from-cyan-50 to-sky-100 text-primary dark:from-cyan-950 dark:to-slate-900',
                className,
            )}
        >
            <CupSoda className="size-1/3 opacity-60" aria-hidden />
        </div>
    );
}
