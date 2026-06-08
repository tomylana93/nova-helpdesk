<script setup lang="ts">
import { Head, Link, setLayoutProps, useForm } from '@inertiajs/vue3';
import { update } from '@/actions/App/Http/Controllers/Admin/MasterData/UserController';
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
import { edit, index } from '@/routes/admin/master-data/users';
import type { SelectOption, User, UserRoleName } from '@/types';

type Props = {
    user: User;
    userRoleOptions: SelectOption[];
    userStatusOptions: SelectOption[];
};

type EditUserFormData = {
    name: string;
    email: string;
    status: User['status'];
    role: UserRoleName | '';
};

const props = defineProps<Props>();

defineOptions({ inheritAttrs: false });

const { trans } = useTrans();

const form = useForm<EditUserFormData>({
    name: props.user.name,
    email: props.user.email,
    status: props.user.status,
    role: props.user.role ?? '',
});

function submit(): void {
    form.submit(update(props.user.id));
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
            title: trans('admin.master_data.user.edit.title'),
            href: edit(props.user.id),
        },
    ],
});
</script>

<template>
    <Head :title="trans('admin.master_data.user.edit.title')" />

    <PageWrapper
        :title="trans('admin.master_data.user.edit.heading')"
        :description="trans('admin.master_data.user.edit.description')"
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
                    :arials-invalid="!!form.errors.name"
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
                    :aria-invalid="!!form.errors.email"
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
                <Label for="email">{{
                    trans('admin.master_data.user.label.status')
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
                                    'admin.master_data.user.placeholder.status',
                                )
                            "
                        />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="option in props.userStatusOptions"
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
                    {{ trans('admin.master_data.user.action.update') }}
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
