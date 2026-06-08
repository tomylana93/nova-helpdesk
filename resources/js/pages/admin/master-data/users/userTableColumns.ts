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
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import UserAvatar from '@/components/UserAvatar.vue';
import UserStatusBadge from '@/components/UserStatusBadge.vue';
import { useTrans } from '@/composables/useTrans';
import { edit, show } from '@/routes/admin/master-data/users';
import type { SharedPageProps, UserTableRow } from '@/types';

const { trans } = useTrans();
const page = usePage<SharedPageProps>();

export function userTableColumns(): ColumnDef<UserTableRow>[] {
    const canUpdate = page.props.auth.abilities.update_users;

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
            meta: { label: trans('user.label.name') },
            header: ({ column }) =>
                h(DataTableColumnHeader, {
                    column,
                    title: trans('user.label.name'),
                }),
            cell: ({ row }) => {
                const user = row.original;

                return h('div', { class: 'flex min-w-0 items-center gap-3' }, [
                    h(UserAvatar, {
                        name: user.name,
                    }),
                    h('div', { class: 'min-w-0' }, [
                        canUpdate
                            ? h(
                                  TextLink,
                                  {
                                      href: edit(user.id),
                                      prefetch: true,
                                  },
                                  () => user.name,
                              )
                            : h('p', { class: 'font-medium' }, user.name),
                        h(
                            'p',
                            {
                                class: 'truncate text-sm text-muted-foreground',
                            },
                            user.email,
                        ),
                    ]),
                ]);
            },
        },
        {
            accessorKey: 'role',
            enableSorting: false,
            meta: { label: trans('user.label.role') },
            header: ({ column }) =>
                h(DataTableColumnHeader, {
                    column,
                    title: trans('user.label.role'),
                }),
            cell: ({ row }) =>
                row.original.roleLabel === null
                    ? h('span', { class: 'text-sm text-muted-foreground' }, '-')
                    : h(
                          Badge,
                          {
                              variant: 'outline',
                              class: 'font-normal',
                          },
                          () => row.original.roleLabel,
                      ),
        },
        {
            accessorKey: 'status',
            meta: { label: trans('user.label.status') },
            header: ({ column }) =>
                h(DataTableColumnHeader, {
                    column,
                    title: trans('user.label.status'),
                }),
            cell: ({ row }) =>
                h(UserStatusBadge, {
                    status: row.original.status,
                    label: row.original.statusLabel,
                }),
        },
        {
            id: 'actions',
            cell: ({ row }) => {
                if (!canUpdate) {
                    return null;
                }

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
                                () => {
                                    const statusActionItems =
                                        buildStatusActionItems();

                                    return [
                                        h(
                                            DropdownMenuItem,
                                            { asChild: true },
                                            () =>
                                                h(
                                                    Link,
                                                    {
                                                        href: show(
                                                            row.original.id,
                                                        ),
                                                        prefetch: true,
                                                        class: 'flex w-full items-center gap-2',
                                                    },
                                                    () => [
                                                        h(Eye, {
                                                            class: 'size-4',
                                                        }),
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
                                                                      row
                                                                          .original
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
                                        ...(statusActionItems.length > 0
                                            ? [
                                                  h(DropdownMenuSeparator),
                                                  ...statusActionItems,
                                              ]
                                            : []),
                                    ];
                                },
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

function buildStatusActionItems(): ReturnType<typeof h>[] {
    return [];
}
