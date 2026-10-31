import { router } from '@inertiajs/react';
import { usePasskeyVerify } from '@laravel/passkeys/react';
import { KeyRound } from 'lucide-react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { Spinner } from '@/components/ui/spinner';

type Props = {
    /** Defaults to the login ceremony; password confirmation passes its own. */
    routes?: { options: string; submit: string };
    label: string;
    separator: string;
};

/**
 * Signs in (or confirms the password) with a passkey. Renders nothing in
 * browsers without WebAuthn, so the password form is always the fallback.
 */
export default function PasskeyButton({ routes, label, separator }: Props) {
    const { verify, isLoading, error, isSupported } = usePasskeyVerify({
        routes,
        onSuccess: (response) => router.visit(response.redirect ?? '/'),
    });

    if (!isSupported) {
        return null;
    }

    return (
        <div className="space-y-6">
            <div className="grid gap-2">
                <Button
                    type="button"
                    variant="outline"
                    className="w-full"
                    onClick={verify}
                    disabled={isLoading}
                >
                    {isLoading ? (
                        <Spinner />
                    ) : (
                        <KeyRound className="size-4" aria-hidden />
                    )}
                    {label}
                </Button>
                <InputError
                    message={error ?? undefined}
                    className="text-center"
                />
            </div>

            <div className="flex items-center gap-3 text-xs text-muted-foreground uppercase">
                <Separator className="flex-1" />
                {separator}
                <Separator className="flex-1" />
            </div>
        </div>
    );
}
