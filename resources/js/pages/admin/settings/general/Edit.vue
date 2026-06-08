<script lang="ts" setup>
import { Head, setLayoutProps, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { update } from '@/actions/App/Http/Controllers/Admin/Settings/GeneralSettingsController';
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
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { useTrans } from '@/composables/useTrans';
import { renderFlagIcon } from '@/lib/utils';
import { dashboard } from '@/routes';
import { index } from '@/routes/admin/settings';
import { edit } from '@/routes/admin/settings/general';
import type { GeneralSettings, SelectOption } from '@/types';

type Props = {
    generalSettings: GeneralSettings;
    localeOptions: SelectOption[];
};

type GeneralSettingsFormData = {
    site_name: string;
    site_description: string;
    site_locale: string;
};

defineOptions({ inheritAttrs: false });

const props = defineProps<Props>();
const { trans } = useTrans();
const form = useForm<GeneralSettingsFormData>({
    site_name: props.generalSettings.site_name,
    site_description: props.generalSettings.site_description,
    site_locale: props.generalSettings.site_locale,
});

const selectedLocaleOption = computed(() =>
    props.localeOptions.find((option) => option.value === form.site_locale),
);

function submit(): void {
    form.submit(update(), {
        preserveScroll: true,
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
            title: trans('admin.settings.general.title'),
            href: edit(),
        },
    ],
});
</script>

<template>
    <Head :title="trans('admin.settings.general.title')" />

    <PageWrapper
        :title="trans('admin.settings.general.heading')"
        :description="trans('admin.settings.general.description')"
    >
        <form class="flex flex-col gap-6" @submit.prevent="submit">
            <Card>
                <CardHeader>
                    <CardTitle>{{
                        trans('admin.settings.general.heading')
                    }}</CardTitle>
                    <CardDescription>{{
                        trans('admin.settings.general.description')
                    }}</CardDescription>
                </CardHeader>

                <CardContent class="flex flex-col gap-6">
                    <div class="flex flex-col gap-2">
                        <Label for="site_name">{{
                            trans('admin.settings.general.label.site_name')
                        }}</Label>
                        <Input
                            id="site_name"
                            v-model="form.site_name"
                            name="site_name"
                            autofocus
                            autocomplete="organization"
                            :aria-invalid="Boolean(form.errors.site_name)"
                            :placeholder="
                                trans(
                                    'admin.settings.general.placeholder.site_name',
                                )
                            "
                        />
                        <InputError :message="form.errors.site_name" />
                    </div>

                    <div class="flex flex-col gap-2">
                        <Label for="site_description">{{
                            trans(
                                'admin.settings.general.label.site_description',
                            )
                        }}</Label>
                        <Textarea
                            id="site_description"
                            v-model="form.site_description"
                            name="site_description"
                            :aria-invalid="
                                Boolean(form.errors.site_description)
                            "
                            :placeholder="
                                trans(
                                    'admin.settings.general.placeholder.site_description',
                                )
                            "
                        />
                        <InputError :message="form.errors.site_description" />
                    </div>

                    <div class="flex flex-col gap-2">
                        <Label for="site_locale">{{
                            trans('admin.settings.general.label.locale')
                        }}</Label>
                        <Select v-model="form.site_locale" name="site_locale">
                            <SelectTrigger
                                id="site_locale"
                                class="w-full"
                                :aria-invalid="Boolean(form.errors.site_locale)"
                            >
                                <SelectValue
                                    :placeholder="
                                        trans(
                                            'admin.settings.general.placeholder.locale',
                                        )
                                    "
                                >
                                    {{
                                        selectedLocaleOption
                                            ? `${renderFlagIcon(selectedLocaleOption.icon)} ${selectedLocaleOption.label}`
                                            : ''
                                    }}
                                </SelectValue>
                            </SelectTrigger>
                            <SelectContent>
                                <SelectGroup>
                                    <SelectItem
                                        v-for="option in localeOptions"
                                        :key="option.value"
                                        :value="option.value"
                                    >
                                        {{ renderFlagIcon(option.icon) }}
                                        {{ option.label }}
                                    </SelectItem>
                                </SelectGroup>
                            </SelectContent>
                        </Select>
                        <InputError :message="form.errors.site_locale" />
                    </div>
                </CardContent>

                <CardFooter class="border-t">
                    <Button type="submit" :disabled="form.processing">
                        {{
                            form.processing
                                ? `${trans('admin.settings.general.action.submit')}...`
                                : trans('admin.settings.general.action.submit')
                        }}
                    </Button>
                </CardFooter>
            </Card>
        </form>
    </PageWrapper>
</template>
