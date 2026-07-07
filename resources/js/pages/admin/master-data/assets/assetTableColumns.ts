import { Link, usePage, router } from '@inertiajs/vue3';
import { Eye, MoreHorizontal, Pencil, Trash } from '@lucide/vue';
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
import { edit, show, destroy } from '@/routes/admin/master-data/assets';
import type { SharedPageProps, AssetTableRow } from '@/types';

const { trans } = useTrans();
const page = usePage<SharedPageProps>();

export function assetTableColumns(): ColumnDef<AssetTableRow>[] {
    const canManage = page.props.auth.abilities.manage_assets;

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
            accessorKey: 'assetTag',
            meta: { label: trans('admin.master_data.asset.label.asset_tag') },
            header: ({ column }) =>
                h(DataTableColumnHeader, {
                    column,
                    title: trans('admin.master_data.asset.label.asset_tag'),
                }),
            cell: ({ row }) => {
                const asset = row.original;

                return canManage
                    ? h(
                          TextLink,
                          {
                              href: edit(asset.id),
                              prefetch: true,
                          },
                          () => asset.assetTag,
                      )
                    : h('span', { class: 'font-medium' }, asset.assetTag);
            },
        },
        {
            accessorKey: 'name',
            meta: { label: trans('admin.master_data.asset.label.name') },
            header: ({ column }) =>
                h(DataTableColumnHeader, {
                    column,
                    title: trans('admin.master_data.asset.label.name'),
                }),
            cell: ({ row }) => h('span', row.original.name),
        },
        {
            accessorKey: 'category',
            meta: { label: trans('admin.master_data.asset.label.category') },
            header: ({ column }) =>
                h(DataTableColumnHeader, {
                    column,
                    title: trans('admin.master_data.asset.label.category'),
                }),
            cell: ({ row }) => h('span', row.original.categoryLabel),
        },
        {
            accessorKey: 'status',
            meta: { label: trans('admin.master_data.asset.label.status') },
            header: ({ column }) =>
                h(DataTableColumnHeader, {
                    column,
                    title: trans('admin.master_data.asset.label.status'),
                }),
            cell: ({ row }) => {
                const asset = row.original;

                return h(
                    Badge,
                    {
                        variant: asset.statusVariant as 'default',
                        class: 'font-normal capitalize',
                    },
                    () => asset.statusLabel,
                );
            },
        },
        {
            accessorKey: 'branchName',
            meta: { label: trans('admin.master_data.asset.label.branch') },
            header: ({ column }) =>
                h(DataTableColumnHeader, {
                    column,
                    title: trans('admin.master_data.asset.label.branch'),
                }),
            cell: ({ row }) => h('span', row.original.branchName || '-'),
        },
        {
            accessorKey: 'userName',
            meta: { label: trans('admin.master_data.asset.label.user') },
            header: ({ column }) =>
                h(DataTableColumnHeader, {
                    column,
                    title: trans('admin.master_data.asset.label.user'),
                }),
            cell: ({ row }) => h('span', row.original.userName || '-'),
        },
        {
            id: 'actions',
            cell: ({ row }) => {
                const asset = row.original;

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
                                                href: show(asset.id),
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
                                    ...(canManage
                                        ? [
                                              h(
                                                  DropdownMenuItem,
                                                  { asChild: true },
                                                  () =>
                                                      h(
                                                          Link,
                                                          {
                                                              href: edit(
                                                                  asset.id,
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
                                              h(
                                                  DropdownMenuItem,
                                                  {
                                                      onClick: () => {
                                                          if (
                                                              confirm(
                                                                  trans(
                                                                      'admin.master_data.asset.action.confirm_delete',
                                                                  ),
                                                              )
                                                          ) {
                                                              router.delete(
                                                                  destroy(
                                                                      asset.id,
                                                                  ),
                                                              );
                                                          }
                                                      },
                                                      class: 'flex w-full items-center gap-2 text-destructive focus:text-destructive focus:bg-destructive/10 cursor-pointer',
                                                  },
                                                  () => [
                                                      h(Trash, {
                                                          class: 'size-4',
                                                      }),
                                                      trans(
                                                          'admin.master_data.asset.action.delete',
                                                      ),
                                                  ],
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
