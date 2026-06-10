<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { Bell, CheckCheck, Inbox } from 'lucide-vue-next';
import { onMounted, onUnmounted, ref, watch } from 'vue';
import { toast } from 'vue-sonner';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    index as notificationsIndexRoute,
    read,
    readAll,
} from '@/routes/notifications';
import { show as showTicketRoute } from '@/routes/tickets';
import type { AuthenticatedSharedPageProps, NotificationItem } from '@/types';

interface EchoNotification {
    id: string;
    type?: string;
    ticket_id?: string | null;
    ticket_number?: string | null;
    subject?: string | null;
    message?: string;
}

interface TicketCreatedEvent {
    ticket_id: string;
    ticket_number: string;
    subject: string;
    message: string;
}

interface SlaEscalatedEvent {
    ticket_id: string;
    ticket_number: string;
    subject: string;
    escalation_type: 'warning' | 'breached';
    message: string;
}

const page = usePage<AuthenticatedSharedPageProps>();
const unreadCount = ref(page.props.auth.unreadNotificationsCount);
const notificationsList = ref<NotificationItem[]>(
    page.props.auth.notifications,
);

watch(
    () => page.props.auth.unreadNotificationsCount,
    (val) => {
        unreadCount.value = val;
    },
);

watch(
    () => page.props.auth.notifications,
    (val) => {
        notificationsList.value = val;
    },
);

const handleMarkAllAsRead = () => {
    router.post(
        readAll().url,
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                unreadCount.value = 0;
                notificationsList.value = [];
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
        },
    );
};

onMounted(() => {
    const user = page.props.auth.user;

    if (user && window.Echo) {
        // Listen to personal private notification channel
        window.Echo.private(`App.Models.User.${user.id}`).notification(
            (notification: EchoNotification) => {
                unreadCount.value++;

                const newItem: NotificationItem = {
                    id: notification.id,
                    type: notification.type || 'info',
                    ticket_id: notification.ticket_id || null,
                    ticket_number: notification.ticket_number || null,
                    subject: notification.subject || null,
                    message: notification.message || '',
                    created_at: new Date().toISOString(),
                };

                notificationsList.value = [
                    newItem,
                    ...notificationsList.value.slice(0, 4),
                ];

                toast.info(notification.message || 'New notification', {
                    description: notification.subject || undefined,
                    action: notification.ticket_id
                        ? {
                              label: 'View Ticket',
                              onClick: () => {
                                  handleMarkAsRead(notification.id);
                                  router.visit(
                                      showTicketRoute(notification.ticket_id!),
                                  );
                              },
                          }
                        : undefined,
                });
            },
        );

        // Listen to shared agent channel if IT Agent or Super Admin
        const isAgent = user.role === 'it_agent' || user.role === 'super_admin';

        if (isAgent) {
            window.Echo.private('helpdesk.agents')
                .listen(
                    '.App\\Events\\TicketCreated',
                    (e: TicketCreatedEvent) => {
                        toast.info(
                            `New Unassigned Ticket: ${e.ticket_number}`,
                            {
                                description: e.subject,
                                action: {
                                    label: 'View',
                                    onClick: () => {
                                        router.visit(
                                            showTicketRoute(e.ticket_id),
                                        );
                                    },
                                },
                            },
                        );
                    },
                )
                .listen(
                    '.App\\Events\\SlaEscalated',
                    (e: SlaEscalatedEvent) => {
                        const alertType =
                            e.escalation_type === 'breached'
                                ? 'error'
                                : 'warning';

                        toast[alertType](`SLA Alert (${e.escalation_type})`, {
                            description: `${e.ticket_number}: ${e.subject}`,
                            action: {
                                label: 'View',
                                onClick: () => {
                                    router.visit(showTicketRoute(e.ticket_id));
                                },
                            },
                        });
                    },
                );
        }
    }
});

onUnmounted(() => {
    const user = page.props.auth.user;

    if (user && window.Echo) {
        window.Echo.leave(`App.Models.User.${user.id}`);
        window.Echo.leave('helpdesk.agents');
    }
});
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger :as-child="true">
            <Button
                variant="ghost"
                size="icon"
                class="relative size-9 cursor-pointer rounded-full"
            >
                <Bell class="size-5 opacity-80" />
                <span
                    v-if="unreadCount > 0"
                    class="absolute top-1 right-1 flex size-4 animate-pulse items-center justify-center rounded-full bg-destructive text-[9px] font-bold text-destructive-foreground"
                >
                    {{ unreadCount }}
                </span>
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent
            align="end"
            class="w-80 overflow-hidden rounded-xl border border-neutral-200 bg-white p-0 shadow-xl dark:border-neutral-800 dark:bg-neutral-900"
        >
            <div
                class="flex items-center justify-between border-b border-neutral-100 px-4 py-3 dark:border-neutral-800"
            >
                <span
                    class="text-sm font-semibold text-neutral-900 dark:text-neutral-100"
                    >Notifications</span
                >

                <button
                    v-if="unreadCount > 0"
                    @click="handleMarkAllAsRead"
                    class="flex items-center gap-1 text-xs font-medium text-primary transition hover:text-primary/80"
                >
                    <CheckCheck class="size-3.5" />
                    Mark all read
                </button>
            </div>

            <div class="max-h-80 overflow-y-auto">
                <div
                    v-if="notificationsList.length === 0"
                    class="flex flex-col items-center justify-center py-8 text-neutral-400"
                >
                    <Inbox class="mb-2 size-8 stroke-1 opacity-60" />
                    <span class="text-xs">No unread notifications</span>
                </div>

                <div
                    v-else
                    class="divide-y divide-neutral-100 dark:divide-neutral-800"
                >
                    <div
                        v-for="item in notificationsList"
                        :key="item.id"
                        class="flex flex-col gap-1 p-4 text-left transition duration-150 hover:bg-neutral-50 dark:hover:bg-neutral-800/50"
                    >
                        <div class="flex items-start justify-between gap-2">
                            <span
                                class="text-xs font-semibold tracking-wider text-neutral-500 uppercase"
                            >
                                {{ item.ticket_number || 'ALERT' }}
                            </span>

                            <button
                                @click="handleMarkAsRead(item.id)"
                                class="text-[10px] font-medium text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300"
                            >
                                Dismiss
                            </button>
                        </div>

                        <Link
                            v-if="item.ticket_id"
                            :href="showTicketRoute(item.ticket_id)"
                            @click="handleMarkAsRead(item.id)"
                            class="line-clamp-2 text-xs font-medium text-neutral-800 transition hover:text-primary dark:text-neutral-200"
                        >
                            {{ item.message }}
                        </Link>

                        <span
                            v-else
                            class="line-clamp-2 text-xs font-medium text-neutral-800 dark:text-neutral-200"
                        >
                            {{ item.message }}
                        </span>

                        <span class="mt-1 text-[10px] text-neutral-400">
                            {{
                                new Date(item.created_at).toLocaleTimeString(
                                    [],
                                    { hour: '2-digit', minute: '2-digit' },
                                )
                            }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="border-t border-neutral-100 dark:border-neutral-800">
                <Link
                    :href="notificationsIndexRoute()"
                    class="block w-full bg-neutral-50/50 py-2.5 text-center text-xs font-medium text-neutral-600 hover:text-neutral-900 dark:bg-neutral-900/50 dark:text-neutral-400 dark:hover:text-neutral-200"
                >
                    View all notifications
                </Link>
            </div>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
