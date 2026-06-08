<script setup lang="ts">
import { Head, setLayoutProps, useForm } from '@inertiajs/vue3';
import { update } from '@/actions/App/Http/Controllers/Settings/SecurityController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { useTrans } from '@/composables/useTrans';
import { edit } from '@/routes/security';
import type { PasswordRulesProps } from '@/types';

type SecurityFormData = {
    current_password: string;
    password: string;
    password_confirmation: string;
};

defineOptions({ inheritAttrs: false });

const props = defineProps<PasswordRulesProps>();
const { trans } = useTrans();

const form = useForm<SecurityFormData>({
    current_password: '',
    password: '',
    password_confirmation: '',
});

function submit(): void {
    form.submit(update(), {
        preserveScroll: true,
        onSuccess: () => {
            form.resetAndClearErrors();
        },
        onError: () => {
            form.reset('password', 'password_confirmation', 'current_password');
        },
    });
}

setLayoutProps({
    breadcrumbs: [
        {
            title: trans('settings.security.title'),
            href: edit(),
        },
    ],
});
</script>

<template>
    <Head :title="trans('settings.security.title')" />

    <h1 class="sr-only">{{ trans('settings.security.title') }}</h1>

    <div class="space-y-6">
        <Heading
            variant="small"
            :title="trans('settings.security.heading')"
            :description="trans('settings.security.description')"
        />

        <form class="space-y-6" @submit.prevent="submit">
            <div class="grid gap-2">
                <Label for="current_password">{{
                    trans('settings.security.label.current_password')
                }}</Label>
                <PasswordInput
                    id="current_password"
                    name="current_password"
                    v-model="form.current_password"
                    autocomplete="current-password"
                    :placeholder="
                        trans('settings.security.placeholder.current_password')
                    "
                />
                <InputError :message="form.errors.current_password" />
            </div>

            <div class="grid gap-2">
                <Label for="password">{{
                    trans('settings.security.label.password')
                }}</Label>
                <PasswordInput
                    id="password"
                    name="password"
                    v-model="form.password"
                    autocomplete="new-password"
                    :placeholder="
                        trans('settings.security.placeholder.password')
                    "
                    :passwordrules="props.passwordRules"
                />
                <InputError :message="form.errors.password" />
            </div>

            <div class="grid gap-2">
                <Label for="password_confirmation">{{
                    trans('settings.security.label.password_confirmation')
                }}</Label>
                <PasswordInput
                    id="password_confirmation"
                    name="password_confirmation"
                    v-model="form.password_confirmation"
                    autocomplete="new-password"
                    :placeholder="
                        trans(
                            'settings.security.placeholder.password_confirmation',
                        )
                    "
                    :passwordrules="props.passwordRules"
                />
                <InputError :message="form.errors.password_confirmation" />
            </div>

            <div class="flex items-center gap-4">
                <Button
                    type="submit"
                    :disabled="form.processing"
                    data-test="update-password-button"
                >
                    {{ trans('settings.security.action.submit') }}
                </Button>
            </div>
        </form>
    </div>
</template>
