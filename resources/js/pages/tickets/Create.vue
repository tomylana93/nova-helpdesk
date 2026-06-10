<script setup lang="ts">
import { Head, Link, setLayoutProps, useForm } from '@inertiajs/vue3';
import { store } from '@/actions/App/Http/Controllers/Helpdesk/TicketController';
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
import { Textarea } from '@/components/ui/textarea';
import { useTrans } from '@/composables/useTrans';
import { dashboard } from '@/routes';
import { create, index } from '@/routes/tickets';
import type { SelectOption } from '@/types';

type DepartmentOption = SelectOption & { branch_id: string | null };

type Props = {
    typeOptions: SelectOption[];
    priorityOptions: SelectOption[];
    branchOptions: SelectOption[];
    departmentOptions: DepartmentOption[];
    queueOptions: SelectOption[];
    categoryOptions: SelectOption[];
};

type CreateTicketFormData = {
    type: string;
    subject: string;
    description: string;
    priority: string;
    branch_id: string;
    department_id: string;
    queue_id: string;
    category_id: string;
};

const props = defineProps<Props>();

defineOptions({ inheritAttrs: false });

const { trans } = useTrans();

const form = useForm<CreateTicketFormData>({
    type: '',
    subject: '',
    description: '',
    priority: '',
    branch_id: '',
    department_id: '',
    queue_id: '',
    category_id: '',
});

function submit(): void {
    form.submit(store());
}

function reset(): void {
    form.resetAndClearErrors();
}

setLayoutProps({
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: trans('helpdesk.ticket.index.title'), href: index() },
        { title: trans('helpdesk.ticket.create.title'), href: create() },
    ],
});
</script>

<template>
    <Head :title="trans('helpdesk.ticket.create.title')" />

    <PageWrapper
        :title="trans('helpdesk.ticket.create.heading')"
        :description="trans('helpdesk.ticket.create.description')"
    >
        <form class="flex flex-col gap-6" @submit.prevent="submit">
            <div class="grid gap-2">
                <Label for="type">{{
                    trans('helpdesk.ticket.label.type')
                }}</Label>
                <Select
                    id="type"
                    v-model="form.type"
                    name="type"
                    :aria-invalid="!!form.errors.type"
                >
                    <SelectTrigger class="w-full">
                        <SelectValue
                            :placeholder="
                                trans('helpdesk.ticket.placeholder.type')
                            "
                        />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="option in props.typeOptions"
                            :key="option.value"
                            :value="option.value"
                        >
                            {{ option.label }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <InputError :message="form.errors.type" />
            </div>

            <div class="grid gap-2">
                <Label for="subject">{{
                    trans('helpdesk.ticket.label.subject')
                }}</Label>
                <Input
                    id="subject"
                    name="subject"
                    v-model="form.subject"
                    autofocus
                    :placeholder="trans('helpdesk.ticket.placeholder.subject')"
                />
                <InputError :message="form.errors.subject" />
            </div>

            <div class="grid gap-2">
                <Label for="description">{{
                    trans('helpdesk.ticket.label.description')
                }}</Label>
                <Textarea
                    id="description"
                    name="description"
                    v-model="form.description"
                    rows="5"
                    :placeholder="
                        trans('helpdesk.ticket.placeholder.description')
                    "
                />
                <InputError :message="form.errors.description" />
            </div>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div class="grid gap-2">
                    <Label for="priority">{{
                        trans('helpdesk.ticket.label.priority')
                    }}</Label>
                    <Select
                        id="priority"
                        v-model="form.priority"
                        name="priority"
                        :aria-invalid="!!form.errors.priority"
                    >
                        <SelectTrigger class="w-full">
                            <SelectValue
                                :placeholder="
                                    trans(
                                        'helpdesk.ticket.placeholder.priority',
                                    )
                                "
                            />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="option in props.priorityOptions"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.priority" />
                </div>

                <div class="grid gap-2">
                    <Label for="category_id">{{
                        trans('helpdesk.ticket.label.category')
                    }}</Label>
                    <Select
                        id="category_id"
                        v-model="form.category_id"
                        name="category_id"
                    >
                        <SelectTrigger class="w-full">
                            <SelectValue
                                :placeholder="
                                    trans(
                                        'helpdesk.ticket.placeholder.category',
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
                    <InputError :message="form.errors.category_id" />
                </div>

                <div class="grid gap-2">
                    <Label for="branch_id">{{
                        trans('helpdesk.ticket.label.branch')
                    }}</Label>
                    <Select
                        id="branch_id"
                        v-model="form.branch_id"
                        name="branch_id"
                    >
                        <SelectTrigger class="w-full">
                            <SelectValue
                                :placeholder="
                                    trans('helpdesk.ticket.placeholder.branch')
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
                        trans('helpdesk.ticket.label.department')
                    }}</Label>
                    <Select
                        id="department_id"
                        v-model="form.department_id"
                        name="department_id"
                    >
                        <SelectTrigger class="w-full">
                            <SelectValue
                                :placeholder="
                                    trans(
                                        'helpdesk.ticket.placeholder.department',
                                    )
                                "
                            />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="option in props.departmentOptions"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.department_id" />
                </div>

                <div class="grid gap-2">
                    <Label for="queue_id">{{
                        trans('helpdesk.ticket.label.queue')
                    }}</Label>
                    <Select
                        id="queue_id"
                        v-model="form.queue_id"
                        name="queue_id"
                    >
                        <SelectTrigger class="w-full">
                            <SelectValue
                                :placeholder="
                                    trans('helpdesk.ticket.placeholder.queue')
                                "
                            />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="option in props.queueOptions"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.queue_id" />
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
                    {{ trans('helpdesk.ticket.action.create') }}
                </Button>
                <Button
                    type="button"
                    variant="secondary"
                    :disabled="form.processing"
                    class="w-full sm:w-auto"
                    @click="reset"
                >
                    {{ trans('helpdesk.ticket.action.reset') }}
                </Button>
                <Button
                    :as="Link"
                    variant="outline"
                    :href="index()"
                    :disabled="form.processing"
                    class="w-full sm:w-auto"
                >
                    {{ trans('helpdesk.ticket.action.back') }}
                </Button>
            </div>
        </form>
    </PageWrapper>
</template>
