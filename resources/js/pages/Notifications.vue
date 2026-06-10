<script setup lang="ts">
import { Head, Link, router, setLayoutProps, usePage } from '@inertiajs/vue3';
import {
    CheckCheck,
    Inbox,
    MessageSquare,
    ShieldAlert,
    Ticket,
} from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import {
    index as notificationsIndexRoute,
    read,
    readAll,
} from '@/routes/notifications';
import { show as showTicketRoute } from '@/routes/tickets';
import type { AuthenticatedSharedPageProps, NotificationItem } from '@/types';

type PaginatedNotifications = {
    current_page: number;
    data: NotificationItem[];
    first_page_url: string;
    from: number;
    last_page: number;
    last_page_url: string;
    next_page_url: string | null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
    to: number;
    total: number;
};

type Props = {
    notifications: PaginatedNotifications;
};

interface EchoNotification {
    id: string;
    type?: string;
    ticket_id?: string | null;
    ticket_number?: string | null;
    subject?: string | null;
    message?: string;
}

const props = defineProps<Props>();
const page = usePage<AuthenticatedSharedPageProps>();

setLayoutProps({
    breadcrumbs: [
        {
            title: 'Dashboard',
            href: dashboard(),
        },
        {
            title: 'Notifications',
            href: notificationsIndexRoute(),
        },
    ],
});

const localNotifications = ref<NotificationItem[]>(props.notifications.data);

// Keep local state in sync when Inertia reloads the page
watch(
    () => props.notifications.data,
    (newData) => {
        localNotifications.value = newData;
    },
);

const unreadCount = computed(() => {
    return localNotifications.value.filter((n) => !n.read_at).length;
});

const handleMarkAllAsRead = () => {
    router.post(
        readAll().url,
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                localNotifications.value = localNotifications.value.map(
                    (n) => ({
                        ...n,
                        read_at: new Date().toISOString(),
                    }),
                );
            },
        },
    );
};

const handleMarkAsRead = (id: string) => {
    router.post(
        read(id).url,
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                localNotifications.value = localNotifications.value.map((n) =>
                    n.id === id
                        ? { ...n, read_at: new Date().toISOString() }
                        : n,
                );
            },
        },
    );
};

const getIcon = (type: string) => {
    switch (type) {
        case 'comment':
            return MessageSquare;
        case 'sla_warning':
        case 'sla_breached':
            return ShieldAlert;
        default:
            return Ticket;
    }
};

const getIconColor = (type: string) => {
    switch (type) {
        case 'comment':
            return 'text-blue-500 bg-blue-50 dark:bg-blue-950/50';
        case 'sla_warning':
            return 'text-amber-500 bg-amber-50 dark:bg-amber-950/50';
        case 'sla_breached':
            return 'text-red-500 bg-red-50 dark:bg-red-950/50';
        default:
            return 'text-primary bg-primary/10';
    }
};

// Listen for incoming notifications in real-time to append to the active page
onMounted(() => {
    const user = page.props.auth.user;

    if (user && window.Echo) {
        window.Echo.private(`App.Models.User.${user.id}`).notification(
            (notification: EchoNotification) => {
                // Prepend new notification to current view if we are on page 1
                if (props.notifications.current_page === 1) {
                    const newItem: NotificationItem = {
                        id: notification.id,
                        type: notification.type || 'info',
                        ticket_id: notification.ticket_id || null,
                        ticket_number: notification.ticket_number || null,
                        subject: notification.subject || null,
                        message: notification.message || '',
                        created_at: new Date().toISOString(),
                    };

                    localNotifications.value = [
                        newItem,
                        ...localNotifications.value,
                    ];
                }
            },
        );
    }
});

onUnmounted(() => {
    const user = page.props.auth.user;

    if (user && window.Echo) {
        window.Echo.leave(`App.Models.User.${user.id}`);
    }
});
</script>

<template>
    <Head title="Notifications" />

    <div class="mx-auto flex w-full max-w-4xl flex-1 flex-col gap-6 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1
                    class="text-2xl font-bold tracking-tight text-neutral-900 dark:text-neutral-100"
                >
                    Notifications
                </h1>

                <p class="text-sm text-neutral-500 dark:text-neutral-400">
                    Stay updated with your tickets, approvals, and SLA status.
                </p>
            </div>

            <Button
                v-if="unreadCount > 0"
                variant="outline"
                size="sm"
                @click="handleMarkAllAsRead"
                class="flex cursor-pointer items-center gap-2"
            >
                <CheckCheck class="size-4" />
                Mark all read
            </Button>
        </div>

        <div
            class="overflow-hidden rounded-2xl border border-neutral-200 bg-white shadow-sm dark:border-neutral-800 dark:bg-neutral-900"
        >
            <div
                v-if="localNotifications.length === 0"
                class="flex flex-col items-center justify-center py-16 text-neutral-400"
            >
                <Inbox class="mb-3 size-12 stroke-1 opacity-60" />

                <h3 class="text-sm font-semibold">No notifications</h3>
                <p class="mt-1 text-xs text-neutral-500">
                    You are all caught up!
                </p>
            </div>

            <div
                v-else
                class="divide-y divide-neutral-100 dark:divide-neutral-800"
            >
                <div
                    v-for="item in localNotifications"
                    :key="item.id"
                    class="relative flex items-start gap-4 p-5 transition duration-150"
                    :class="[
                        !item.read_at
                            ? 'bg-neutral-50/50 dark:bg-neutral-800/20'
                            : '',
                    ]"
                >
                    <div
                        class="flex shrink-0 items-center justify-center rounded-xl p-2.5"
                        :class="[getIconColor(item.type)]"
                    >
                        <component :is="getIcon(item.type)" class="size-5" />
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex items-center gap-2">
                                <span
                                    v-if="item.ticket_number"
                                    class="rounded bg-neutral-100 px-2 py-0.5 text-xs font-semibold tracking-wider text-neutral-500 uppercase dark:bg-neutral-800 dark:text-neutral-400"
                                >
                                    {{ item.ticket_number }}
                                </span>

                                <span class="text-xs text-neutral-400">
                                    {{
                                        new Date(
                                            item.created_at,
                                        ).toLocaleDateString([], {
                                            month: 'short',
                                            day: 'numeric',
                                            hour: '2-digit',
                                            minute: '2-digit',
                                        })
                                    }}
                                </span>
                            </div>

                            <Button
                                v-if="!item.read_at"
                                variant="ghost"
                                size="sm"
                                @click="handleMarkAsRead(item.id)"
                                class="h-8 text-xs font-medium text-neutral-500 hover:text-neutral-900 dark:hover:text-neutral-100"
                            >
                                Mark as read
                            </Button>
                        </div>

                        <div class="mt-1.5">
                            <Link
                                v-if="item.ticket_id"
                                :href="showTicketRoute(item.ticket_id)"
                                @click="
                                    !item.read_at && handleMarkAsRead(item.id)
                                "
                                class="text-sm font-semibold text-neutral-800 transition hover:text-primary dark:text-neutral-200"
                            >
                                {{ item.message }}
                            </Link>

                            <p
                                v-else
                                class="text-sm font-semibold text-neutral-800 dark:text-neutral-200"
                            >
                                {{ item.message }}
                            </p>

                            <p
                                v-if="item.subject"
                                class="mt-1 line-clamp-1 text-xs text-neutral-500"
                            >
                                Subject: {{ item.subject }}
                            </p>
                        </div>
                    </div>

                    <div
                        v-if="!item.read_at"
                        class="absolute top-0 bottom-0 left-0 w-1 rounded-l-2xl bg-primary"
                    ></div>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div
            v-if="props.notifications.last_page > 1"
            class="mt-2 flex items-center justify-between gap-4"
        >
            <Button
                variant="outline"
                size="sm"
                :disabled="!props.notifications.prev_page_url"
                @click="router.visit(props.notifications.prev_page_url!)"
                class="cursor-pointer"
            >
                Previous
            </Button>

            <span class="text-xs text-neutral-500">
                Page {{ props.notifications.current_page }} of
                {{ props.notifications.last_page }}
            </span>

            <Button
                variant="outline"
                size="sm"
                :disabled="!props.notifications.next_page_url"
                @click="router.visit(props.notifications.next_page_url!)"
                class="cursor-pointer"
            >
                Next
            </Button>
        </div>
    </div>
</template>
