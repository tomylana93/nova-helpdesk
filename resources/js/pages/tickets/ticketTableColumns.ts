import { Link, usePage } from '@inertiajs/vue3';
import type { ColumnDef } from '@tanstack/vue-table';
import { Eye, MoreHorizontal, Pencil } from 'lucide-vue-next';
import { h } from 'vue';
import DataTableColumnHeader from '@/components/datatable/DataTableColumnHeader.vue';
import TextLink from '@/components/TextLink.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { useTrans } from '@/composables/useTrans';
import { edit, show } from '@/routes/tickets';
import type { SharedPageProps, TicketSlaTarget, TicketTableRow } from '@/types';

const { trans } = useTrans();
const page = usePage<SharedPageProps>();

function slaStateClass(state: TicketSlaTarget['state']): string {
    if (state === 'overdue') {
        return 'text-destructive';
    }

    if (state === 'due_soon') {
        return 'text-amber-600 dark:text-amber-400';
    }

    if (state === 'no_sla' || state === 'completed') {
        return 'text-muted-foreground';
    }

    return 'text-foreground';
}

function slaTitle(target: TicketSlaTarget): string | undefined {
    return target.dueAt ? new Date(target.dueAt).toLocaleString() : undefined;
}

function renderSlaTarget(target: TicketSlaTarget) {
    return h(
        'div',
        {
            class: 'flex items-center justify-between gap-3 text-xs leading-5',
            title: slaTitle(target),
        },
        [
            h('span', { class: 'text-muted-foreground' }, target.label),
            h(
                'span',
                {
                    class: [
                        'max-w-28 truncate text-right font-medium',
                        slaStateClass(target.state),
                    ].join(' '),
                },
                target.statusLabel,
            ),
        ],
    );
}

export function ticketTableColumns(): ColumnDef<TicketTableRow>[] {
    const canUpdate = page.props.auth.abilities.update_tickets;

    return [
        {
            id: 'select',
            enableHiding: false,
            enableSorting: false,
            header: ({ table }) =>
                h(Checkbox, {
                    modelValue: table.getIsAllPageRowsSelected()
                        ? true
                        : table.getIsSomePageRowsSelected()
                          ? 'indeterminate'
                          : false,
                    'onUpdate:modelValue': (value: boolean | 'indeterminate') =>
                        table.toggleAllPageRowsSelected(!!value),
                }),
            cell: ({ row }) =>
                h(Checkbox, {
                    modelValue: row.getIsSelected(),
                    disabled: !row.getCanSelect(),
                    'onUpdate:modelValue': (value: boolean | 'indeterminate') =>
                        row.toggleSelected(!!value),
                }),
        },
        {
            accessorKey: 'ticketNumber',
            meta: { label: trans('helpdesk.ticket.label.ticket_number') },
            header: ({ column }) =>
                h(DataTableColumnHeader, {
                    column,
                    title: trans('helpdesk.ticket.label.ticket_number'),
                }),
            cell: ({ row }) => {
                const ticket = row.original;

                return h(
                    TextLink,
                    { href: show(ticket.id), prefetch: true },
                    () => ticket.ticketNumber,
                );
            },
        },
        {
            accessorKey: 'subject',
            meta: { label: trans('helpdesk.ticket.label.subject') },
            header: ({ column }) =>
                h(DataTableColumnHeader, {
                    column,
                    title: trans('helpdesk.ticket.label.subject'),
                }),
            cell: ({ row }) => {
                const ticket = row.original;

                return canUpdate
                    ? h(
                          TextLink,
                          { href: edit(ticket.id), prefetch: true },
                          () => ticket.subject,
                      )
                    : h('span', { class: 'font-medium' }, ticket.subject);
            },
        },
        {
            accessorKey: 'type',
            meta: { label: trans('helpdesk.ticket.label.type') },
            header: ({ column }) =>
                h(DataTableColumnHeader, {
                    column,
                    title: trans('helpdesk.ticket.label.type'),
                }),
            cell: ({ row }) =>
                h(
                    Badge,
                    { variant: 'outline', class: 'font-normal' },
                    () => row.original.typeLabel,
                ),
        },
        {
            accessorKey: 'status',
            meta: { label: trans('helpdesk.ticket.label.status') },
            header: ({ column }) =>
                h(DataTableColumnHeader, {
                    column,
                    title: trans('helpdesk.ticket.label.status'),
                }),
            cell: ({ row }) =>
                h(
                    Badge,
                    {
                        variant: row.original.statusVariant as 'default',
                        class: 'font-normal',
                    },
                    () => row.original.statusLabel,
                ),
        },
        {
            accessorKey: 'priority',
            meta: { label: trans('helpdesk.ticket.label.priority') },
            header: ({ column }) =>
                h(DataTableColumnHeader, {
                    column,
                    title: trans('helpdesk.ticket.label.priority'),
                }),
            cell: ({ row }) =>
                h(
                    Badge,
                    {
                        variant: row.original.priorityVariant as 'default',
                        class: 'font-normal capitalize',
                    },
                    () => row.original.priorityLabel,
                ),
        },
        {
            accessorKey: 'sla',
            meta: { label: 'SLA' },
            enableSorting: false,
            header: ({ column }) =>
                h(DataTableColumnHeader, {
                    column,
                    title: 'SLA',
                }),
            cell: ({ row }) =>
                h('div', { class: 'min-w-44 space-y-0.5' }, [
                    renderSlaTarget(row.original.sla.firstResponse),
                    renderSlaTarget(row.original.sla.resolution),
                ]),
        },
        {
            accessorKey: 'requesterName',
            meta: { label: trans('helpdesk.ticket.label.requester') },
            enableSorting: false,
            header: ({ column }) =>
                h(DataTableColumnHeader, {
                    column,
                    title: trans('helpdesk.ticket.label.requester'),
                }),
            cell: ({ row }) => h('span', row.original.requesterName ?? '-'),
        },
        {
            accessorKey: 'assigneeName',
            meta: { label: trans('helpdesk.ticket.label.assignee') },
            enableSorting: false,
            header: ({ column }) =>
                h(DataTableColumnHeader, {
                    column,
                    title: trans('helpdesk.ticket.label.assignee'),
                }),
            cell: ({ row }) =>
                h(
                    'span',
                    { class: 'text-muted-foreground' },
                    row.original.assigneeName ?? '—',
                ),
        },
        {
            id: 'actions',
            enableHiding: false,
            enableSorting: false,
            meta: { label: trans('user.label.actions') },
            cell: ({ row }) =>
                h(
                    DropdownMenu,
                    {},
                    {
                        default: () => [
                            h(DropdownMenuTrigger, { asChild: true }, () =>
                                h(
                                    Button,
                                    {
                                        variant: 'ghost',
                                        size: 'icon-sm',
                                        class: 'text-muted-foreground hover:text-foreground',
                                    },
                                    () => [
                                        h(MoreHorizontal, { class: 'size-4' }),
                                        h(
                                            'span',
                                            { class: 'sr-only' },
                                            trans('user.label.actions'),
                                        ),
                                    ],
                                ),
                            ),
                            h(
                                DropdownMenuContent,
                                { align: 'end', class: 'w-40' },
                                () => [
                                    h(DropdownMenuItem, { asChild: true }, () =>
                                        h(
                                            Link,
                                            {
                                                href: show(row.original.id),
                                                prefetch: true,
                                                class: 'flex w-full items-center gap-2',
                                            },
                                            () => [
                                                h(Eye, { class: 'size-4' }),
                                                trans(
                                                    'admin.master_data.user.action.view',
                                                ),
                                            ],
                                        ),
                                    ),
                                    ...(canUpdate
                                        ? [
                                              h(
                                                  DropdownMenuItem,
                                                  { asChild: true },
                                                  () =>
                                                      h(
                                                          Link,
                                                          {
                                                              href: edit(
                                                                  row.original
                                                                      .id,
                                                              ),
                                                              prefetch: true,
                                                              class: 'flex w-full items-center gap-2',
                                                          },
                                                          () => [
                                                              h(Pencil, {
                                                                  class: 'size-4',
                                                              }),
                                                              trans(
                                                                  'user.action.edit',
                                                              ),
                                                          ],
                                                      ),
                                              ),
                                          ]
                                        : []),
                                ],
                            ),
                        ],
                    },
                ),
        },
    ];
}
