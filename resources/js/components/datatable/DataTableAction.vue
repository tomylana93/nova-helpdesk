<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import { Eye, MoreHorizontal, Pen, Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';

import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

interface Props {
    routes: {
        show?: (id: string) => { url: string };
        edit?: (id: string) => { url: string };
        destroy?: (id: string) => { url: string };
    };
    row: any;
}

const props = defineProps<Props>();

const isDialogOpen = ref(false);
const isDeleting = ref(false);
const isDropdownOpen = ref(false);

const hasAnyAction = computed(() =>
    Boolean(props.routes.show || props.routes.edit || props.routes.destroy),
);

const handleShow = () => {
    if (props.routes.show) {
        router.get(props.routes.show(props.row.original.id).url);
    }
    isDropdownOpen.value = false;
};

const handleEdit = () => {
    if (props.routes.edit) {
        router.get(props.routes.edit(props.row.original.id).url);
    }
    isDropdownOpen.value = false;
};

const handleDelete = () => {
    if (props.routes.destroy) {
        isDeleting.value = true;
        router.delete(props.routes.destroy(props.row.original.id).url, {
            preserveScroll: true,
            onFinish: () => {
                isDialogOpen.value = false;
                isDeleting.value = false;
                isDropdownOpen.value = false;
            },
        });
    }
};
</script>

<template>
    <template v-if="hasAnyAction">
        <DropdownMenu v-model:open="isDropdownOpen">
            <DropdownMenuTrigger as-child>
                <Button variant="ghost" class="h-8 w-8 cursor-pointer">
                    <MoreHorizontal class="h-4 w-4" />
                </Button>
            </DropdownMenuTrigger>

            <DropdownMenuContent align="end">
                <DropdownMenuItem
                    v-if="routes.show"
                    @select.prevent="handleShow"
                >
                    <Eye class="mr-2 h-4 w-4" />
                    {{ trans('ui.datatable_actions.show') }}
                </DropdownMenuItem>
                <DropdownMenuItem
                    v-if="routes.edit"
                    @select.prevent="handleEdit"
                >
                    <Pen class="mr-2 h-4 w-4" />
                    {{ trans('ui.datatable_actions.edit') }}
                </DropdownMenuItem>
                <DropdownMenuSeparator v-if="routes.destroy" />
                <DropdownMenuItem
                    v-if="routes.destroy"
                    class="cursor-pointer text-destructive focus:bg-destructive/10 focus:text-destructive"
                    @select.prevent="
                        () => ((isDialogOpen = true), (isDropdownOpen = false))
                    "
                >
                    <Trash2 class="mr-2 h-4 w-4" />
                    {{ trans('ui.datatable_actions.delete') }}
                </DropdownMenuItem>
            </DropdownMenuContent>
        </DropdownMenu>

        <AlertDialog v-model:open="isDialogOpen">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>
                        {{ trans('ui.datatable_actions.confirm_delete_title') }}
                    </AlertDialogTitle>
                    <AlertDialogDescription>
                        {{
                            trans(
                                'ui.datatable_actions.confirm_delete_description',
                            )
                        }}
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel>
                        {{ trans('ui.datatable_actions.cancel') }}
                    </AlertDialogCancel>
                    <AlertDialogAction
                        :disabled="isDeleting"
                        @click="handleDelete"
                    >
                        {{
                            isDeleting
                                ? trans('ui.datatable_actions.loading')
                                : trans('ui.datatable_actions.confirm')
                        }}
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </template>
</template>
