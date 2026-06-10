<script setup lang="ts">
import { Head, Link, setLayoutProps, useForm } from '@inertiajs/vue3';
import { store } from '@/actions/App/Http/Controllers/Admin/MasterData/DepartmentController';
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
import { create, index } from '@/routes/admin/master-data/departments';
import type { SelectOption } from '@/types';

type Props = {
    statusOptions: SelectOption[];
    branchOptions: SelectOption[];
};

type CreateDepartmentFormData = {
    branch_id: string;
    code: string;
    name: string;
    status: string;
};

const props = defineProps<Props>();

defineOptions({ inheritAttrs: false });

const { trans } = useTrans();

const form = useForm<CreateDepartmentFormData>({
    branch_id: '',
    code: '',
    name: '',
    status: '',
});

function submit(): void {
    form.submit(store());
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
            title: trans('admin.master_data.department.index.title'),
            href: index(),
        },
        {
            title: trans('admin.master_data.department.create.title'),
            href: create(),
        },
    ],
});
</script>

<template>
    <Head :title="trans('admin.master_data.department.create.title')" />

    <PageWrapper
        :title="trans('admin.master_data.department.create.heading')"
        :description="trans('admin.master_data.department.create.description')"
    >
        <form class="flex flex-col gap-6" @submit.prevent="submit">
            <div class="grid gap-2">
                <Label for="branch_id">{{
                    trans('admin.master_data.department.label.branch')
                }}</Label>
                <Select
                    id="branch_id"
                    v-model="form.branch_id"
                    name="branch_id"
                    :aria-invalid="!!form.errors.branch_id"
                >
                    <SelectTrigger class="w-full">
                        <SelectValue
                            :placeholder="
                                trans(
                                    'admin.master_data.department.placeholder.branch',
                                )
                            "
                        />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="option in props.branchOptions"
                            :key="option.value"
                            :value="option.value"
                        >
                            {{ option.label }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <InputError :message="form.errors.branch_id" />
            </div>
            <div class="grid gap-2">
                <Label for="code">{{
                    trans('admin.master_data.department.label.code')
                }}</Label>
                <Input
                    id="code"
                    name="code"
                    v-model="form.code"
                    :placeholder="
                        trans('admin.master_data.department.placeholder.code')
                    "
                />
                <InputError :message="form.errors.code" />
            </div>
            <div class="grid gap-2">
                <Label for="name">{{
                    trans('admin.master_data.department.label.name')
                }}</Label>
                <Input
                    id="name"
                    name="name"
                    v-model="form.name"
                    :placeholder="
                        trans('admin.master_data.department.placeholder.name')
                    "
                />
                <InputError :message="form.errors.name" />
            </div>
            <div class="grid gap-2">
                <Label for="status">{{
                    trans('admin.master_data.department.label.status')
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
                                    'admin.master_data.department.placeholder.status',
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
                    {{ trans('admin.master_data.department.action.create') }}
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
                    {{ trans('admin.master_data.department.action.back') }}
                </Button>
            </div>
        </form>
    </PageWrapper>
</template>
