import { usePage } from '@inertiajs/vue3';
import { watch } from 'vue';
import type { SharedPageProps, SharedStyleSettings } from '@/types';

const FONT_STYLESHEET_ID = 'site-font-stylesheet';

function ensureFontStylesheetLink(): HTMLLinkElement | null {
    if (typeof document === 'undefined') {
        return null;
    }

    const existingLink = document.getElementById(FONT_STYLESHEET_ID);

    if (existingLink instanceof HTMLLinkElement) {
        return existingLink;
    }

    const link = document.createElement('link');
    link.id = FONT_STYLESHEET_ID;
    link.rel = 'stylesheet';
    document.head.appendChild(link);

    return link;
}

function applyStyleSettings(style: SharedStyleSettings): void {
    if (typeof document === 'undefined') {
        return;
    }

    document.documentElement.dataset.theme = style.site_theme;
    document.documentElement.dataset.font = style.site_font;

    const fontStylesheetLink = ensureFontStylesheetLink();

    if (!fontStylesheetLink) {
        return;
    }

    if (fontStylesheetLink.href !== style.font_url) {
        fontStylesheetLink.href = style.font_url;
    }
}

export function useStyleSettings(): void {
    const page = usePage<SharedPageProps>();

    watch(
        () => page.props.style,
        (style) => {
            applyStyleSettings(style);
        },
        {
            deep: true,
            immediate: true,
        },
    );
}
