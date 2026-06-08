import type { InertiaLinkProps } from '@inertiajs/vue3';
import { clsx } from 'clsx';
import type { ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';
import type { UserStatus } from '@/types';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function toUrl(href: NonNullable<InertiaLinkProps['href']>) {
    return typeof href === 'string' ? href : href?.url;
}

export function renderFlagIcon(icon?: string): string {
    if (!icon) {
        return '';
    }

    return icon
        .trim()
        .slice(0, 2)
        .toUpperCase()
        .replace(/[A-Z]/g, (character) =>
            String.fromCodePoint(character.charCodeAt(0) + 127397),
        );
}

export function statusClasses(status: UserStatus): string {
    switch (status) {
        case 'active':
            return 'border-emerald-500/25 bg-emerald-500/10 text-emerald-700 dark:text-emerald-200';
        case 'disable':
            return 'border-amber-500/25 bg-amber-500/10 text-amber-700 dark:text-amber-200';
        case 'suspend':
            return 'border-rose-500/25 bg-rose-500/10 text-rose-700 dark:text-rose-200';
    }
}
