<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    BarChart3,
    Database,
    LayoutGrid,
    Settings,
    Ticket,
} from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
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
import { index as adminMasterDataIndex } from '@/routes/admin/master-data';
import { index as adminSettingsIndex } from '@/routes/admin/settings';
import { index as reportsIndex } from '@/routes/reports';
import { index as ticketsIndex } from '@/routes/tickets';
import type { NavGroup, SharedPageProps } from '@/types';

const page = usePage<SharedPageProps>();

const mainNavGroups = computed<NavGroup[]>(() => {
    const abilities = page.props.auth.abilities;

    const groups: NavGroup[] = [
        {
            title: 'Main',
            items: [
                {
                    title: 'Dashboard',
                    href: dashboard(),
                    icon: LayoutGrid,
                },
                ...(abilities.view_tickets
                    ? [
                          {
                              title: 'Tickets',
                              href: ticketsIndex(),
                              icon: Ticket,
                          },
                      ]
                    : []),
                ...(abilities.view_reports
                    ? [
                          {
                              title: 'Reports',
                              href: reportsIndex(),
                              icon: BarChart3,
                          },
                      ]
                    : []),
            ],
        },
        {
            title: 'Admin',
            items: [
                ...(abilities.manage_settings
                    ? [
                          {
                              title: 'Settings',
                              href: adminSettingsIndex(),
                              icon: Settings,
                          },
                      ]
                    : []),
                ...(abilities.view_users ||
                abilities.manage_branches ||
                abilities.manage_departments ||
                abilities.manage_categories ||
                abilities.manage_sla_policies ||
                abilities.manage_assets
                    ? [
                          {
                              title: 'Master Data',
                              href: adminMasterDataIndex(),
                              icon: Database,
                          },
                      ]
                    : []),
            ],
        },
    ];

    return groups.filter((group) => group.items.length > 0);
});
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
            <NavMain :groups="mainNavGroups" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
            <div
                class="mt-1 border-t border-sidebar-border/30 px-4 py-2 text-center text-[10px] font-medium tracking-wider text-muted-foreground/40 group-data-[state=collapsed]:hidden"
            >
                v{{ page.props.version }}
            </div>
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
