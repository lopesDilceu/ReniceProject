import { formatDateTime } from '@/lib/format';
import { cn } from '@/lib/utils';
import type { OrderStatusChange } from '@/types';

/**
 * Every status the order went through, oldest first. The actor is only sent
 * to admin pages; there, a missing actor means the system made the change.
 */
export default function OrderTimeline({
    timeline,
}: {
    timeline: OrderStatusChange[];
}) {
    return (
        <ol className="space-y-4">
            {timeline.map((change, index) => {
                const current = index === timeline.length - 1;

                return (
                    <li key={change.id} className="relative flex gap-3">
                        {!current && (
                            <span
                                className="absolute top-4 left-[5px] h-full w-px bg-border"
                                aria-hidden
                            />
                        )}
                        <span
                            className={cn(
                                'relative mt-1.5 size-[11px] shrink-0 rounded-full border-2',
                                current
                                    ? 'border-primary bg-primary'
                                    : 'border-muted-foreground/40 bg-background',
                            )}
                            aria-hidden
                        />
                        <div className="space-y-0.5 text-sm">
                            <p className={cn(current && 'font-medium')}>
                                {change.status_label}
                            </p>
                            <p className="text-muted-foreground">
                                <time dateTime={change.created_at}>
                                    {formatDateTime(change.created_at)}
                                </time>
                                {change.actor !== undefined &&
                                    ` · ${change.actor ?? 'Sistema'}`}
                            </p>
                            {change.reason && (
                                <p className="text-muted-foreground italic">
                                    {change.reason}
                                </p>
                            )}
                        </div>
                    </li>
                );
            })}
        </ol>
    );
}
