<script lang="ts" setup>
import { Head, setLayoutProps, useForm } from '@inertiajs/vue3';
import { update } from '@/actions/App/Http/Controllers/Admin/Settings/PasswordSettingsController';
import InputError from '@/components/InputError.vue';
import PageWrapper from '@/components/PageWrapper.vue';
import PasswordInput from '@/components/PasswordInput.vue';
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
import { useTrans } from '@/composables/useTrans';
import { dashboard } from '@/routes';
import { index } from '@/routes/admin/settings';
import { edit } from '@/routes/admin/settings/password';
import type { PasswordRulesProps } from '@/types';

type PasswordSettingsFormData = {
    default_user_password: string;
    default_user_password_confirmation: string;
};

defineOptions({ inheritAttrs: false });

const props = defineProps<PasswordRulesProps>();
const { trans } = useTrans();

const form = useForm<PasswordSettingsFormData>({
    default_user_password: '',
    default_user_password_confirmation: '',
});

function submit(): void {
    form.submit(update(), {
        preserveScroll: true,
        onSuccess: () => {
            form.resetAndClearErrors();
        },
        onError: () => {
            form.reset(
                'default_user_password',
                'default_user_password_confirmation',
            );
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
            title: trans('admin.settings.password.title'),
            href: edit(),
        },
    ],
});
</script>

<template>
    <Head :title="trans('admin.settings.password.title')" />

    <PageWrapper
        :title="trans('admin.settings.password.heading')"
        :description="trans('admin.settings.password.description')"
    >
        <form class="flex flex-col gap-6" @submit.prevent="submit">
            <Card>
                <CardHeader>
                    <CardTitle>{{
                        trans('admin.settings.password.heading')
                    }}</CardTitle>
                    <CardDescription>{{
                        trans('admin.settings.password.description')
                    }}</CardDescription>
                </CardHeader>

                <CardContent class="flex flex-col gap-6">
                    <div class="grid gap-2">
                        <Label for="default_user_password">{{
                            trans(
                                'admin.settings.password.label.default_user_password',
                            )
                        }}</Label>
                        <PasswordInput
                            id="default_user_password"
                            name="default_user_password"
                            v-model="form.default_user_password"
                            autocomplete="new-password"
                            :placeholder="
                                trans(
                                    'admin.settings.password.placeholder.default_user_password',
                                )
                            "
                            :passwordrules="props.passwordRules"
                        />
                        <InputError
                            :message="form.errors.default_user_password"
                        />
                    </div>

                    <div class="grid gap-2">
                        <Label for="default_user_password_confirmation">{{
                            trans(
                                'admin.settings.password.label.default_user_password_confirmation',
                            )
                        }}</Label>
                        <PasswordInput
                            id="default_user_password_confirmation"
                            name="default_user_password_confirmation"
                            v-model="form.default_user_password_confirmation"
                            autocomplete="new-password"
                            :placeholder="
                                trans(
                                    'admin.settings.password.placeholder.default_user_password_confirmation',
                                )
                            "
                            :passwordrules="props.passwordRules"
                        />
                        <InputError
                            :message="
                                form.errors.default_user_password_confirmation
                            "
                        />
                    </div>
                </CardContent>

                <CardFooter class="border-t">
                    <Button type="submit" :disabled="form.processing">
                        {{ trans('admin.settings.password.action.submit') }}
                    </Button>
                </CardFooter>
            </Card>
        </form>
    </PageWrapper>
</template>
