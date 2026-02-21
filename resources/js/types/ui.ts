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
    href: string;
    icon: Component;
};
