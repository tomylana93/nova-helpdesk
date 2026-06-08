<script setup lang="ts">
import { Head, Link, setLayoutProps, useForm } from '@inertiajs/vue3';
import { store } from '@/actions/Laravel/Fortify/Http/Controllers/PasswordResetLinkController';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { useTrans } from '@/composables/useTrans';
import { login } from '@/routes';

type ForgotPasswordFormData = {
    email: string;
};

const { trans } = useTrans();

const form = useForm<ForgotPasswordFormData>({
    email: '',
});

function submit(): void {
    form.submit(store());
}

defineOptions({ inheritAttrs: false });

setLayoutProps({
    title: trans('auth.forgot_password.card.heading'),
    description: trans('auth.forgot_password.card.description'),
});

defineProps<{
    status?: string;
}>();
</script>

<template>
    <Head :title="trans('auth.forgot_password.title')" />

    <div
        v-if="status"
        class="mb-4 text-center text-sm font-medium text-green-600"
    >
        {{ status }}
    </div>

    <form class="flex flex-col gap-6" @submit.prevent="submit">
        <div class="grid gap-2">
            <Label for="email">{{
                trans('auth.forgot_password.label.email')
            }}</Label>
            <Input
                id="email"
                type="email"
                name="email"
                v-model="form.email"
                autocomplete="off"
                autofocus
            />
            <InputError :message="form.errors.email" />
        </div>

        <div class="flex flex-col gap-2">
            <Button
                type="submit"
                class="w-full"
                :disabled="form.processing"
                data-test="email-password-reset-link-button"
            >
                <Spinner v-if="form.processing" />
                {{ trans('auth.forgot_password.action.submit') }}
            </Button>
            <Button
                :as="Link"
                :href="login()"
                variant="secondary"
                class="w-full"
                type="button"
                :disabled="form.processing"
            >
                {{ trans('auth.forgot_password.link.login') }}
            </Button>
        </div>
    </form>
</template>
