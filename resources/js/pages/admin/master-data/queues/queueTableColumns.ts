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
import { edit, show } from '@/routes/admin/master-data/queues';
import type { SharedPageProps, QueueTableRow } from '@/types';

const { trans } = useTrans();
const page = usePage<SharedPageProps>();

export function queueTableColumns(): ColumnDef<QueueTableRow>[] {
    const canUpdate = page.props.auth.abilities.manage_queues;

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
            meta: { label: trans('admin.master_data.queue.label.name') },
            header: ({ column }) =>
                h(DataTableColumnHeader, {
                    column,
                    title: trans('admin.master_data.queue.label.name'),
                }),
            cell: ({ row }) => {
                const queue = row.original;

                return canUpdate
                    ? h(
                          TextLink,
                          {
                              href: edit(queue.id),
                              prefetch: true,
                          },
                          () => queue.name,
                      )
                    : h('span', { class: 'font-medium' }, queue.name);
            },
        },
        {
            accessorKey: 'description',
            meta: { label: trans('admin.master_data.queue.label.description') },
            header: ({ column }) =>
                h(DataTableColumnHeader, {
                    column,
                    title: trans('admin.master_data.queue.label.description'),
                }),
            cell: ({ row }) => h('span', row.original.description ?? '-'),
        },
        {
            accessorKey: 'status',
            meta: { label: trans('admin.master_data.queue.label.status') },
            header: ({ column }) =>
                h(DataTableColumnHeader, {
                    column,
                    title: trans('admin.master_data.queue.label.status'),
                }),
            cell: ({ row }) => {
                const status = row.original.status;

                return h(
                    Badge,
                    {
                        variant:
                            status === 'active' ? 'default' : 'destructive',
                        class: 'font-normal capitalize',
                    },
                    () => row.original.statusLabel,
                );
            },
        },
        {
            id: 'actions',
            cell: ({ row }) => {
                return h(
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
                );
            },
            enableHiding: false,
            enableSorting: false,
            meta: { label: trans('user.label.actions') },
        },
    ];
}
