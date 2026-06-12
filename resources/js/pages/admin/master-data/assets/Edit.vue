<script setup lang="ts">
import { Head, Link, setLayoutProps, useForm } from '@inertiajs/vue3';
import { update } from '@/actions/App/Http/Controllers/Admin/MasterData/AssetController';
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
import { edit, index } from '@/routes/admin/master-data/assets';
import type { SelectOption } from '@/types';

type Asset = {
    id: string;
    asset_tag: string;
    name: string;
    category: string;
    status: string;
    branch_id: string | null;
    user_id: string | null;
};

type Props = {
    asset: Asset;
    categoryOptions: SelectOption[];
    statusOptions: SelectOption[];
    branchOptions: SelectOption[];
    userOptions: SelectOption[];
};

type EditAssetFormData = {
    asset_tag: string;
    name: string;
    category: string;
    status: string;
    branch_id: string | null;
    user_id: string | null;
};

const props = defineProps<Props>();

defineOptions({ inheritAttrs: false });

const { trans } = useTrans();

const form = useForm<EditAssetFormData>({
    asset_tag: props.asset.asset_tag,
    name: props.asset.name,
    category: props.asset.category,
    status: props.asset.status,
    branch_id: props.asset.branch_id || '',
    user_id: props.asset.user_id || '',
});

function submit(): void {
    // Convert empty string back to null for Laravel validation
    const payload = {
        ...form.data(),
        branch_id: form.branch_id === '' ? null : form.branch_id,
        user_id: form.user_id === '' ? null : form.user_id,
    };
    form.transform(() => payload).submit(update(props.asset.id));
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
            title: trans('admin.master_data.asset.index.title'),
            href: index(),
        },
        {
            title: trans('admin.master_data.asset.edit.title'),
            href: edit(props.asset.id),
        },
    ],
});
</script>

<template>
    <Head :title="trans('admin.master_data.asset.edit.title')" />

    <PageWrapper
        :title="trans('admin.master_data.asset.edit.heading')"
        :description="trans('admin.master_data.asset.edit.description')"
    >
        <form class="flex max-w-2xl flex-col gap-6" @submit.prevent="submit">
            <div class="grid gap-2">
                <Label for="asset_tag">{{
                    trans('admin.master_data.asset.label.asset_tag')
                }}</Label>
                <Input
                    id="asset_tag"
                    name="asset_tag"
                    v-model="form.asset_tag"
                    autofocus
                    :placeholder="
                        trans('admin.master_data.asset.placeholder.asset_tag')
                    "
                />
                <InputError :message="form.errors.asset_tag" />
            </div>

            <div class="grid gap-2">
                <Label for="name">{{
                    trans('admin.master_data.asset.label.name')
                }}</Label>
                <Input
                    id="name"
                    name="name"
                    v-model="form.name"
                    :placeholder="
                        trans('admin.master_data.asset.placeholder.name')
                    "
                />
                <InputError :message="form.errors.name" />
            </div>

            <div class="grid gap-2">
                <Label for="category">{{
                    trans('admin.master_data.asset.label.category')
                }}</Label>
                <Select
                    id="category"
                    v-model="form.category"
                    name="category"
                    :aria-invalid="!!form.errors.category"
                >
                    <SelectTrigger class="w-full">
                        <SelectValue
                            :placeholder="
                                trans(
                                    'admin.master_data.asset.placeholder.category',
                                )
                            "
                        />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="option in props.categoryOptions"
                            :key="option.value"
                            :value="option.value"
                        >
                            {{ option.label }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <InputError :message="form.errors.category" />
            </div>

            <div class="grid gap-2">
                <Label for="status">{{
                    trans('admin.master_data.asset.label.status')
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
                                    'admin.master_data.asset.placeholder.status',
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

            <div class="grid gap-2">
                <Label for="branch_id">{{
                    trans('admin.master_data.asset.label.branch')
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
                                    'admin.master_data.asset.placeholder.branch',
                                )
                            "
                        />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="">
                            {{
                                trans(
                                    'admin.master_data.asset.placeholder.branch_unassigned',
                                )
                            }}
                        </SelectItem>
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
                <Label for="user_id">{{
                    trans('admin.master_data.asset.label.user')
                }}</Label>
                <Select
                    id="user_id"
                    v-model="form.user_id"
                    name="user_id"
                    :aria-invalid="!!form.errors.user_id"
                >
                    <SelectTrigger class="w-full">
                        <SelectValue
                            :placeholder="
                                trans(
                                    'admin.master_data.asset.placeholder.user',
                                )
                            "
                        />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="">
                            {{
                                trans(
                                    'admin.master_data.asset.placeholder.user_unassigned',
                                )
                            }}
                        </SelectItem>
                        <SelectItem
                            v-for="option in props.userOptions"
                            :key="option.value"
                            :value="option.value"
                        >
                            {{ option.label }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <InputError :message="form.errors.user_id" />
            </div>

            <div
                class="mt-4 flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center"
            >
                <Button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full sm:w-auto"
                >
                    <Spinner v-if="form.processing" />
                    {{ trans('admin.master_data.asset.action.update') }}
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
                    {{ trans('admin.master_data.asset.action.back') }}
                </Button>
            </div>
        </form>
    </PageWrapper>
</template>
