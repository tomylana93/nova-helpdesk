<script setup lang="ts">
import { Head, Link, setLayoutProps, useForm } from '@inertiajs/vue3';
import { store } from '@/actions/Laravel/Fortify/Http/Controllers/AuthenticatedSessionController';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { useTrans } from '@/composables/useTrans';
import { request } from '@/routes/password';

type LoginFormData = {
    email: string;
    password: string;
    remember: boolean;
};

type LoginSubmitData = Omit<LoginFormData, 'remember'> & {
    remember: '' | 'on';
};

const { trans } = useTrans();

const form = useForm<LoginFormData>({
    email: '',
    password: '',
    remember: false,
});

function submit(): void {
    form.transform(
        (data): LoginSubmitData => ({
            ...data,
            remember: data.remember ? 'on' : '',
        }),
    ).submit(store(), {
        onSuccess: () => {
            form.reset('password');
        },
    });
}

defineOptions({ inheritAttrs: false });

setLayoutProps({
    title: trans('auth.login.card.heading'),
    description: trans('auth.login.card.description'),
});

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();
</script>

<template>
    <Head :title="trans('auth.login.title')" />

    <div
        v-if="status"
        class="mb-4 text-center text-sm font-medium text-green-600"
    >
        {{ status }}
    </div>

    <form class="flex flex-col gap-6" @submit.prevent="submit">
        <div class="grid gap-2">
            <Label for="email">{{ trans('auth.login.label.email') }}</Label>
            <Input
                id="email"
                type="email"
                name="email"
                v-model="form.email"
                autofocus
                :tabindex="1"
                autocomplete="email"
            />
            <InputError :message="form.errors.email" />
        </div>

        <div class="grid gap-2">
            <Label for="password">{{
                trans('auth.login.label.password')
            }}</Label>
            <PasswordInput
                id="password"
                name="password"
                v-model="form.password"
                :tabindex="2"
                autocomplete="current-password"
            />
            <InputError :message="form.errors.password" />
        </div>

        <div class="flex items-center justify-between">
            <Label for="remember" class="flex items-center space-x-3">
                <Checkbox
                    id="remember"
                    v-model="form.remember"
                    name="remember"
                    :tabindex="3"
                />
                <span>{{ trans('auth.login.label.remember') }}</span>
            </Label>
        </div>

        <div class="flex flex-col gap-2">
            <Button
                class="w-full"
                type="submit"
                :tabindex="4"
                :disabled="form.processing"
                data-test="login-button"
            >
                <Spinner v-if="form.processing" />
                {{ trans('auth.login.action.submit') }}
            </Button>
            <Button
                :as="Link"
                :href="request()"
                variant="secondary"
                class="w-full"
                v-if="canResetPassword"
                type="button"
                :tabindex="5"
                data-test="forgot-password-link"
                :disabled="form.processing"
            >
                {{ trans('auth.login.link.forgot') }}
            </Button>
        </div>
    </form>
</template>
