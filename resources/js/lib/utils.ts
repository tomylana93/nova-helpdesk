import type { InertiaLinkProps } from '@inertiajs/vue3';
import { type ClassValue, clsx } from 'clsx';
import { twMerge } from 'tailwind-merge';
import type { Ref } from 'vue';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function toUrl(href: NonNullable<InertiaLinkProps['href']>) {
    return typeof href === 'string' ? href : href?.url;
}

export function valueUpdater<T>(
    updaterOrValue: T | ((prev: T) => T),
    ref: Ref<T>,
): void {
    if (typeof updaterOrValue === 'function') {
        ref.value = (updaterOrValue as (prev: T) => T)(ref.value);
    } else {
        ref.value = updaterOrValue;
    }
}

export function debounce<T extends (...args: any[]) => void>(
    func: T,
    wait = 300,
    immediate = false,
): ((...args: Parameters<T>) => void) & { cancel: () => void } {
    let timeout: ReturnType<typeof setTimeout> | undefined;

    const debounced = (...args: Parameters<T>) => {
        const callNow = immediate && !timeout;

        clearTimeout(timeout);
        timeout = setTimeout(() => {
            timeout = undefined;
            if (!immediate) func(...args);
        }, wait);

        if (callNow) func(...args);
    };

    debounced.cancel = () => {
        if (timeout) clearTimeout(timeout);
        timeout = undefined;
    };

    return debounced;
}
