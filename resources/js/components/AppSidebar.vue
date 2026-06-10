<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { Database, LayoutGrid, Settings, Ticket } from 'lucide-vue-next';
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
                abilities.manage_queues ||
                abilities.manage_categories ||
                abilities.manage_sla_policies
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
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
