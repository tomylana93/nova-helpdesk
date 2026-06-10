<script setup lang="ts">
import { Head, Link, setLayoutProps, useForm } from '@inertiajs/vue3';
import {
    approve,
    reject,
} from '@/actions/App/Http/Controllers/Helpdesk/TicketApprovalController';
import { store as storeComment } from '@/actions/App/Http/Controllers/Helpdesk/TicketCommentController';
import InputError from '@/components/InputError.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
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
import { edit, index, show } from '@/routes/tickets';
import type { Ticket } from '@/types';

type Comment = {
    id: string;
    body: string;
    visibility: 'public' | 'internal';
    authorName: string;
    createdAt: string | null;
};

type Props = {
    ticket: Ticket;
    canUpdate?: boolean;
    canComment?: boolean;
    isAgent?: boolean;
    comments?: Comment[];
    approval?: {
        status: string;
        reviewerName: string | null;
        decidedAt: string | null;
        decisionNote: string | null;
    } | null;
};

const props = defineProps<Props>();

const commentForm = useForm({
    body: '',
    visibility: 'public',
});

const approvalForm = useForm({
    decision_note: '',
});

function submitComment(): void {
    commentForm.submit(storeComment(props.ticket.id), {
        onSuccess: () => commentForm.reset(),
    });
}

function submitApprove(): void {
    approvalForm.submit(approve(props.ticket.id));
}

function submitReject(): void {
    approvalForm.submit(reject(props.ticket.id));
}

const { trans } = useTrans();

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
            <div class="flex shrink-0 gap-2">
                <Button
                    v-if="canUpdate"
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
            <div class="grid grid-cols-2 gap-x-8 gap-y-4 sm:grid-cols-3">
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
                        {{ trans('helpdesk.ticket.label.queue') }}
                    </p>
                    <p class="mt-1 text-sm">{{ ticket.queueName ?? '—' }}</p>
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
        </div>

        <!-- Approval Section -->
        <div
            v-if="ticket.status === 'waiting_for_approval' && isAgent"
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
                </div>
            </div>

            <p v-else class="mb-6 text-sm text-muted-foreground">
                {{ trans('helpdesk.comment.label.no_comments') }}
            </p>

            <form
                v-if="canComment"
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

                <div v-if="isAgent" class="grid gap-2">
                    <Label for="comment-visibility">{{
                        trans('helpdesk.comment.label.visibility')
                    }}</Label>
                    <Select
                        id="comment-visibility"
                        v-model="commentForm.visibility"
                    >
                        <SelectTrigger class="w-48">
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
