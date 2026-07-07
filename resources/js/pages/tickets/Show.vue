<script setup lang="ts">
import { Head, Link, setLayoutProps, useForm, usePage } from '@inertiajs/vue3';
import { Paperclip } from '@lucide/vue';
import { ref } from 'vue';
import {
    approve,
    reject,
} from '@/actions/App/Http/Controllers/Helpdesk/TicketApprovalController';
import { store as storeComment } from '@/actions/App/Http/Controllers/Helpdesk/TicketCommentController';
import { syncAssets } from '@/actions/App/Http/Controllers/Helpdesk/TicketController';
import {
    confirm as confirmResolved,
    reopen,
    transition,
} from '@/actions/App/Http/Controllers/Helpdesk/TicketLifecycleController';
import {
    store as storeUpload,
    destroy as destroyUpload,
} from '@/actions/App/Http/Controllers/TemporaryUploadController';
import InputError from '@/components/InputError.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
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
import { Uploader } from '@/components/uploader';
import { useTrans } from '@/composables/useTrans';
import { dashboard } from '@/routes';
import { edit, index, show } from '@/routes/tickets';
import type { SharedPageProps, SelectOption } from '@/types';
import type { Ticket, TicketAttachment } from '@/types/ticket';

type Comment = {
    id: string;
    body: string;
    visibility: 'public' | 'internal';
    authorName: string;
    createdAt: string | null;
    attachments: TicketAttachment[];
};

type TransitionOption = {
    value: string;
    label: string;
};

type Props = {
    ticket: Ticket;
    viewerRole: 'requester' | 'it_agent' | 'auditor' | 'super_admin';
    canSeeInternal?: boolean;
    canAct?: boolean;
    canReply?: boolean;
    availableTransitions?: TransitionOption[];
    canApprove?: boolean;
    canReopen?: boolean;
    canConfirm?: boolean;
    comments?: Comment[];
    approval?: {
        status: string;
        reviewerName: string | null;
        decidedAt: string | null;
        decisionNote: string | null;
    } | null;
    assetOptions?: SelectOption[];
};

const props = defineProps<Props>();

const page = usePage<SharedPageProps>();
const canManageAssets =
    (props.viewerRole === 'it_agent' || props.viewerRole === 'super_admin') &&
    page.props.auth.abilities.manage_assets;

const isEditingAssets = ref(false);
const syncAssetsForm = useForm({
    asset_ids: props.ticket.assets ? props.ticket.assets.map((a) => a.id) : [],
});

function submitSyncAssets(): void {
    syncAssetsForm.submit(syncAssets(props.ticket.id), {
        preserveScroll: true,
        onSuccess: () => {
            isEditingAssets.value = false;
        },
    });
}

function cancelEditAssets(): void {
    syncAssetsForm.asset_ids = props.ticket.assets
        ? props.ticket.assets.map((a) => a.id)
        : [];
    isEditingAssets.value = false;
}

function setSyncAssetSelection(
    assetId: string,
    checked: boolean | 'indeterminate',
): void {
    if (checked === true) {
        if (!syncAssetsForm.asset_ids.includes(assetId)) {
            syncAssetsForm.asset_ids.push(assetId);
        }

        return;
    }

    syncAssetsForm.asset_ids = syncAssetsForm.asset_ids.filter(
        (id) => id !== assetId,
    );
}

type CommentFormData = {
    body: string;
    visibility: string;
    attachment_upload_ids: string[];
};

const commentForm = useForm<CommentFormData>({
    body: '',
    visibility: 'public',
    attachment_upload_ids: [],
});

const temporaryUploadUrl = storeUpload().url;
const deleteTemporaryUploadUrl = (id: string) => destroyUpload(id).url;
const commentAttachmentUploadIds = ref<string[]>([]);

const approvalForm = useForm({
    decision_note: '',
});

const transitionForm = useForm({
    status: '',
});

// Reopen and confirm-resolved carry no payload; a single empty form tracks their processing state.
const lifecycleForm = useForm({});

function submitComment(): void {
    commentForm
        .transform((data) => ({
            ...data,
            attachment_upload_ids: commentAttachmentUploadIds.value,
        }))
        .submit(storeComment(props.ticket.id), {
            onSuccess: () => {
                commentForm.reset();
                commentAttachmentUploadIds.value = [];
            },
        });
}

function submitApprove(): void {
    approvalForm.submit(approve(props.ticket.id));
}

function submitReject(): void {
    approvalForm.submit(reject(props.ticket.id));
}

function applyTransition(status: string): void {
    transitionForm.status = status;
    transitionForm.submit(transition(props.ticket.id), {
        preserveScroll: true,
    });
}

function submitReopen(): void {
    lifecycleForm.submit(reopen(props.ticket.id), { preserveScroll: true });
}

function submitConfirm(): void {
    lifecycleForm.submit(confirmResolved(props.ticket.id), {
        preserveScroll: true,
    });
}

const { trans } = useTrans();

function formatBytes(bytes: number, decimals = 2) {
    if (!bytes) {
        return '0 Bytes';
    }

    const k = 1024;
    const dm = decimals < 0 ? 0 : decimals;
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));

    return `${parseFloat((bytes / Math.pow(k, i)).toFixed(dm))} ${sizes[i]}`;
}

setLayoutProps({
    breadcrumbs: [
        { title: 'Dashboard', href: dashboard() },
        { title: trans('helpdesk.ticket.index.title'), href: index() },
        {
            title: props.ticket.ticket_number,
            href: show(props.ticket.id),
        },
    ],
});
</script>

<template>
    <Head :title="`${ticket.ticket_number} — ${ticket.subject}`" />

    <div class="mx-auto max-w-4xl space-y-6 p-4 sm:p-6">
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"
        >
            <div>
                <h1 class="text-xl font-semibold">{{ ticket.subject }}</h1>
                <p class="text-sm text-muted-foreground">
                    {{ ticket.ticket_number }}
                </p>
            </div>
            <div class="flex shrink-0 flex-wrap gap-2">
                <Button
                    v-if="canConfirm"
                    type="button"
                    size="sm"
                    :disabled="lifecycleForm.processing"
                    @click="submitConfirm"
                >
                    <Spinner v-if="lifecycleForm.processing" />
                    {{ trans('helpdesk.ticket.action.confirm_resolved') }}
                </Button>
                <Button
                    v-if="canReopen"
                    type="button"
                    variant="outline"
                    size="sm"
                    :disabled="lifecycleForm.processing"
                    @click="submitReopen"
                >
                    {{ trans('helpdesk.ticket.action.reopen') }}
                </Button>
                <Button
                    v-if="canAct"
                    :as="Link"
                    :href="edit(ticket.id)"
                    size="sm"
                >
                    {{ trans('helpdesk.ticket.action.edit') }}
                </Button>
                <Button :as="Link" variant="outline" :href="index()" size="sm">
                    {{ trans('helpdesk.ticket.action.back') }}
                </Button>
            </div>
        </div>

        <div class="rounded-lg border bg-card p-6 shadow-sm">
            <div
                class="grid grid-cols-1 gap-x-8 gap-y-4 sm:grid-cols-2 lg:grid-cols-3"
            >
                <div>
                    <p
                        class="text-xs font-medium tracking-wider text-muted-foreground uppercase"
                    >
                        {{ trans('helpdesk.ticket.label.status') }}
                    </p>
                    <div class="mt-1">
                        <Badge
                            :variant="ticket.statusVariant as 'default'"
                            class="font-normal"
                        >
                            {{ ticket.statusLabel }}
                        </Badge>
                    </div>
                </div>

                <div>
                    <p
                        class="text-xs font-medium tracking-wider text-muted-foreground uppercase"
                    >
                        {{ trans('helpdesk.ticket.label.priority') }}
                    </p>
                    <div class="mt-1">
                        <Badge
                            :variant="ticket.priorityVariant as 'default'"
                            class="font-normal capitalize"
                        >
                            {{ ticket.priorityLabel }}
                        </Badge>
                    </div>
                </div>

                <div>
                    <p
                        class="text-xs font-medium tracking-wider text-muted-foreground uppercase"
                    >
                        {{ trans('helpdesk.ticket.label.type') }}
                    </p>
                    <p class="mt-1 text-sm">{{ ticket.typeLabel }}</p>
                </div>

                <div>
                    <p
                        class="text-xs font-medium tracking-wider text-muted-foreground uppercase"
                    >
                        {{ trans('helpdesk.ticket.label.requester') }}
                    </p>
                    <p class="mt-1 text-sm">
                        {{ ticket.requesterName ?? '—' }}
                    </p>
                </div>

                <div>
                    <p
                        class="text-xs font-medium tracking-wider text-muted-foreground uppercase"
                    >
                        {{ trans('helpdesk.ticket.label.assignee') }}
                    </p>
                    <p class="mt-1 text-sm">{{ ticket.assigneeName ?? '—' }}</p>
                </div>

                <div>
                    <p
                        class="text-xs font-medium tracking-wider text-muted-foreground uppercase"
                    >
                        {{ trans('helpdesk.ticket.label.branch') }}
                    </p>
                    <p class="mt-1 text-sm">{{ ticket.branchName ?? '—' }}</p>
                </div>

                <div>
                    <p
                        class="text-xs font-medium tracking-wider text-muted-foreground uppercase"
                    >
                        {{ trans('helpdesk.ticket.label.department') }}
                    </p>
                    <p class="mt-1 text-sm">
                        {{ ticket.departmentName ?? '—' }}
                    </p>
                </div>

                <div>
                    <p
                        class="text-xs font-medium tracking-wider text-muted-foreground uppercase"
                    >
                        {{ trans('helpdesk.ticket.label.category') }}
                    </p>
                    <p class="mt-1 text-sm">{{ ticket.categoryName ?? '—' }}</p>
                </div>

                <div>
                    <p
                        class="text-xs font-medium tracking-wider text-muted-foreground uppercase"
                    >
                        {{ trans('helpdesk.ticket.label.submitted_at') }}
                    </p>
                    <p class="mt-1 text-sm">
                        {{
                            ticket.submitted_at
                                ? new Date(ticket.submitted_at).toLocaleString()
                                : '—'
                        }}
                    </p>
                </div>

                <div v-if="ticket.resolved_at">
                    <p
                        class="text-xs font-medium tracking-wider text-muted-foreground uppercase"
                    >
                        {{ trans('helpdesk.ticket.label.resolved_at') }}
                    </p>
                    <p class="mt-1 text-sm">
                        {{ new Date(ticket.resolved_at).toLocaleString() }}
                    </p>
                </div>
            </div>
        </div>

        <div class="rounded-lg border bg-card p-6 shadow-sm">
            <h2 class="mb-3 font-medium">
                {{ trans('helpdesk.ticket.label.description') }}
            </h2>
            <p class="text-sm whitespace-pre-wrap text-muted-foreground">
                {{ ticket.description }}
            </p>

            <div
                v-if="ticket.attachments && ticket.attachments.length > 0"
                class="mt-6 border-t pt-4"
            >
                <h3
                    class="mb-2 text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                >
                    {{ trans('helpdesk.ticket.label.attachments') }}
                </h3>
                <ul class="divide-y divide-border">
                    <li
                        v-for="attachment in ticket.attachments"
                        :key="attachment.id"
                        class="flex items-center justify-between py-2 text-sm"
                    >
                        <a
                            :href="attachment.url"
                            target="_blank"
                            class="flex items-center gap-2 font-medium text-primary hover:underline"
                        >
                            <Paperclip class="h-4 w-4 text-muted-foreground" />
                            <span class="max-w-[250px] truncate sm:max-w-md">{{
                                attachment.original_name
                            }}</span>
                            <span class="text-xs text-muted-foreground"
                                >({{ formatBytes(attachment.size) }})</span
                            >
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Associated Assets -->
        <div class="rounded-lg border bg-card p-6 shadow-sm">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-base font-medium">
                    {{ trans('helpdesk.ticket.label.assets') }}
                </h2>
                <Button
                    v-if="
                        canManageAssets &&
                        assetOptions &&
                        assetOptions.length > 0
                    "
                    type="button"
                    variant="outline"
                    size="sm"
                    @click="
                        isEditingAssets
                            ? cancelEditAssets()
                            : (isEditingAssets = true)
                    "
                >
                    {{
                        isEditingAssets
                            ? trans('helpdesk.ticket.action.cancel')
                            : trans('helpdesk.ticket.action.manage_assets')
                    }}
                </Button>
            </div>

            <!-- Read-only View -->
            <div v-if="!isEditingAssets">
                <div
                    v-if="ticket.assets && ticket.assets.length > 0"
                    class="grid gap-4 sm:grid-cols-2"
                >
                    <div
                        v-for="asset in ticket.assets"
                        :key="asset.id"
                        class="flex flex-col justify-between rounded-lg border border-border bg-accent/10 p-4 transition-colors hover:bg-accent/20"
                    >
                        <div>
                            <div
                                class="mb-2 flex items-center justify-between gap-2"
                            >
                                <span
                                    class="text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                                >
                                    {{ asset.asset_tag }}
                                </span>
                                <Badge
                                    :variant="asset.statusVariant as any"
                                    class="px-1.5 py-0 text-[10px] font-normal"
                                >
                                    {{ asset.statusLabel }}
                                </Badge>
                            </div>
                            <h3
                                class="mb-1 text-sm font-semibold text-foreground"
                            >
                                {{ asset.name }}
                            </h3>
                            <p class="text-xs text-muted-foreground">
                                {{ asset.categoryLabel }}
                            </p>
                        </div>
                    </div>
                </div>
                <p v-else class="text-sm text-muted-foreground">
                    {{ trans('helpdesk.ticket.message.no_assets') }}
                </p>
            </div>

            <!-- Inline Sync/Edit View -->
            <div v-else>
                <form @submit.prevent="submitSyncAssets" class="space-y-4">
                    <div class="grid gap-2 sm:grid-cols-2">
                        <label
                            v-for="option in props.assetOptions"
                            :key="option.value"
                            class="flex cursor-pointer items-center gap-3 rounded-lg border border-border p-3 transition-colors hover:bg-accent/50"
                        >
                            <Checkbox
                                :checked="
                                    syncAssetsForm.asset_ids.includes(
                                        option.value,
                                    )
                                "
                                @update:checked="
                                    (checked: boolean | 'indeterminate') =>
                                        setSyncAssetSelection(
                                            option.value,
                                            checked,
                                        )
                                "
                            />
                            <div class="flex flex-col">
                                <span class="text-sm font-medium">{{
                                    option.label
                                }}</span>
                            </div>
                        </label>
                    </div>
                    <div class="flex justify-end gap-2">
                        <Button
                            type="button"
                            variant="secondary"
                            size="sm"
                            :disabled="syncAssetsForm.processing"
                            @click="cancelEditAssets"
                        >
                            {{ trans('helpdesk.ticket.action.cancel') }}
                        </Button>
                        <Button
                            type="submit"
                            size="sm"
                            :disabled="syncAssetsForm.processing"
                        >
                            <Spinner
                                v-if="syncAssetsForm.processing"
                                class="mr-2"
                            />
                            {{ trans('helpdesk.ticket.action.save_assets') }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Agent Lifecycle Actions -->
        <div
            v-if="canAct && availableTransitions && availableTransitions.length"
            class="rounded-lg border bg-card p-6 shadow-sm"
        >
            <h2 class="mb-3 font-medium">
                {{ trans('helpdesk.ticket.label.actions') }}
            </h2>
            <div class="flex flex-wrap gap-2">
                <Button
                    v-for="option in availableTransitions"
                    :key="option.value"
                    type="button"
                    size="sm"
                    variant="outline"
                    :disabled="transitionForm.processing"
                    @click="applyTransition(option.value)"
                >
                    {{ trans(`helpdesk.ticket.transition.${option.value}`) }}
                </Button>
            </div>
        </div>

        <!-- Approval Section -->
        <div
            v-if="canApprove"
            class="rounded-lg border border-yellow-200 bg-yellow-50 p-6 shadow-sm dark:border-yellow-800 dark:bg-yellow-950/20"
        >
            <h2 class="mb-4 font-medium">
                {{ trans('helpdesk.approval.label.pending') }}
            </h2>
            <form class="space-y-4" @submit.prevent>
                <div class="grid gap-2">
                    <Label for="decision-note">{{
                        trans('helpdesk.approval.label.decision_note')
                    }}</Label>
                    <Textarea
                        id="decision-note"
                        v-model="approvalForm.decision_note"
                        rows="3"
                    />
                </div>
                <div class="flex gap-3">
                    <Button
                        type="button"
                        variant="default"
                        :disabled="approvalForm.processing"
                        @click="submitApprove"
                    >
                        <Spinner v-if="approvalForm.processing" />
                        {{ trans('helpdesk.approval.label.approve') }}
                    </Button>
                    <Button
                        type="button"
                        variant="destructive"
                        :disabled="approvalForm.processing"
                        @click="submitReject"
                    >
                        {{ trans('helpdesk.approval.label.reject') }}
                    </Button>
                </div>
            </form>
        </div>

        <!-- Comments -->
        <div class="rounded-lg border bg-card p-6 shadow-sm">
            <h2 class="mb-4 font-medium">
                {{ trans('helpdesk.comment.label.add_comment') }}
            </h2>

            <div v-if="comments && comments.length > 0" class="mb-6 space-y-4">
                <div
                    v-for="comment in comments"
                    :key="comment.id"
                    :class="[
                        'rounded-lg border p-4 text-sm',
                        comment.visibility === 'internal'
                            ? 'border-yellow-200 bg-yellow-50 dark:border-yellow-800 dark:bg-yellow-950/20'
                            : 'bg-muted/40',
                    ]"
                >
                    <div class="mb-2 flex items-center justify-between gap-2">
                        <span class="font-medium">{{
                            comment.authorName
                        }}</span>
                        <div
                            class="flex items-center gap-2 text-xs text-muted-foreground"
                        >
                            <Badge
                                v-if="comment.visibility === 'internal'"
                                variant="outline"
                                class="text-xs"
                            >
                                {{ trans('helpdesk.comment.label.internal') }}
                            </Badge>
                            <span>{{
                                comment.createdAt
                                    ? new Date(
                                          comment.createdAt,
                                      ).toLocaleString()
                                    : ''
                            }}</span>
                        </div>
                    </div>
                    <p class="whitespace-pre-wrap">{{ comment.body }}</p>

                    <div
                        v-if="
                            comment.attachments &&
                            comment.attachments.length > 0
                        "
                        class="mt-3 border-t pt-2"
                    >
                        <ul class="flex flex-wrap gap-2">
                            <li
                                v-for="attachment in comment.attachments"
                                :key="attachment.id"
                            >
                                <a
                                    :href="attachment.url"
                                    target="_blank"
                                    class="flex items-center gap-1.5 rounded border bg-background px-2.5 py-1 text-xs font-medium text-primary transition hover:bg-muted hover:underline"
                                >
                                    <Paperclip
                                        class="h-3 w-3 text-muted-foreground"
                                    />
                                    <span class="max-w-[150px] truncate">{{
                                        attachment.original_name
                                    }}</span>
                                    <span
                                        class="text-[10px] text-muted-foreground"
                                        >({{
                                            formatBytes(attachment.size)
                                        }})</span
                                    >
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <p v-else class="mb-6 text-sm text-muted-foreground">
                {{ trans('helpdesk.comment.label.no_comments') }}
            </p>

            <p
                v-if="
                    canReply &&
                    !canAct &&
                    ticket.status === 'waiting_for_requester'
                "
                class="mb-4 rounded-md border border-yellow-200 bg-yellow-50 p-3 text-sm text-yellow-800 dark:border-yellow-800 dark:bg-yellow-950/20 dark:text-yellow-200"
            >
                {{ trans('helpdesk.comment.label.awaiting_reply') }}
            </p>

            <form
                v-if="canReply"
                class="space-y-4"
                @submit.prevent="submitComment"
            >
                <div class="grid gap-2">
                    <Label for="comment-body">{{
                        trans('helpdesk.comment.label.add_comment')
                    }}</Label>
                    <Textarea
                        id="comment-body"
                        v-model="commentForm.body"
                        rows="4"
                        :placeholder="
                            trans('helpdesk.comment.label.placeholder')
                        "
                    />
                    <InputError :message="commentForm.errors.body" />
                </div>

                <div v-if="canAct" class="grid gap-2">
                    <Label for="comment-visibility">{{
                        trans('helpdesk.comment.label.visibility')
                    }}</Label>
                    <Select
                        id="comment-visibility"
                        v-model="commentForm.visibility"
                    >
                        <SelectTrigger class="w-full sm:w-48">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="public">{{
                                trans('helpdesk.comment.label.public')
                            }}</SelectItem>
                            <SelectItem value="internal">{{
                                trans('helpdesk.comment.label.internal')
                            }}</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <div class="grid gap-2">
                    <Uploader
                        v-model="commentAttachmentUploadIds"
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
                    <InputError
                        :message="commentForm.errors.attachment_upload_ids"
                    />
                </div>

                <Button
                    type="submit"
                    :disabled="commentForm.processing"
                    size="sm"
                >
                    <Spinner v-if="commentForm.processing" />
                    {{ trans('helpdesk.comment.action.submit') }}
                </Button>
            </form>
        </div>
    </div>
</template>
