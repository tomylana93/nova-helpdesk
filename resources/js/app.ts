import { createInertiaApp } from '@inertiajs/vue3';
import { configureEcho } from '@laravel/echo-vue';
import { initializeTheme } from '@/composables/useAppearance';
import AppLayout from '@/layouts/AppLayout.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { initializeFlashToast } from '@/lib/flashToast';

configureEcho({
    broadcaster: 'reverb',
});

createInertiaApp({
    title: (title) => {
        let name = import.meta.env.VITE_APP_NAME || 'Nova Helpdesk';

        if (typeof window !== 'undefined') {
            const pageEl = document.getElementById('app');
            const pageAttr = pageEl?.getAttribute('data-page');

            if (pageAttr) {
                try {
                    const parsed = JSON.parse(pageAttr);
                    name = parsed.props?.name || name;
                } catch {
                    // Keep default fallback
                }
            }
        }

        return title ? `${title} - ${name}` : name;
    },
    layout: (name) => {
        switch (true) {
            case name.startsWith('auth/'):
                return AuthLayout;
            case name.startsWith('settings/'):
                return [AppLayout, SettingsLayout];
            default:
                return AppLayout;
        }
    },
    progress: {
        color: '#4B5563',
    },
});

initializeTheme();
initializeFlashToast();
