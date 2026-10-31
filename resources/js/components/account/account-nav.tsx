import { Link } from '@inertiajs/react';
import { useCurrentUrl } from '@/hooks/use-current-url';
import { cn } from '@/lib/utils';
import { edit } from '@/routes/account';
import { index as orders } from '@/routes/orders';

const tabs = [
    { title: 'Meus dados', href: edit.url() },
    { title: 'Meus pedidos', href: orders.url() },
];

export default function AccountNav() {
    const { isCurrentUrl } = useCurrentUrl();

    return (
        <nav aria-label="Conta" className="flex gap-1 border-b">
            {tabs.map((tab) => (
                <Link
                    key={tab.href}
                    href={tab.href}
                    className={cn(
                        '-mb-px border-b-2 border-transparent px-4 py-2 text-sm font-medium text-muted-foreground hover:text-foreground',
                        isCurrentUrl(tab.href) &&
                            'border-primary text-foreground',
                    )}
                >
                    {tab.title}
                </Link>
            ))}
        </nav>
    );
}
