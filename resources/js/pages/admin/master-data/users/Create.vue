<script setup lang="ts">
import { Head, Link, setLayoutProps, useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import { store } from '@/actions/App/Http/Controllers/Admin/MasterData/UserController';
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
import { create, index } from '@/routes/admin/master-data/users';
import type { SelectOption, UserRoleName } from '@/types';

type Props = {
    userRoleOptions: SelectOption[];
    branchOptions: SelectOption[];
    departmentOptions: (SelectOption & { branch_id: string })[];
};

type CreateUserFormData = {
    name: string;
    email: string;
    role: UserRoleName | '';
    branch_id: string;
    department_id: string;
};

const props = defineProps<Props>();

defineOptions({ inheritAttrs: false });

const { trans } = useTrans();

const form = useForm<CreateUserFormData>({
    name: '',
    email: '',
    role: '',
    branch_id: '',
    department_id: '',
});

const filteredDepartmentOptions = computed(() => {
    if (!form.branch_id) {
        return [];
    }

    return props.departmentOptions.filter(
        (option) => option.branch_id === form.branch_id,
    );
});

watch(
    () => form.branch_id,
    () => {
        form.department_id = '';
    },
);

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
            title: trans('admin.master_data.user.index.title'),
            href: index(),
        },
        {
            title: trans('admin.master_data.user.create.title'),
            href: create(),
        },
    ],
});
</script>

<template>
    <Head :title="trans('admin.master_data.user.create.title')" />

    <PageWrapper
        :title="trans('admin.master_data.user.create.heading')"
        :description="trans('admin.master_data.user.create.description')"
    >
        <form class="flex flex-col gap-6" @submit.prevent="submit">
            <div class="grid gap-2">
                <Label for="name">{{
                    trans('admin.master_data.user.label.name')
                }}</Label>
                <Input
                    id="name"
                    name="name"
                    v-model="form.name"
                    autofocus
                    autocomplete="name"
                    :placeholder="
                        trans('admin.master_data.user.placeholder.name')
                    "
                />
                <InputError :message="form.errors.name" />
            </div>
            <div class="grid gap-2">
                <Label for="email">{{
                    trans('admin.master_data.user.label.email')
                }}</Label>
                <Input
                    id="email"
                    name="email"
                    v-model="form.email"
                    autocomplete="email"
                    :placeholder="
                        trans('admin.master_data.user.placeholder.email')
                    "
                />
                <InputError :message="form.errors.email" />
            </div>
            <div class="grid gap-2">
                <Label for="role">{{
                    trans('admin.master_data.user.label.role')
                }}</Label>
                <Select
                    id="role"
                    v-model="form.role"
                    name="role"
                    :aria-invalid="!!form.errors.role"
                >
                    <SelectTrigger class="w-full">
                        <SelectValue
                            :placeholder="
                                trans('admin.master_data.user.placeholder.role')
                            "
                        />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="option in props.userRoleOptions"
                            :key="option.value"
                            :value="option.value"
                        >
                            {{ option.label }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <InputError :message="form.errors.role" />
            </div>
            <div class="grid gap-2">
                <Label for="branch_id">{{
                    trans('admin.master_data.user.label.branch')
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
                                    'admin.master_data.user.placeholder.branch',
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
                <Label for="department_id">{{
                    trans('admin.master_data.user.label.department')
                }}</Label>
                <Select
                    id="department_id"
                    v-model="form.department_id"
                    name="department_id"
                    :disabled="!form.branch_id"
                    :aria-invalid="!!form.errors.department_id"
                >
                    <SelectTrigger class="w-full">
                        <SelectValue
                            :placeholder="
                                trans(
                                    'admin.master_data.user.placeholder.department',
                                )
                            "
                        />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="option in filteredDepartmentOptions"
                            :key="option.value"
                            :value="option.value"
                        >
                            {{ option.label }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <InputError :message="form.errors.department_id" />
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
                    {{ trans('admin.master_data.user.action.create') }}
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
                    {{ trans('admin.master_data.user.action.back') }}
                </Button>
            </div>
        </form>
    </PageWrapper>
</template>
