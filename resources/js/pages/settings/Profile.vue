<script setup lang="ts">
import type { FormDataConvertible } from '@inertiajs/core';
import { Head, setLayoutProps, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { update } from '@/actions/App/Http/Controllers/Settings/ProfileController';
import {
    destroy,
    store,
} from '@/actions/App/Http/Controllers/TemporaryUploadController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Uploader } from '@/components/uploader';
import type { UploaderExistingFile } from '@/components/uploader';
import { useTrans } from '@/composables/useTrans';
import { edit } from '@/routes/profile';
import type { AuthenticatedSharedPageProps } from '@/types';

type Props = {
    status?: string;
    avatarFile: UploaderExistingFile[];
};

type ProfileFormData = {
    name: string;
    email: string;
    avatar_upload_id: string | null;
    avatar_remove: boolean;
};

type ProfileSubmitData = {
    name: string;
    email: string;
    avatar_upload_id: FormDataConvertible;
    avatar_remove: FormDataConvertible;
};

defineOptions({ inheritAttrs: false });

defineProps<Props>();

const page = usePage<AuthenticatedSharedPageProps>();
const user = computed(() => page.props.auth.user);
const { trans } = useTrans();

const form = useForm<ProfileFormData>({
    name: user.value.name,
    email: user.value.email,
    avatar_upload_id: null as string | null,
    avatar_remove: false,
});

const temporaryUploadUrl = store().url;
const deleteTemporaryUploadUrl = (temporaryUploadId: string) =>
    destroy(temporaryUploadId).url;

const imageTypes = ['image/png', 'image/jpeg', 'image/webp'];

const temporaryUploadMessages = computed(() => ({
    invalidType: trans('settings.profile.message.upload_invalid_type'),
    tooLarge: trans('settings.profile.message.upload_too_large'),
    uploadFailed: trans('settings.profile.message.upload_failed'),
    removeFailed: trans('settings.profile.message.upload_remove_failed'),
}));

const avatarUploadIds = ref<string[]>([]);
const avatarRemovedIds = ref<Array<string | number>>([]);

function transform(data: ProfileFormData): ProfileSubmitData {
    return {
        ...data,
        avatar_upload_id: avatarUploadIds.value[0] ?? null,
        avatar_remove: avatarRemovedIds.value.length > 0,
    };
}

function resetUploaderState(): void {
    avatarUploadIds.value = [];
    avatarRemovedIds.value = [];
}

function submit(): void {
    form.transform(transform).submit(update(), {
        onSuccess: () => {
            resetUploaderState();
        },
    });
}

setLayoutProps({
    breadcrumbs: [
        {
            title: trans('settings.profile.title'),
            href: edit(),
        },
    ],
});
</script>

<template>
    <Head :title="trans('settings.profile.title')" />

    <h1 class="sr-only">{{ trans('settings.profile.title') }}</h1>

    <div class="flex flex-col space-y-6">
        <Heading
            variant="small"
            :title="trans('settings.profile.heading')"
            :description="trans('settings.profile.description')"
        />

        <form class="space-y-6" @submit.prevent="submit">
            <div class="grid gap-2">
                <Label for="name">{{
                    trans('settings.profile.label.name')
                }}</Label>
                <Input
                    id="name"
                    name="name"
                    v-model="form.name"
                    autocomplete="name"
                    :placeholder="trans('settings.profile.placeholder.name')"
                />
                <InputError :message="form.errors.name" />
            </div>

            <div class="grid gap-2">
                <Label for="email">{{
                    trans('settings.profile.label.email')
                }}</Label>
                <Input
                    id="email"
                    type="email"
                    name="email"
                    v-model="form.email"
                    autocomplete="username"
                    :placeholder="trans('settings.profile.placeholder.email')"
                />
                <InputError :message="form.errors.email" />
            </div>

            <div class="grid gap-2">
                <Label>{{ trans('settings.profile.label.avatar') }}</Label>
                <Uploader
                    v-model="avatarUploadIds"
                    v-model:removed="avatarRemovedIds"
                    :existing-files="avatarFile"
                    :upload-url="temporaryUploadUrl"
                    :delete-url-resolver="deleteTemporaryUploadUrl"
                    :accepted-file-types="imageTypes"
                    :messages="temporaryUploadMessages"
                    :label-idle="trans('settings.profile.helper.uploader_idle')"
                    preview-size="compact"
                />
                <InputError :message="form.errors.avatar_upload_id" />
            </div>

            <div class="flex items-center gap-4">
                <Button
                    type="submit"
                    :disabled="form.processing"
                    data-test="update-profile-button"
                    >{{ trans('settings.profile.action.submit') }}</Button
                >
            </div>
        </form>
    </div>
</template>
