import { Link } from '@inertiajs/react';
import {
    Activity,
    ArrowLeft,
    LayoutDashboard,
    Menu,
    MessageSquare,
    Package,
    Receipt,
    Truck,
    Users,
} from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import type { ReactNode } from 'react';
import AppLogo from '@/components/app-logo';
import DemoBanner from '@/components/demo-banner';
import { Button } from '@/components/ui/button';
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
import { useCurrentUrl } from '@/hooks/use-current-url';
import { useFlashToast } from '@/hooks/use-flash-toast';
import { cn } from '@/lib/utils';
import { home } from '@/routes';
import admin from '@/routes/admin';
import customers from '@/routes/admin/customers';
import orders from '@/routes/admin/orders';
import products from '@/routes/admin/products';
import purchases from '@/routes/admin/purchases';
import reviews from '@/routes/admin/reviews';

type Item = { title: string; href: string; icon: LucideIcon };

const items: Item[] = [
    { title: 'Visão geral', href: admin.home.url(), icon: LayoutDashboard },
    { title: 'Pedidos', href: orders.index.url(), icon: Receipt },
    { title: 'Produtos', href: products.index.url(), icon: Package },
    { title: 'Compras', href: purchases.index.url(), icon: Truck },
    { title: 'Clientes', href: customers.index.url(), icon: Users },
    { title: 'Avaliações', href: reviews.index.url(), icon: MessageSquare },
];

function Navigation() {
    const { isCurrentUrl, isCurrentOrParentUrl } = useCurrentUrl();

    return (
        <nav aria-label="Administração" className="flex flex-col gap-1">
            {items.map(({ title, href, icon: Icon }) => (
                <Link
                    key={href}
                    href={href}
                    className={cn(
                        'flex items-center gap-3 rounded-md px-3 py-2 text-sm text-muted-foreground transition-colors hover:bg-accent hover:text-foreground',
                        (href === admin.home.url()
                            ? isCurrentUrl(href)
                            : isCurrentOrParentUrl(href)) &&
                            'bg-accent font-medium text-foreground',
                    )}
                >
                    <Icon className="size-4" /> {title}
                </Link>
            ))}
            {/* Pulse is its own (Livewire) page: a full page load, not Inertia. */}
            <a
                href="/pulse"
                className="flex items-center gap-3 rounded-md px-3 py-2 text-sm text-muted-foreground transition-colors hover:bg-accent hover:text-foreground"
            >
                <Activity className="size-4" /> Monitoramento
            </a>
            <Link
                href={home()}
                className="mt-4 flex items-center gap-3 rounded-md px-3 py-2 text-sm text-muted-foreground hover:text-foreground"
            >
                <ArrowLeft className="size-4" /> Voltar à loja
            </Link>
        </nav>
    );
}

export default function AdminLayout({ children }: { children: ReactNode }) {
    useFlashToast();

    return (
        <div className="flex min-h-svh bg-muted/30">
            <aside className="hidden w-60 shrink-0 border-r bg-background p-4 md:block">
                <div className="mb-6 px-3">
                    <AppLogo />
                    <p className="mt-1 text-xs text-muted-foreground">
                        Administração
                    </p>
                </div>
                <Navigation />
            </aside>

            <div className="flex-1">
                <DemoBanner />
                <header className="flex h-14 items-center gap-3 border-b bg-background px-4 md:hidden">
                    <Sheet>
                        <SheetTrigger asChild>
                            <Button
                                variant="ghost"
                                size="icon"
                                aria-label="Abrir menu"
                            >
                                <Menu className="size-5" />
                            </Button>
                        </SheetTrigger>
                        <SheetContent side="left" className="w-64 p-4">
                            <SheetHeader className="px-3">
                                <SheetTitle>
                                    <AppLogo />
                                </SheetTitle>
                            </SheetHeader>
                            <Navigation />
                        </SheetContent>
                    </Sheet>
                    <span className="font-medium">Administração</span>
                </header>

                <main className="mx-auto max-w-6xl p-4 md:p-8">{children}</main>
            </div>
        </div>
    );
}
