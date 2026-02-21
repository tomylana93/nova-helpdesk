<script setup lang="ts">
import type { PendingVisit } from '@inertiajs/core';
import { router, useForm } from '@inertiajs/vue3';
import { loadLanguageAsync, trans } from 'laravel-vue-i18n';
import AppHead from '@/components/AppHead.vue';
import { onBeforeUnmount, ref } from 'vue';

import {
    destroyImage,
    storeImage,
    update,
} from '@/actions/App/Http/Controllers/Settings/GeneralSettingsController';
import ContentHeader from '@/components/ContentHeader.vue';
import ContentWrapper from '@/components/ContentWrapper.vue';
import InputError from '@/components/InputError.vue';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
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
import { Uploader } from '@/components/uploader';
import { useCsrf } from '@/composables/useCsrf';
import AppLayout from '@/layouts/AppLayout.vue';
import type { GeneralSettings, GeneralSettingsForm, Option } from '@/types';

type Props = {
    generalSettings: GeneralSettings;
    headerStyles: Option[];
    languages: Option[];
};

type ImageField = 'app_logo' | 'app_icon' | 'app_favicon';

const props = defineProps<Props>();

const form = useForm<GeneralSettingsForm>({
    app_name: props.generalSettings.app_name,
    app_logo: props.generalSettings.app_logo,
    app_icon: props.generalSettings.app_icon,
    app_favicon: props.generalSettings.app_favicon,
    header_style: props.generalSettings.header_style,
    language: props.generalSettings.language,
});

const initialImages: Record<ImageField, string | null> = {
    app_logo: props.generalSettings.app_logo,
    app_icon: props.generalSettings.app_icon,
    app_favicon: props.generalSettings.app_favicon,
};

const { buildHeaders } = useCsrf();
const showLeaveDialog = ref(false);
const pendingVisit = ref<PendingVisit | null>(null);
let allowLeave = false;
const initialLanguage = ref(props.generalSettings.language);

const submit = () => {
    form.submit(update(), {
        preserveScroll: true,
        onBefore: () => {
            allowLeave = true;
        },
        onSuccess: async () => {
            await deleteReplacedImages();
            initialImages.app_logo = form.app_logo;
            initialImages.app_icon = form.app_icon;
            initialImages.app_favicon = form.app_favicon;
            form.defaults();

            if (initialLanguage.value !== form.language) {
                await loadLanguageAsync(form.language);
                document.documentElement.lang = form.language;
                initialLanguage.value = form.language;
                window.location.reload();
            }
        },
        onFinish: () => {
            allowLeave = false;
        },
    });
};

const imageRoutes: Record<
    ImageField,
    { processUrl: string; revertUrl: string }
> = {
    app_logo: {
        processUrl: storeImage('app_logo').url,
        revertUrl: destroyImage('app_logo').url,
    },
    app_icon: {
        processUrl: storeImage('app_icon').url,
        revertUrl: destroyImage('app_icon').url,
    },
    app_favicon: {
        processUrl: storeImage('app_favicon').url,
        revertUrl: destroyImage('app_favicon').url,
    },
};

async function deleteImage(type: ImageField, path: string): Promise<void> {
    try {
        await fetch(imageRoutes[type].revertUrl, {
            method: 'DELETE',
            headers: buildHeaders({ 'Content-Type': 'application/json' }),
            body: JSON.stringify({ path }),
        });
    } catch {
        // Best-effort cleanup.
    }
}

async function cleanupUploads(): Promise<void> {
    const pendingDeletes: Array<{ type: ImageField; path: string }> = [];

    (Object.keys(imageRoutes) as Array<ImageField>).forEach((type) => {
        const current = form[type];
        const initial = initialImages[type];

        if (current && current !== initial) {
            pendingDeletes.push({ type, path: current });
        }
    });

    await Promise.allSettled(
        pendingDeletes.map(({ type, path }) => deleteImage(type, path)),
    );
}

function handleRemoved(type: ImageField, path: string): void {
    if (path === initialImages[type]) {
        return;
    }

    void deleteImage(type, path);
}

async function deleteReplacedImages(): Promise<void> {
    const pendingDeletes: Array<{ type: ImageField; path: string }> = [];

    (Object.keys(imageRoutes) as Array<ImageField>).forEach((type) => {
        const current = form[type];
        const initial = initialImages[type];

        if (initial && current !== initial) {
            pendingDeletes.push({ type, path: initial });
        }
    });

    await Promise.allSettled(
        pendingDeletes.map(({ type, path }) => deleteImage(type, path)),
    );
}

const handleBeforeLeave = (visit?: PendingVisit) => {
    if (allowLeave) {
        return true;
    }

    if (visit && visit.method !== 'get') {
        return true;
    }

    if (!form.isDirty) {
        return true;
    }

    pendingVisit.value = visit ?? null;
    showLeaveDialog.value = true;
    return false;
};

const removeBeforeHook = router.on('before', (event) => {
    if (!handleBeforeLeave(event.detail.visit)) {
        event.preventDefault();
    }
});

const handleBeforeUnload = (event: BeforeUnloadEvent) => {
    if (!form.isDirty) {
        return;
    }
    event.preventDefault();
};

window.addEventListener('beforeunload', handleBeforeUnload);

const confirmLeave = async () => {
    showLeaveDialog.value = false;
    await cleanupUploads();

    const visit = pendingVisit.value;
    pendingVisit.value = null;

    if (visit?.url) {
        allowLeave = true;
        router.visit(visit.url, visit);
        setTimeout(() => {
            allowLeave = false;
        }, 0);
    }
};

const cancelLeave = () => {
    showLeaveDialog.value = false;
    pendingVisit.value = null;
};

onBeforeUnmount(() => {
    window.removeEventListener('beforeunload', handleBeforeUnload);
    removeBeforeHook();
});
</script>

<template>
    <AppHead :title="trans('settings.general.title')" />

    <AppLayout>
        <ContentWrapper>
            <ContentHeader
                :title="trans('settings.general.header.title')"
                :description="trans('settings.general.header.description')"
            />
            <form class="space-y-8" @submit.prevent="submit">
                <div class="grid gap-6 md:grid-cols-2">
                    <div class="grid gap-2 md:col-span-2">
                        <Label for="app_name">
                            {{ trans('settings.general.label.app_name') }}
                        </Label>
                        <Input
                            id="app_name"
                            name="app_name"
                            v-model="form.app_name"
                            :placeholder="
                                trans('settings.general.placeholder.app_name')
                            "
                            autocomplete="off"
                        />
                        <InputError :message="form.errors.app_name" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="header_style">
                            {{ trans('settings.general.header_style.label') }}
                        </Label>
                        <Select v-model="form.header_style">
                            <SelectTrigger class="w-full" id="header_style">
                                <SelectValue
                                    :placeholder="
                                        trans(
                                            'settings.general.header_style.placeholder',
                                        )
                                    "
                                />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="option in props.headerStyles"
                                    :key="option.value"
                                    :value="option.value"
                                >
                                    {{ option.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="form.errors.header_style" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="language">
                            {{ trans('settings.general.language.label') }}
                        </Label>
                        <Select v-model="form.language">
                            <SelectTrigger class="w-full" id="language">
                                <SelectValue :placeholder="trans('settings.general.language.placeholder')" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="option in props.languages"
                                    :key="option.value"
                                    :value="option.value"
                                >
                                    {{ option.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="form.errors.language" />
                    </div>
                </div>
                 <div class="grid gap-6 lg:grid-cols-3">
                    <div class="grid gap-2">
                        <Label>
                            {{ trans('settings.general.app_logo.label') }}
                        </Label>
                        <Uploader
                            v-model="form.app_logo"
                            :process-url="imageRoutes.app_logo.processUrl"
                            :revert-url="imageRoutes.app_logo.revertUrl"
                            defer-delete
                            @removed="handleRemoved('app_logo', $event.path)"
                        />
                        <InputError :message="form.errors.app_logo" />
                    </div>

                    <div class="grid gap-2">
                        <Label>
                            {{ trans('settings.general.app_icon.label') }}
                        </Label>
                        <Uploader
                            v-model="form.app_icon"
                            :process-url="imageRoutes.app_icon.processUrl"
                            :revert-url="imageRoutes.app_icon.revertUrl"
                            defer-delete
                            @removed="handleRemoved('app_icon', $event.path)"
                        />
                        <InputError :message="form.errors.app_icon" />
                    </div>

                    <div class="grid gap-2">
                        <Label>
                            {{ trans('settings.general.app_favicon.label') }}
                        </Label>
                        <Uploader
                            v-model="form.app_favicon"
                            :process-url="imageRoutes.app_favicon.processUrl"
                            :revert-url="imageRoutes.app_favicon.revertUrl"
                            defer-delete
                            @removed="handleRemoved('app_favicon', $event.path)"
                        />
                        <InputError :message="form.errors.app_favicon" />
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <Button type="submit" :disabled="form.processing">
                        {{ trans('settings.general.actions.save') }}
                    </Button>
                </div>
            </form>

             <AlertDialog v-model:open="showLeaveDialog">
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>
                            {{ trans('settings.general.dialog.title') }}
                        </AlertDialogTitle>
                        <AlertDialogDescription>
                            {{ trans('settings.general.dialog.description') }}
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                        <AlertDialogCancel @click="cancelLeave">
                            {{ trans('settings.general.dialog.stay') }}
                        </AlertDialogCancel>
                        <AlertDialogAction @click="confirmLeave">
                            {{ trans('settings.general.dialog.leave') }}
                        </AlertDialogAction>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>
        </ContentWrapper>
    </AppLayout>
</template>
