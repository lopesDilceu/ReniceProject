import { usePage } from '@inertiajs/react';
import { FlaskConical } from 'lucide-react';

/**
 * On the public demo (DEMO_MODE), every page says so: the data is reset
 * every night and payments are simulated.
 */
export default function DemoBanner() {
    const { demo } = usePage().props;

    if (!demo) {
        return null;
    }

    return (
        <div
            role="note"
            className="flex items-center justify-center gap-2 bg-amber-100 px-4 py-1.5 text-center text-xs text-amber-950 dark:bg-amber-950 dark:text-amber-100"
        >
            <FlaskConical className="size-3.5 shrink-0" aria-hidden />
            Ambiente de demonstração: os dados voltam ao original toda noite e
            os pagamentos são simulados.
        </div>
    );
}
