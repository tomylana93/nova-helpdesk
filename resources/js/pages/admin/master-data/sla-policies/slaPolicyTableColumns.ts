import { Link, usePage } from '@inertiajs/vue3';
import { Eye, MoreHorizontal, Pencil } from '@lucide/vue';
import type { ColumnDef } from '@tanstack/vue-table';
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
import { edit, show } from '@/routes/admin/master-data/sla-policies';
import type { SharedPageProps, SlaPolicyTableRow } from '@/types';

const { trans } = useTrans();
const page = usePage<SharedPageProps>();

export function slaPolicyTableColumns(): ColumnDef<SlaPolicyTableRow>[] {
    const canUpdate = page.props.auth.abilities.manage_sla_policies;

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
            accessorKey: 'name',
            meta: {
                label: trans('admin.master_data.sla_policy.label.name'),
            },
            header: ({ column }) =>
                h(DataTableColumnHeader, {
                    column,
                    title: trans('admin.master_data.sla_policy.label.name'),
                }),
            cell: ({ row }) =>
                canUpdate
                    ? h(
                          TextLink,
                          { href: edit(row.original.id), prefetch: true },
                          () => row.original.name,
                      )
                    : h('span', { class: 'font-medium' }, row.original.name),
        },
        {
            accessorKey: 'ticketTypeLabel',
            enableSorting: false,
            meta: {
                label: trans('admin.master_data.sla_policy.label.ticket_type'),
            },
            header: ({ column }) =>
                h(DataTableColumnHeader, {
                    column,
                    title: trans(
                        'admin.master_data.sla_policy.label.ticket_type',
                    ),
                }),
            cell: ({ row }) => h('span', row.original.ticketTypeLabel),
        },
        {
            accessorKey: 'priority',
            meta: {
                label: trans('admin.master_data.sla_policy.label.priority'),
            },
            header: ({ column }) =>
                h(DataTableColumnHeader, {
                    column,
                    title: trans('admin.master_data.sla_policy.label.priority'),
                }),
            cell: ({ row }) =>
                h(
                    Badge,
                    { variant: 'outline', class: 'font-normal capitalize' },
                    () => row.original.priorityLabel,
                ),
        },
        {
            accessorKey: 'firstResponseTargetMinutes',
            enableSorting: false,
            meta: {
                label: trans(
                    'admin.master_data.sla_policy.label.first_response',
                ),
            },
            header: ({ column }) =>
                h(DataTableColumnHeader, {
                    column,
                    title: trans(
                        'admin.master_data.sla_policy.label.first_response',
                    ),
                }),
            cell: ({ row }) =>
                h('span', `${row.original.firstResponseTargetMinutes} min`),
        },
        {
            accessorKey: 'resolutionTargetMinutes',
            enableSorting: false,
            meta: {
                label: trans('admin.master_data.sla_policy.label.resolution'),
            },
            header: ({ column }) =>
                h(DataTableColumnHeader, {
                    column,
                    title: trans(
                        'admin.master_data.sla_policy.label.resolution',
                    ),
                }),
            cell: ({ row }) =>
                h('span', `${row.original.resolutionTargetMinutes} min`),
        },
        {
            accessorKey: 'isActive',
            meta: {
                label: trans('admin.master_data.sla_policy.label.is_active'),
            },
            header: ({ column }) =>
                h(DataTableColumnHeader, {
                    column,
                    title: trans(
                        'admin.master_data.sla_policy.label.is_active',
                    ),
                }),
            cell: ({ row }) =>
                h(
                    Badge,
                    {
                        variant: row.original.isActive
                            ? 'default'
                            : 'destructive',
                        class: 'font-normal',
                    },
                    () => (row.original.isActive ? 'Active' : 'Inactive'),
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
