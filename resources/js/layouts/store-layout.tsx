import { Link, usePage } from '@inertiajs/react';
import {
    LogOut,
    Menu,
    Monitor,
    Moon,
    Package,
    Shield,
    ShoppingCart,
    Sun,
    User as UserIcon,
} from 'lucide-react';
import type { ReactNode } from 'react';
import AppLogo from '@/components/app-logo';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
import { useAppearance } from '@/hooks/use-appearance';
import type { Appearance } from '@/hooks/use-appearance';
import { useCurrentUrl } from '@/hooks/use-current-url';
import { useFlashToast } from '@/hooks/use-flash-toast';
import { useInitials } from '@/hooks/use-initials';
import { cn } from '@/lib/utils';
import {
    home,
    login,
    logout,
    minhaConta,
    minhasCompras,
    register,
    sobre,
} from '@/routes';
import adm from '@/routes/adm';
import products from '@/routes/products';
import usuario from '@/routes/usuario';
import type { User } from '@/types';

/*
| Pages still served by the v1 Blade views need full page loads, so they are
| plain anchors (`inertia: false`). Inertia <Link> is used only for pages
| already rewritten in React. Flip the flag as each module moves over.
*/
type NavLink = { title: string; href: string; inertia: boolean };

const navigation: NavLink[] = [
    { title: 'Início', href: home.url(), inertia: true },
    { title: 'Produtos', href: products.index.url(), inertia: true },
    { title: 'Sobre o projeto', href: sobre.url(), inertia: true },
];

function NavAnchor({ item, className }: { item: NavLink; className?: string }) {
    return item.inertia ? (
        <Link href={item.href} className={className} prefetch>
            {item.title}
        </Link>
    ) : (
        <a href={item.href} className={className}>
            {item.title}
        </a>
    );
}

function AppearanceMenu() {
    const { appearance, updateAppearance } = useAppearance();
    const options: { value: Appearance; label: string; icon: typeof Sun }[] = [
        { value: 'light', label: 'Claro', icon: Sun },
        { value: 'dark', label: 'Escuro', icon: Moon },
        { value: 'system', label: 'Sistema', icon: Monitor },
    ];
    const Current =
        options.find((option) => option.value === appearance)?.icon ?? Monitor;

    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <Button variant="ghost" size="icon" aria-label="Alterar tema">
                    <Current className="size-5" />
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end">
                {options.map(({ value, label, icon: Icon }) => (
                    <DropdownMenuItem
                        key={value}
                        onSelect={() => updateAppearance(value)}
                    >
                        <Icon className="size-4" /> {label}
                    </DropdownMenuItem>
                ))}
            </DropdownMenuContent>
        </DropdownMenu>
    );
}

function UserMenu({ user }: { user: User }) {
    const getInitials = useInitials();

    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <Button
                    variant="ghost"
                    className="size-10 rounded-full p-1"
                    aria-label="Menu da conta"
                >
                    <Avatar className="size-8">
                        <AvatarImage
                            src={user.avatar_url ?? undefined}
                            alt={user.name}
                        />
                        <AvatarFallback>
                            {getInitials(user.name)}
                        </AvatarFallback>
                    </Avatar>
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end" className="w-56">
                <DropdownMenuLabel className="font-normal">
                    <div className="truncate font-medium">{user.name}</div>
                    <div className="truncate text-xs text-muted-foreground">
                        {user.email}
                    </div>
                </DropdownMenuLabel>
                <DropdownMenuSeparator />
                <DropdownMenuItem asChild>
                    <a href={minhaConta.url()}>
                        <UserIcon className="size-4" /> Minha conta
                    </a>
                </DropdownMenuItem>
                <DropdownMenuItem asChild>
                    <a href={minhasCompras.url()}>
                        <Package className="size-4" /> Minhas compras
                    </a>
                </DropdownMenuItem>
                {user.is_admin && (
                    <DropdownMenuItem asChild>
                        <a href={adm.home.url()}>
                            <Shield className="size-4" /> Administração
                        </a>
                    </DropdownMenuItem>
                )}
                <DropdownMenuSeparator />
                <DropdownMenuItem asChild>
                    <Link href={logout()} as="button" className="w-full">
                        <LogOut className="size-4" /> Sair
                    </Link>
                </DropdownMenuItem>
            </DropdownMenuContent>
        </DropdownMenu>
    );
}

export default function StoreLayout({ children }: { children: ReactNode }) {
    const { auth } = usePage().props;
    const { isCurrentUrl } = useCurrentUrl();
    useFlashToast();

    const linkClass = (item: NavLink) =>
        cn(
            'text-sm font-medium text-muted-foreground transition-colors hover:text-foreground',
            item.inertia && isCurrentUrl(item.href) && 'text-foreground',
        );

    return (
        <div className="flex min-h-svh flex-col bg-background">
            <header className="sticky top-0 z-40 border-b bg-background/80 backdrop-blur">
                <div className="mx-auto flex h-16 max-w-6xl items-center gap-6 px-4">
                    <Sheet>
                        <SheetTrigger asChild>
                            <Button
                                variant="ghost"
                                size="icon"
                                className="md:hidden"
                                aria-label="Abrir menu"
                            >
                                <Menu className="size-5" />
                            </Button>
                        </SheetTrigger>
                        <SheetContent side="left" className="w-72">
                            <SheetHeader>
                                <SheetTitle>
                                    <AppLogo />
                                </SheetTitle>
                            </SheetHeader>
                            <nav className="flex flex-col gap-4 px-4">
                                {navigation.map((item) => (
                                    <NavAnchor
                                        key={item.href}
                                        item={item}
                                        className={linkClass(item)}
                                    />
                                ))}
                            </nav>
                        </SheetContent>
                    </Sheet>

                    <Link href={home.url()} aria-label="Renice — início">
                        <AppLogo />
                    </Link>

                    <nav
                        className="hidden items-center gap-6 md:flex"
                        aria-label="Principal"
                    >
                        {navigation.map((item) => (
                            <NavAnchor
                                key={item.href}
                                item={item}
                                className={linkClass(item)}
                            />
                        ))}
                    </nav>

                    <div className="ml-auto flex items-center gap-1">
                        <AppearanceMenu />
                        {auth.user ? (
                            <>
                                <Button variant="ghost" size="icon" asChild>
                                    <a
                                        href={usuario.carrinho.url()}
                                        aria-label="Carrinho"
                                    >
                                        <ShoppingCart className="size-5" />
                                    </a>
                                </Button>
                                <UserMenu user={auth.user} />
                            </>
                        ) : (
                            <>
                                <Button variant="ghost" asChild>
                                    <Link href={login()}>Entrar</Link>
                                </Button>
                                <Button
                                    asChild
                                    className="hidden sm:inline-flex"
                                >
                                    <Link href={register()}>Criar conta</Link>
                                </Button>
                            </>
                        )}
                    </div>
                </div>
            </header>

            <main className="flex-1">{children}</main>

            <footer className="border-t">
                <div className="mx-auto flex max-w-6xl flex-col items-center justify-between gap-4 px-4 py-6 text-sm text-muted-foreground sm:flex-row">
                    <p>
                        © 2024–{new Date().getFullYear()} Renice · Projeto de
                        portfólio
                    </p>
                    <div className="flex gap-6">
                        <Link
                            href={sobre.url()}
                            className="hover:text-foreground"
                        >
                            Sobre o projeto
                        </Link>
                        <a
                            href="https://github.com/lopesDilceu/ReniceProject"
                            className="hover:text-foreground"
                            target="_blank"
                            rel="noreferrer"
                        >
                            Código no GitHub
                        </a>
                    </div>
                </div>
            </footer>
        </div>
    );
}
