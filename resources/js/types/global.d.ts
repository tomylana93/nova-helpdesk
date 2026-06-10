import '@inertiajs/core';
import type { createHeadManager, Page, router } from '@inertiajs/core';
import type Echo from 'laravel-echo';
import type { SharedPageProps } from '@/types/auth';

// Extend ImportMeta interface for Vite...
declare module 'vite/client' {
    interface ImportMetaEnv {
        readonly VITE_APP_NAME: string;
        [key: string]: string | boolean | undefined;
    }

    interface ImportMeta {
        readonly env: ImportMetaEnv;
    }
}

declare module '@inertiajs/core' {
    export interface InertiaConfig {
        sharedPageProps: SharedPageProps;
    }
}

declare module 'vue' {
    interface ComponentCustomProperties {
        $inertia: typeof router;
        $page: Page<SharedPageProps>;
        $headManager: ReturnType<typeof createHeadManager>;
    }
}

declare global {
    interface Window {
        Echo: Echo;
    }
}
