<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import { BookOpen, Database, Folder, LayoutGrid } from 'lucide-vue-next';
import { computed } from 'vue';

import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { index as masterIndex } from '@/routes/master';
import { type NavItem } from '@/types';

import AppLogo from './AppLogo.vue';

const mainNavItems = computed<NavItem[]>(() => [
    {
        title: trans('breadcrumbs.dashboard'),
        href: dashboard(),
        icon: LayoutGrid,
    },
]);

const masterNavItems = computed<NavItem[]>(() => [
    {
        title: trans('breadcrumbs.master'),
        href: masterIndex(),
        icon: Database,
    },
]);

const footerNavItems: NavItem[] = [
    {
        title: trans('navigation.footer.github_repo'),
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: Folder,
    },
    {
        title: trans('navigation.footer.documentation'),
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
            <NavMain :items="masterNavItems" :group-label="trans('navigation.main_group.master')" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
