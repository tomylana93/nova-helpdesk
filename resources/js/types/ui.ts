import type { InertiaLinkProps } from '@inertiajs/vue3';
import type { Component } from 'vue';

export type Appearance = 'light' | 'dark' | 'system';
export type ResolvedAppearance = 'light' | 'dark';

export type AppShellVariant = 'header' | 'sidebar';

export type ModuleCard = {
    key: string;
    badgeKey: string;
    titleKey: string;
    descriptionKey: string;
    ctaKey: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon: Component;
};

export type Option = {
    label: string;
    value: string;
    color?: string;
};
