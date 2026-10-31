import { Link } from '@inertiajs/react';
import type { ReactNode } from 'react';
import AppLogo from '@/components/app-logo';
import { useFlashToast } from '@/hooks/use-flash-toast';
import { home } from '@/routes';

type Props = {
    title?: string;
    description?: string;
    children: ReactNode;
};

export default function AuthLayout({
    title = '',
    description = '',
    children,
}: Props) {
    useFlashToast();

    return (
        <div className="flex min-h-svh flex-col items-center justify-center bg-muted/40 p-6 md:p-10">
            <div className="w-full max-w-sm space-y-8 rounded-xl border bg-background p-8 shadow-sm">
                <div className="flex flex-col items-center gap-6">
                    <Link href={home()} aria-label="Renice — voltar à loja">
                        <AppLogo className="h-10" />
                    </Link>
                    <div className="space-y-2 text-center">
                        <h1 className="text-xl font-medium">{title}</h1>
                        <p className="text-sm text-muted-foreground">
                            {description}
                        </p>
                    </div>
                </div>
                {children}
            </div>
        </div>
    );
}
