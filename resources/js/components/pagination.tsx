import { Link } from '@inertiajs/react';
import { cn } from '@/lib/utils';
import type { PaginationLink } from '@/types';

/** Laravel labels the arrows with HTML entities ("&laquo; Previous"). */
function label(link: PaginationLink, index: number, total: number): string {
    if (index === 0) {
        return 'Anterior';
    }

    if (index === total - 1) {
        return 'Próxima';
    }

    return link.label;
}

export default function Pagination({ links }: { links: PaginationLink[] }) {
    if (links.length <= 3) {
        return null;
    }

    return (
        <nav
            aria-label="Paginação"
            className="flex flex-wrap justify-center gap-1"
        >
            {links.map((link, index) =>
                link.url ? (
                    <Link
                        key={index}
                        href={link.url}
                        preserveScroll={false}
                        aria-current={link.active ? 'page' : undefined}
                        className={cn(
                            'min-w-9 rounded-md border px-3 py-1.5 text-center text-sm transition-colors hover:bg-accent',
                            link.active &&
                                'border-primary bg-primary text-primary-foreground hover:bg-primary',
                        )}
                    >
                        {label(link, index, links.length)}
                    </Link>
                ) : (
                    <span
                        key={index}
                        className="min-w-9 rounded-md px-3 py-1.5 text-center text-sm text-muted-foreground"
                    >
                        {label(link, index, links.length)}
                    </span>
                ),
            )}
        </nav>
    );
}
