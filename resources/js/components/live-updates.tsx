import {
    echoIsConfigured,
    useConnectionStatus,
    useEcho,
} from '@laravel/echo-react';
import { cn } from '@/lib/utils';

/** App\Events\Broadcast\OrderUpdated ("order.updated"). */
export type OrderUpdated = {
    id: number;
    status: string;
    status_label: string;
    total_cents: number;
    /** The first entry of the timeline: a new order. */
    placed: boolean;
};

/** App\Events\Broadcast\StockRunningLow ("stock.low"). */
export type StockRunningLow = {
    product_id: number;
    name: string;
    stock: number;
};

type Props<T> = {
    /** Private channel name, without the "private-" prefix. */
    channel: string;
    /** Names set with broadcastAs() on the server, e.g. "order.updated". */
    events: string[];
    onEvent: (payload: T) => void;
};

function Subscription<T>({ channel, events, onEvent }: Props<T>) {
    useEcho<T>(
        channel,
        events.map((event) => `.${event}`),
        onEvent,
        // Callbacks only reload and toast, so the first one stays valid.
        [channel],
    );

    return null;
}

/**
 * Subscribes to a private channel while mounted. Renders nothing, and does
 * nothing, when Echo is not configured (no Reverb in this environment): the
 * pages keep working on their own, with polling or manual reloads.
 */
export default function LiveUpdates<T>(props: Props<T>) {
    return echoIsConfigured() ? <Subscription<T> {...props} /> : null;
}

function Indicator() {
    const status = useConnectionStatus();
    const live = status === 'connected';

    return (
        <span
            className="inline-flex items-center gap-1.5 text-xs text-muted-foreground"
            role="status"
        >
            <span
                className={cn(
                    'size-2 rounded-full',
                    live
                        ? 'animate-pulse bg-green-500'
                        : 'bg-muted-foreground/40',
                )}
                aria-hidden
            />
            {live ? 'Ao vivo' : 'Reconectando…'}
        </span>
    );
}

/** "Ao vivo" while the WebSocket is connected. */
export function LiveIndicator() {
    return echoIsConfigured() ? <Indicator /> : null;
}
