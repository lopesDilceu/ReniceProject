import { cn } from '@/lib/utils';

export default function AppLogo({ className }: { className?: string }) {
    return (
        <img
            src="/images/logo/renice-logo-side.png"
            alt="Renice"
            width={834}
            height={341}
            className={cn('h-8 w-auto dark:brightness-125', className)}
        />
    );
}
