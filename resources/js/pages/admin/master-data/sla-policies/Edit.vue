<script setup lang="ts">
import { Head, Link, setLayoutProps, useForm } from '@inertiajs/vue3';
import { update } from '@/actions/App/Http/Controllers/Admin/MasterData/SlaPolicyController';
import InputError from '@/components/InputError.vue';
import PageWrapper from '@/components/PageWrapper.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
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
import { edit, index } from '@/routes/admin/master-data/sla-policies';
import type { SelectOption, SlaPolicy } from '@/types';

type Props = {
    slaPolicy: SlaPolicy;
    typeOptions: SelectOption[];
    priorityOptions: SelectOption[];
    queueOptions: SelectOption[];
};

type FormData = {
    name: string;
    ticket_type: string;
    priority: string;
    queue_id: string;
    first_response_target_minutes: number | string;
    resolution_target_minutes: number | string;
    is_active: boolean;
};

const props = defineProps<Props>();
defineOptions({ inheritAttrs: false });

const { trans } = useTrans();

const form = useForm<FormData>({
    name: props.slaPolicy.name,
    ticket_type: props.slaPolicy.ticket_type ?? '',
    priority: props.slaPolicy.priority,
    queue_id: props.slaPolicy.queue_id ?? '',
    first_response_target_minutes:
        props.slaPolicy.first_response_target_minutes,
    resolution_target_minutes: props.slaPolicy.resolution_target_minutes,
    is_active: props.slaPolicy.is_active,
});

function submit(): void {
    form.submit(update(props.slaPolicy.id));
}

setLayoutProps({
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        {
            title: trans('admin.master_data.title'),
            href: adminMasterDataIndex(),
        },
        {
            title: trans('admin.master_data.sla_policy.index.title'),
            href: index(),
        },
        {
            title: trans('admin.master_data.sla_policy.edit.title'),
            href: edit(props.slaPolicy.id),
        },
    ],
});
</script>

<template>
    <Head :title="trans('admin.master_data.sla_policy.edit.title')" />

    <PageWrapper
        :title="trans('admin.master_data.sla_policy.edit.heading')"
        :description="trans('admin.master_data.sla_policy.edit.description')"
    >
        <form class="flex flex-col gap-6" @submit.prevent="submit">
            <div class="grid gap-2">
                <Label for="name">{{
                    trans('admin.master_data.sla_policy.label.name')
                }}</Label>
                <Input
                    id="name"
                    v-model="form.name"
                    autofocus
                    :placeholder="
                        trans('admin.master_data.sla_policy.placeholder.name')
                    "
                />
                <InputError :message="form.errors.name" />
            </div>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div class="grid gap-2">
                    <Label for="ticket_type">{{
                        trans('admin.master_data.sla_policy.label.ticket_type')
                    }}</Label>
                    <Select id="ticket_type" v-model="form.ticket_type">
                        <SelectTrigger class="w-full">
                            <SelectValue
                                :placeholder="
                                    trans(
                                        'admin.master_data.sla_policy.placeholder.ticket_type',
                                    )
                                "
                            />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="opt in props.typeOptions"
                                :key="opt.value"
                                :value="opt.value"
                            >
                                {{ opt.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.ticket_type" />
                </div>

                <div class="grid gap-2">
                    <Label for="priority">{{
                        trans('admin.master_data.sla_policy.label.priority')
                    }}</Label>
                    <Select
                        id="priority"
                        v-model="form.priority"
                        :aria-invalid="!!form.errors.priority"
                    >
                        <SelectTrigger class="w-full">
                            <SelectValue
                                :placeholder="
                                    trans(
                                        'admin.master_data.sla_policy.placeholder.priority',
                                    )
                                "
                            />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="opt in props.priorityOptions"
                                :key="opt.value"
                                :value="opt.value"
                            >
                                {{ opt.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.priority" />
                </div>

                <div class="grid gap-2">
                    <Label for="first_response_target_minutes">{{
                        trans(
                            'admin.master_data.sla_policy.label.first_response',
                        )
                    }}</Label>
                    <Input
                        id="first_response_target_minutes"
                        v-model="form.first_response_target_minutes"
                        type="number"
                        min="1"
                    />
                    <InputError
                        :message="form.errors.first_response_target_minutes"
                    />
                </div>

                <div class="grid gap-2">
                    <Label for="resolution_target_minutes">{{
                        trans('admin.master_data.sla_policy.label.resolution')
                    }}</Label>
                    <Input
                        id="resolution_target_minutes"
                        v-model="form.resolution_target_minutes"
                        type="number"
                        min="1"
                    />
                    <InputError
                        :message="form.errors.resolution_target_minutes"
                    />
                </div>

                <div class="grid gap-2">
                    <Label for="queue_id">{{
                        trans('admin.master_data.sla_policy.label.queue')
                    }}</Label>
                    <Select id="queue_id" v-model="form.queue_id">
                        <SelectTrigger class="w-full">
                            <SelectValue
                                :placeholder="
                                    trans(
                                        'admin.master_data.sla_policy.placeholder.queue',
                                    )
                                "
                            />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="opt in props.queueOptions"
                                :key="opt.value"
                                :value="opt.value"
                            >
                                {{ opt.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.queue_id" />
                </div>

                <div class="flex items-center gap-3 pt-6">
                    <Checkbox
                        id="is_active"
                        :checked="form.is_active"
                        @update:checked="
                            (v: boolean | 'indeterminate') =>
                                (form.is_active = v === true)
                        "
                    />
                    <Label for="is_active">{{
                        trans('admin.master_data.sla_policy.label.is_active')
                    }}</Label>
                    <InputError :message="form.errors.is_active" />
                </div>
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
                    {{ trans('admin.master_data.sla_policy.action.update') }}
                </Button>
                <Button
                    :as="Link"
                    variant="outline"
                    :href="index()"
                    :disabled="form.processing"
                    class="w-full sm:w-auto"
                >
                    {{ trans('admin.master_data.sla_policy.action.back') }}
                </Button>
            </div>
        </form>
    </PageWrapper>
</template>
