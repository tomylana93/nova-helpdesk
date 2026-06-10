<script setup lang="ts">
import { Head, Link, setLayoutProps, useForm } from '@inertiajs/vue3';
import { update } from '@/actions/App/Http/Controllers/Admin/MasterData/TicketCategoryController';
import InputError from '@/components/InputError.vue';
import PageWrapper from '@/components/PageWrapper.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Spinner } from '@/components/ui/spinner';
import { useTrans } from '@/composables/useTrans';
import { dashboard } from '@/routes';
import { index as adminMasterDataIndex } from '@/routes/admin/master-data';
import { edit, index } from '@/routes/admin/master-data/ticket-categories';
import type { SelectOption } from '@/types';

type Category = {
    id: string;
    parent_id: string | null;
    name: string;
    description: string | null;
    status: 'active' | 'inactive';
};

type Props = {
    category: Category;
    statusOptions: SelectOption[];
    parentOptions: SelectOption[];
};

type EditCategoryFormData = {
    parent_id: string | null;
    name: string;
    description: string;
    status: Category['status'];
};

const props = defineProps<Props>();

defineOptions({ inheritAttrs: false });

const { trans } = useTrans();

const form = useForm<EditCategoryFormData>({
    parent_id: props.category.parent_id,
    name: props.category.name,
    description: props.category.description ?? '',
    status: props.category.status,
});

function submit(): void {
    form.submit(update(props.category.id));
}

function reset(): void {
    form.resetAndClearErrors();
}

setLayoutProps({
    breadcrumbs: [
        {
            title: 'Dashboard',
            href: dashboard(),
        },
        {
            title: trans('admin.master_data.title'),
            href: adminMasterDataIndex(),
        },
        {
            title: trans('admin.master_data.ticket_category.index.title'),
            href: index(),
        },
        {
            title: trans('admin.master_data.ticket_category.edit.title'),
            href: edit(props.category.id),
        },
    ],
});
</script>

<template>
    <Head :title="trans('admin.master_data.ticket_category.edit.title')" />

    <PageWrapper
        :title="trans('admin.master_data.ticket_category.edit.heading')"
        :description="
            trans('admin.master_data.ticket_category.edit.description')
        "
    >
        <form class="flex flex-col gap-6" @submit.prevent="submit">
            <div class="grid gap-2">
                <Label for="parent_id">{{
                    trans('admin.master_data.ticket_category.label.parent')
                }}</Label>
                <Select
                    id="parent_id"
                    v-model="form.parent_id"
                    name="parent_id"
                    :aria-invalid="!!form.errors.parent_id"
                >
                    <SelectTrigger class="w-full">
                        <SelectValue
                            :placeholder="
                                trans(
                                    'admin.master_data.ticket_category.placeholder.parent',
                                )
                            "
                        />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="option in props.parentOptions"
                            :key="option.value"
                            :value="option.value"
                        >
                            {{ option.label }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <InputError :message="form.errors.parent_id" />
            </div>
            <div class="grid gap-2">
                <Label for="name">{{
                    trans('admin.master_data.ticket_category.label.name')
                }}</Label>
                <Input
                    id="name"
                    name="name"
                    v-model="form.name"
                    autofocus
                    :placeholder="
                        trans(
                            'admin.master_data.ticket_category.placeholder.name',
                        )
                    "
                />
                <InputError :message="form.errors.name" />
            </div>
            <div class="grid gap-2">
                <Label for="description">{{
                    trans('admin.master_data.ticket_category.label.description')
                }}</Label>
                <Input
                    id="description"
                    name="description"
                    v-model="form.description"
                    :placeholder="
                        trans(
                            'admin.master_data.ticket_category.placeholder.description',
                        )
                    "
                />
                <InputError :message="form.errors.description" />
            </div>
            <div class="grid gap-2">
                <Label for="status">{{
                    trans('admin.master_data.ticket_category.label.status')
                }}</Label>
                <Select
                    id="status"
                    v-model="form.status"
                    name="status"
                    :aria-invalid="!!form.errors.status"
                >
                    <SelectTrigger class="w-full">
                        <SelectValue
                            :placeholder="
                                trans(
                                    'admin.master_data.ticket_category.placeholder.status',
                                )
                            "
                        />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="option in props.statusOptions"
                            :key="option.value"
                            :value="option.value"
                        >
                            {{ option.label }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <InputError :message="form.errors.status" />
            </div>
            <div
                class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center"
            >
                <Button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full sm:w-auto"
                >
                    <Spinner v-if="form.processing" />
                    {{
                        trans('admin.master_data.ticket_category.action.update')
                    }}
                </Button>
                <Button
                    type="button"
                    variant="secondary"
                    :disabled="form.processing"
                    class="w-full sm:w-auto"
                    @click="reset"
                >
                    {{ trans('admin.master_data.user.action.reset') }}
                </Button>
                <Button
                    :as="Link"
                    variant="outline"
                    :href="index()"
                    :disabled="form.processing"
                    class="w-full sm:w-auto"
                >
                    {{ trans('admin.master_data.ticket_category.action.back') }}
                </Button>
            </div>
        </form>
    </PageWrapper>
</template>
