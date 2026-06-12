<script lang="ts" setup>
import { Head, setLayoutProps, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { update } from '@/actions/App/Http/Controllers/Admin/Settings/StyleSettingsController';
import {
    destroy,
    store,
} from '@/actions/App/Http/Controllers/TemporaryUploadController';
import InputError from '@/components/InputError.vue';
import PageWrapper from '@/components/PageWrapper.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { ToggleGroup, ToggleGroupItem } from '@/components/ui/toggle-group';
import { Uploader } from '@/components/uploader';
import type { UploaderExistingFile } from '@/components/uploader';
import { useTrans } from '@/composables/useTrans';
import { dashboard } from '@/routes';
import { index } from '@/routes/admin/settings';
import { edit } from '@/routes/admin/settings/style';
import type { SelectOption, StyleSettings } from '@/types';

type BrandingFiles = {
    icon: UploaderExistingFile[];
    icon_alt: UploaderExistingFile[];
    logo: UploaderExistingFile[];
    logo_alt: UploaderExistingFile[];
    favicon: UploaderExistingFile[];
};

type FontOption = SelectOption & {
    heading: string;
    body: string;
};

type Props = {
    styleSettings: StyleSettings;
    logoStyleOptions: SelectOption[];
    authLayoutOptions: SelectOption[];
    layoutOptions: SelectOption[];
    themeOptions: SelectOption[];
    fontOptions: FontOption[];
    brandingFiles: BrandingFiles;
};

type StyleSettingsFormData = {
    site_logo_style: StyleSettings['site_logo_style'];
    site_auth_layout: StyleSettings['site_auth_layout'];
    site_layout: StyleSettings['site_layout'];
    site_theme: StyleSettings['site_theme'];
    site_font: StyleSettings['site_font'];
    site_icon_upload_id: string | null;
    site_icon_alt_upload_id: string | null;
    site_logo_upload_id: string | null;
    site_logo_alt_upload_id: string | null;
    site_favicon_upload_id: string | null;
    site_icon_remove: boolean;
    site_icon_alt_remove: boolean;
    site_logo_remove: boolean;
    site_logo_alt_remove: boolean;
    site_favicon_remove: boolean;
};

type StyleSettingsSubmitData = StyleSettingsFormData;

defineOptions({ inheritAttrs: false });

const props = defineProps<Props>();
const { trans } = useTrans();
const form = useForm<StyleSettingsFormData>({
    site_logo_style: props.styleSettings.site_logo_style,
    site_auth_layout: props.styleSettings.site_auth_layout,
    site_layout: props.styleSettings.site_layout,
    site_theme: props.styleSettings.site_theme,
    site_font: props.styleSettings.site_font,
    site_icon_upload_id: null as string | null,
    site_icon_alt_upload_id: null as string | null,
    site_logo_upload_id: null as string | null,
    site_logo_alt_upload_id: null as string | null,
    site_favicon_upload_id: null as string | null,
    site_icon_remove: false,
    site_icon_alt_remove: false,
    site_logo_remove: false,
    site_logo_alt_remove: false,
    site_favicon_remove: false,
});

const selectedThemeOption = computed(() =>
    props.themeOptions.find((option) => option.value === form.site_theme),
);
const selectedFontOption = computed(() =>
    props.fontOptions.find((option) => option.value === form.site_font),
);

const temporaryUploadUrl = store().url;
const deleteTemporaryUploadUrl = (temporaryUploadId: string) =>
    destroy(temporaryUploadId).url;

const imageTypes = ['image/png', 'image/jpeg', 'image/webp'];
const faviconTypes = [
    ...imageTypes,
    'image/x-icon',
    'image/vnd.microsoft.icon',
];

const temporaryUploadMessages = computed(() => ({
    invalidType: trans('admin.settings.style.message.upload_invalid_type'),
    tooLarge: trans('admin.settings.style.message.upload_too_large'),
    uploadFailed: trans('admin.settings.style.message.upload_failed'),
    removeFailed: trans('admin.settings.style.message.upload_remove_failed'),
}));

const iconUploadIds = ref<string[]>([]);
const iconRemovedIds = ref<Array<string | number>>([]);
const iconAltUploadIds = ref<string[]>([]);
const iconAltRemovedIds = ref<Array<string | number>>([]);
const logoUploadIds = ref<string[]>([]);
const logoRemovedIds = ref<Array<string | number>>([]);
const logoAltUploadIds = ref<string[]>([]);
const logoAltRemovedIds = ref<Array<string | number>>([]);
const faviconUploadIds = ref<string[]>([]);
const faviconRemovedIds = ref<Array<string | number>>([]);

function transform(data: StyleSettingsFormData): StyleSettingsSubmitData {
    return {
        ...data,
        site_icon_upload_id: iconUploadIds.value[0] ?? null,
        site_icon_alt_upload_id: iconAltUploadIds.value[0] ?? null,
        site_logo_upload_id: logoUploadIds.value[0] ?? null,
        site_logo_alt_upload_id: logoAltUploadIds.value[0] ?? null,
        site_favicon_upload_id: faviconUploadIds.value[0] ?? null,
        site_icon_remove: iconRemovedIds.value.length > 0,
        site_icon_alt_remove: iconAltRemovedIds.value.length > 0,
        site_logo_remove: logoRemovedIds.value.length > 0,
        site_logo_alt_remove: logoAltRemovedIds.value.length > 0,
        site_favicon_remove: faviconRemovedIds.value.length > 0,
    };
}

function resetUploaderState(): void {
    iconUploadIds.value = [];
    iconRemovedIds.value = [];
    iconAltUploadIds.value = [];
    iconAltRemovedIds.value = [];
    logoUploadIds.value = [];
    logoRemovedIds.value = [];
    logoAltUploadIds.value = [];
    logoAltRemovedIds.value = [];
    faviconUploadIds.value = [];
    faviconRemovedIds.value = [];
}

function submit(): void {
    form.transform(transform).submit(update(), {
        preserveScroll: true,
        onSuccess: () => {
            resetUploaderState();
        },
    });
}

setLayoutProps({
    breadcrumbs: [
        {
            title: 'Dashboard',
            href: dashboard(),
        },
        {
            title: trans('admin.settings.title'),
            href: index(),
        },
        {
            title: trans('admin.settings.style.title'),
            href: edit(),
        },
    ],
});
</script>

<template>
    <Head :title="trans('admin.settings.style.title')" />

    <PageWrapper
        :title="trans('admin.settings.style.heading')"
        :description="trans('admin.settings.style.description')"
    >
        <form class="flex flex-col gap-6" @submit.prevent="submit">
            <Card>
                <CardHeader>
                    <CardTitle>{{
                        trans('admin.settings.style.heading')
                    }}</CardTitle>
                    <CardDescription>
                        {{ trans('admin.settings.style.description') }}
                    </CardDescription>
                </CardHeader>

                <CardContent class="flex flex-col gap-6">
                    <div class="flex flex-col gap-3">
                        <Label for="site_logo_style">
                            {{ trans('admin.settings.style.label.logo_style') }}
                        </Label>
                        <ToggleGroup
                            id="site_logo_style"
                            v-model="form.site_logo_style"
                            type="single"
                            name="site_logo_style"
                            variant="outline"
                            class="grid w-full grid-cols-1 gap-3 sm:grid-cols-2"
                        >
                            <ToggleGroupItem
                                v-for="option in logoStyleOptions"
                                :key="option.value"
                                :value="option.value"
                                class="h-auto flex-col items-start gap-1 px-4 py-3 text-left"
                            >
                                <span class="font-medium">{{
                                    option.label
                                }}</span>
                                <span class="text-xs text-muted-foreground">
                                    {{
                                        trans(
                                            `admin.settings.style.option.logo_style.${option.value}`,
                                        )
                                    }}
                                </span>
                            </ToggleGroupItem>
                        </ToggleGroup>
                        <InputError :message="form.errors.site_logo_style" />
                    </div>

                    <div class="flex flex-col gap-3">
                        <Label for="site_auth_layout">
                            {{
                                trans('admin.settings.style.label.auth_layout')
                            }}
                        </Label>
                        <ToggleGroup
                            id="site_auth_layout"
                            v-model="form.site_auth_layout"
                            type="single"
                            name="site_auth_layout"
                            variant="outline"
                            class="grid w-full grid-cols-1 gap-3 md:grid-cols-3"
                        >
                            <ToggleGroupItem
                                v-for="option in authLayoutOptions"
                                :key="option.value"
                                :value="option.value"
                                class="h-auto flex-col items-start gap-1 px-4 py-3 text-left"
                            >
                                <span class="font-medium">{{
                                    option.label
                                }}</span>
                                <span class="text-xs text-muted-foreground">
                                    {{
                                        trans(
                                            `admin.settings.style.option.auth_layout.${option.value}`,
                                        )
                                    }}
                                </span>
                            </ToggleGroupItem>
                        </ToggleGroup>
                        <InputError :message="form.errors.site_auth_layout" />
                    </div>

                    <div class="flex flex-col gap-3">
                        <Label for="site_layout">
                            {{ trans('admin.settings.style.label.layout') }}
                        </Label>
                        <ToggleGroup
                            id="site_layout"
                            v-model="form.site_layout"
                            type="single"
                            name="site_layout"
                            variant="outline"
                            class="grid w-full grid-cols-1 gap-3 md:grid-cols-2"
                        >
                            <ToggleGroupItem
                                v-for="option in layoutOptions"
                                :key="option.value"
                                :value="option.value"
                                class="h-auto flex-col items-start gap-1 px-4 py-3 text-left"
                            >
                                <span class="font-medium">{{
                                    option.label
                                }}</span>
                                <span class="text-xs text-muted-foreground">
                                    {{
                                        trans(
                                            `admin.settings.style.option.layout.${option.value}`,
                                        )
                                    }}
                                </span>
                            </ToggleGroupItem>
                        </ToggleGroup>
                        <InputError :message="form.errors.site_layout" />
                    </div>

                    <div class="grid gap-6 md:grid-cols-2">
                        <div class="flex flex-col gap-2">
                            <Label for="site_theme">
                                {{ trans('admin.settings.style.label.theme') }}
                            </Label>
                            <Select v-model="form.site_theme" name="site_theme">
                                <SelectTrigger
                                    id="site_theme"
                                    class="w-full"
                                    :aria-invalid="
                                        Boolean(form.errors.site_theme)
                                    "
                                >
                                    <SelectValue
                                        :placeholder="
                                            trans(
                                                'admin.settings.style.placeholder.theme',
                                            )
                                        "
                                    >
                                        {{ selectedThemeOption?.label ?? '' }}
                                    </SelectValue>
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectGroup>
                                        <SelectItem
                                            v-for="option in themeOptions"
                                            :key="option.value"
                                            :value="option.value"
                                        >
                                            {{ option.label }}
                                        </SelectItem>
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                            <InputError :message="form.errors.site_theme" />
                        </div>

                        <div class="flex flex-col gap-2">
                            <Label for="site_font">
                                {{ trans('admin.settings.style.label.font') }}
                            </Label>
                            <Select v-model="form.site_font" name="site_font">
                                <SelectTrigger
                                    id="site_font"
                                    class="w-full"
                                    :aria-invalid="
                                        Boolean(form.errors.site_font)
                                    "
                                >
                                    <SelectValue
                                        :placeholder="
                                            trans(
                                                'admin.settings.style.placeholder.font',
                                            )
                                        "
                                    >
                                        {{ selectedFontOption?.label ?? '' }}
                                    </SelectValue>
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectGroup>
                                        <SelectItem
                                            v-for="option in fontOptions"
                                            :key="option.value"
                                            :value="option.value"
                                            class="py-3"
                                        >
                                            <div class="flex flex-col">
                                                <span class="font-medium">{{
                                                    option.label
                                                }}</span>
                                                <span
                                                    class="text-xs text-muted-foreground"
                                                >
                                                    {{ option.heading }} /
                                                    {{ option.body }}
                                                </span>
                                            </div>
                                        </SelectItem>
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                            <InputError :message="form.errors.site_font" />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>{{
                        trans('admin.settings.style.branding.heading')
                    }}</CardTitle>
                    <CardDescription>{{
                        trans('admin.settings.style.branding.description')
                    }}</CardDescription>
                </CardHeader>

                <CardContent class="grid gap-6 lg:grid-cols-2">
                    <div class="flex flex-col gap-2">
                        <Label>{{
                            trans('admin.settings.style.label.icon')
                        }}</Label>
                        <Uploader
                            v-model="iconUploadIds"
                            v-model:removed="iconRemovedIds"
                            :existing-files="brandingFiles.icon"
                            :upload-url="temporaryUploadUrl"
                            :delete-url-resolver="deleteTemporaryUploadUrl"
                            :accepted-file-types="imageTypes"
                            :messages="temporaryUploadMessages"
                            :label-idle="
                                trans(
                                    'admin.settings.style.helper.uploader_idle',
                                )
                            "
                            preview-size="compact"
                        />
                        <InputError
                            :message="form.errors.site_icon_upload_id"
                        />
                    </div>

                    <div class="flex flex-col gap-2">
                        <Label>{{
                            trans('admin.settings.style.label.icon_alt')
                        }}</Label>
                        <Uploader
                            v-model="iconAltUploadIds"
                            v-model:removed="iconAltRemovedIds"
                            :existing-files="brandingFiles.icon_alt"
                            :upload-url="temporaryUploadUrl"
                            :delete-url-resolver="deleteTemporaryUploadUrl"
                            :accepted-file-types="imageTypes"
                            :messages="temporaryUploadMessages"
                            :label-idle="
                                trans(
                                    'admin.settings.style.helper.uploader_idle',
                                )
                            "
                            preview-size="compact"
                        />
                        <InputError
                            :message="form.errors.site_icon_alt_upload_id"
                        />
                    </div>

                    <div class="flex flex-col gap-2">
                        <Label>{{
                            trans('admin.settings.style.label.logo')
                        }}</Label>
                        <Uploader
                            v-model="logoUploadIds"
                            v-model:removed="logoRemovedIds"
                            :existing-files="brandingFiles.logo"
                            :upload-url="temporaryUploadUrl"
                            :delete-url-resolver="deleteTemporaryUploadUrl"
                            :accepted-file-types="imageTypes"
                            :messages="temporaryUploadMessages"
                            :label-idle="
                                trans(
                                    'admin.settings.style.helper.uploader_idle',
                                )
                            "
                        />
                        <InputError
                            :message="form.errors.site_logo_upload_id"
                        />
                    </div>

                    <div class="flex flex-col gap-2">
                        <Label>{{
                            trans('admin.settings.style.label.logo_alt')
                        }}</Label>
                        <Uploader
                            v-model="logoAltUploadIds"
                            v-model:removed="logoAltRemovedIds"
                            :existing-files="brandingFiles.logo_alt"
                            :upload-url="temporaryUploadUrl"
                            :delete-url-resolver="deleteTemporaryUploadUrl"
                            :accepted-file-types="imageTypes"
                            :messages="temporaryUploadMessages"
                            :label-idle="
                                trans(
                                    'admin.settings.style.helper.uploader_idle',
                                )
                            "
                        />
                        <InputError
                            :message="form.errors.site_logo_alt_upload_id"
                        />
                    </div>

                    <div class="flex flex-col gap-2 lg:col-span-2">
                        <Label>{{
                            trans('admin.settings.style.label.favicon')
                        }}</Label>
                        <Uploader
                            v-model="faviconUploadIds"
                            v-model:removed="faviconRemovedIds"
                            :existing-files="brandingFiles.favicon"
                            :upload-url="temporaryUploadUrl"
                            :delete-url-resolver="deleteTemporaryUploadUrl"
                            :accepted-file-types="faviconTypes"
                            :messages="temporaryUploadMessages"
                            :label-idle="
                                trans(
                                    'admin.settings.style.helper.uploader_idle',
                                )
                            "
                            preview-size="compact"
                        />
                        <InputError
                            :message="form.errors.site_favicon_upload_id"
                        />
                    </div>
                </CardContent>

                <CardFooter class="border-t">
                    <Button type="submit" :disabled="form.processing">
                        {{
                            form.processing
                                ? `${trans('admin.settings.style.action.submit')}...`
                                : trans('admin.settings.style.action.submit')
                        }}
                    </Button>
                </CardFooter>
            </Card>
        </form>
    </PageWrapper>
</template>
