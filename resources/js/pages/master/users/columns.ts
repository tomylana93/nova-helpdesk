import type { ColumnDef } from "@tanstack/vue-table";
import { h } from "vue";

import { DataTableAction, DataTableColumnHeader } from "@/components/datatable";
import { Checkbox } from "@/components/ui/checkbox";
import { edit } from "@/routes/master/users";
import type { UserTableRow } from "@/types";

export const userColumns : ColumnDef<UserTableRow>[] = [
    {
        id: 'select',
        header: ({ table }) =>
            h(Checkbox, {
                modelValue: table.getIsAllPageRowsSelected(),
                'onUpdate:modelValue': (value: boolean | 'indeterminate') =>
                    table.toggleAllPageRowsSelected(!!value),
                ariaLabel: 'Select all',
            }),
        cell: ({ row }) =>
            h(Checkbox, {
                modelValue: row.getIsSelected(),
                'onUpdate:modelValue': (value: boolean | 'indeterminate') =>
                    row.toggleSelected(!!value),
                ariaLabel: 'Select row',
            }),
        enableSorting: false,
        enableHiding: false,
    },
    {
        accessorKey: 'name',
        meta: { label: 'user.label.name' },
        header: ({ column }) =>
            h(DataTableColumnHeader, {
                column,
                title: 'user.label.name',
            }),
    },
    {
        accessorKey: 'email',
        meta: { label: 'user.label.email' },
        header: ({ column }) =>
            h(DataTableColumnHeader, {
                column,
                title: 'user.label.email',
            }),
    },
    {
        accessorKey: 'status',
        meta: { label: 'user.label.status' },
        header: ({ column }) =>
            h(DataTableColumnHeader, {
                column,
                title: 'user.label.status',
            }),
    },
    {
        id: 'actions',
        enableHiding: false,
        cell: ({ row }) =>
            h(DataTableAction, {
                row,
                routes: {
                    edit:  (id: string) => edit(id)
                },
            }),
    },
];
