<script setup lang="ts">
import { Head, Link, setLayoutProps, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { update } from '@/actions/App/Http/Controllers/Helpdesk/TicketController';
import {
    store as storeUpload,
    destroy as destroyUpload,
} from '@/actions/App/Http/Controllers/TemporaryUploadController';
import InputError from '@/components/InputError.vue';
import PageWrapper from '@/components/PageWrapper.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Spinner } from '@/components/ui/spinner';
import { Textarea } from '@/components/ui/textarea';
import { Uploader } from '@/components/uploader';
import { useTrans } from '@/composables/useTrans';
import { dashboard } from '@/routes';
import { edit, index, show } from '@/routes/tickets';
import type { SelectOption, Ticket } from '@/types';

type Props = {
    ticket: Ticket;
    statusOptions: SelectOption[];
    priorityOptions: SelectOption[];
    branchOptions: SelectOption[];
    departmentOptions: SelectOption[];
    categoryOptions: { label: string; options: SelectOption[] }[];
    agentOptions: SelectOption[];
    assetOptions?: SelectOption[];
};

type EditTicketFormData = {
    subject: string;
    description: string;
    status: string;
    priority: string;
    assigned_to: string;
    branch_id: string;
    department_id: string;
    category_id: string;
    attachment_upload_ids: string[];
    asset_ids: string[];
};

const props = defineProps<Props>();

defineOptions({ inheritAttrs: false });

const { trans } = useTrans();

const form = useForm<EditTicketFormData>({
    subject: props.ticket.subject,
    description: props.ticket.description,
    status: props.ticket.status,
    priority: props.ticket.priority,
    assigned_to: props.ticket.assigned_to ?? '',
    branch_id: props.ticket.branch_id ?? '',
    department_id: props.ticket.department_id ?? '',
    category_id: props.ticket.category_id ?? '',
    attachment_upload_ids: [],
    asset_ids: props.ticket.assets ? props.ticket.assets.map((a) => a.id) : [],
});

const temporaryUploadUrl = storeUpload().url;
const deleteTemporaryUploadUrl = (id: string) => destroyUpload(id).url;
const attachmentUploadIds = ref<string[]>([]);

function submit(): void {
    form.transform((data) => ({
        ...data,
        attachment_upload_ids: attachmentUploadIds.value,
    })).submit(update(props.ticket.id), {
        onSuccess: () => {
            attachmentUploadIds.value = [];
        },
    });
}

function reset(): void {
    form.resetAndClearErrors();
    attachmentUploadIds.value = [];
}

function setAssetSelection(
    assetId: string,
    checked: boolean | 'indeterminate',
): void {
    if (checked === true) {
        if (!form.asset_ids.includes(assetId)) {
            form.asset_ids.push(assetId);
        }

        return;
    }

    form.asset_ids = form.asset_ids.filter((id) => id !== assetId);
}

setLayoutProps({
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: trans('helpdesk.ticket.index.title'), href: index() },
        { title: props.ticket.ticket_number, href: show(props.ticket.id) },
        {
            title: trans('helpdesk.ticket.edit.title'),
            href: edit(props.ticket.id),
        },
    ],
});
</script>

<template>
    <Head
        :title="`${trans('helpdesk.ticket.edit.title')} — ${ticket.ticket_number}`"
    />

    <PageWrapper
        :title="trans('helpdesk.ticket.edit.heading')"
        :description="`${ticket.ticket_number} · ${ticket.typeLabel}`"
    >
        <form class="flex flex-col gap-6" @submit.prevent="submit">
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
                    <Label for="status">{{
                        trans('helpdesk.ticket.label.status')
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
                                    trans('helpdesk.ticket.placeholder.status')
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
                    <Label for="assigned_to">{{
                        trans('helpdesk.ticket.label.assignee')
                    }}</Label>
                    <Select
                        id="assigned_to"
                        v-model="form.assigned_to"
                        name="assigned_to"
                    >
                        <SelectTrigger class="w-full">
                            <SelectValue
                                :placeholder="
                                    trans(
                                        'helpdesk.ticket.placeholder.assignee',
                                    )
                                "
                            />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="option in props.agentOptions"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.assigned_to" />
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
                            <SelectGroup
                                v-for="group in props.categoryOptions"
                                :key="group.label"
                            >
                                <SelectLabel>{{ group.label }}</SelectLabel>
                                <SelectItem
                                    v-for="option in group.options"
                                    :key="option.value"
                                    :value="option.value"
                                >
                                    {{ option.label }}
                                </SelectItem>
                            </SelectGroup>
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
            </div>

            <div
                v-if="props.assetOptions && props.assetOptions.length > 0"
                class="grid gap-2"
            >
                <Label>{{ trans('helpdesk.ticket.label.assets') }}</Label>
                <div class="grid gap-2 sm:grid-cols-2">
                    <label
                        v-for="option in props.assetOptions"
                        :key="option.value"
                        class="flex cursor-pointer items-center gap-3 rounded-lg border border-border p-3 transition-colors hover:bg-accent/50"
                    >
                        <Checkbox
                            :checked="form.asset_ids.includes(option.value)"
                            @update:checked="
                                (checked: boolean | 'indeterminate') =>
                                    setAssetSelection(option.value, checked)
                            "
                        />
                        <div class="flex flex-col">
                            <span class="text-sm font-medium">{{
                                option.label
                            }}</span>
                        </div>
                    </label>
                </div>
                <InputError :message="form.errors.asset_ids" />
            </div>

            <div class="grid gap-2">
                <Label>{{ trans('helpdesk.ticket.label.attachments') }}</Label>
                <Uploader
                    v-model="attachmentUploadIds"
                    :upload-url="temporaryUploadUrl"
                    :delete-url-resolver="deleteTemporaryUploadUrl"
                    :accepted-file-types="[
                        'application/pdf',
                        'application/msword',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'application/vnd.ms-excel',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'text/csv',
                        'image/png',
                        'image/jpeg',
                        'application/zip',
                        'application/x-rar-compressed',
                    ]"
                    :max-file-size="10 * 1024 * 1024"
                    :multiple="true"
                    :label-idle="
                        trans('helpdesk.ticket.placeholder.uploader_idle')
                    "
                />
                <InputError :message="form.errors.attachment_upload_ids" />
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
                    {{ trans('helpdesk.ticket.action.update') }}
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
                    :href="show(ticket.id)"
                    :disabled="form.processing"
                    class="w-full sm:w-auto"
                >
                    {{ trans('helpdesk.ticket.action.back') }}
                </Button>
            </div>
        </form>
    </PageWrapper>
</template>
