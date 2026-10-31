import { createInertiaApp } from '@inertiajs/react';
import { configureEcho } from '@laravel/echo-react';
import { Toaster } from '@/components/ui/sonner';
import { TooltipProvider } from '@/components/ui/tooltip';
import { initializeTheme } from '@/hooks/use-appearance';
import AdminLayout from '@/layouts/admin-layout';
import AuthLayout from '@/layouts/auth-layout';
import StoreLayout from '@/layouts/store-layout';

const appName = import.meta.env.VITE_APP_NAME || 'Renice';

// Live updates over Laravel Reverb. Host, port and key come from the
// VITE_REVERB_* variables; without them the app runs without WebSockets.
if (import.meta.env.VITE_REVERB_APP_KEY) {
    configureEcho({ broadcaster: 'reverb' });
}

void createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    layout: (name) => {
        if (name.startsWith('auth/')) {
            return AuthLayout;
        }

        return name.startsWith('admin/') ? AdminLayout : StoreLayout;
    },
    strictMode: true,
    withApp(app) {
        return (
            <TooltipProvider delayDuration={0}>
                {app}
                <Toaster />
            </TooltipProvider>
        );
    },
    progress: {
        color: '#208090',
    },
});

// Applies light / dark mode on load.
initializeTheme();
