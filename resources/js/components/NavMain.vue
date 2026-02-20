<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { type NavItem } from '@/types';

const props = withDefaults(
    defineProps<{
        items?: NavItem[];
        groupLabel?: string;
    }>(),
    {
        items: () => [],
        groupLabel: undefined,
    },
);

const { isCurrentUrl } = useCurrentUrl();
const hasItems = computed<boolean>(() => props.items.length > 0);
</script>

<template>
    <SidebarGroup v-if="hasItems" class="px-2 py-0">
        <SidebarGroupLabel v-if="props.groupLabel">
            {{ props.groupLabel }}
        </SidebarGroupLabel>
        <SidebarMenu>
            <SidebarMenuItem v-for="item in props.items" :key="item.title">
                <SidebarMenuButton
                    as-child
                    :is-active="isCurrentUrl(item.href)"
                    :tooltip="item.title"
                >
                    <Link :href="item.href">
                        <component :is="item.icon" />
                        <span>{{ item.title }}</span>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>
