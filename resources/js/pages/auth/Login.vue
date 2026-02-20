<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthBase from '@/layouts/AuthLayout.vue';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';
import type { LoginFormData } from '@/types';

defineProps<{
    canResetPassword: boolean;
    canRegister: boolean;
}>();

const form = useForm<LoginFormData>({
    email: '',
    password: '',
    remember: false,
});

const submit = (): void => {
    form.submit(store(), {
        onSuccess: () => {
            form.reset('password');
        },
    });
};
</script>

<template>
    <Head :title="trans('auth.login.title')" />
    
    <AuthBase
        :title="trans('auth.login.header.title')"
        :description="trans('auth.login.header.description')"
    >

        <form @submit.prevent="submit" class="flex flex-col gap-6">
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="email">{{ trans('auth.login.label.email') }}</Label>
                    <Input
                        id="email"
                        v-model="form.email"
                        type="email"
                        autofocus
                        :tabindex="1"
                        autocomplete="email"
                        :placeholder="trans('auth.login.placeholder.email')"
                    />
                    <InputError :message="form.errors.email" />
                </div>

                <div class="grid gap-2">
                    <div class="flex items-center justify-between">
                        <Label for="password">{{ trans('auth.login.label.password') }}</Label>
                        <TextLink
                            v-if="canResetPassword"
                            :href="request()"
                            class="text-sm"
                            :tabindex="5"
                        >
                            {{ trans('auth.login.link.forgot_password') }}
                        </TextLink>
                    </div>
                    <Input
                        id="password"
                        v-model="form.password"
                        type="password"
                        :tabindex="2"
                        autocomplete="current-password"
                        :placeholder="trans('auth.login.placeholder.password')"
                    />
                    <InputError :message="form.errors.password" />
                </div>

                <div class="flex items-center justify-between">
                    <Label for="remember" class="flex items-center space-x-3">
                        <Checkbox id="remember" v-model:checked="form.remember" :tabindex="3" />
                        <span>{{ trans('auth.login.helper_text.remember') }}</span>
                    </Label>
                </div>

                <Button
                    type="submit"
                    class="mt-4 w-full"
                    :tabindex="4"
                    :disabled="form.processing"
                    data-test="login-button"
                >
                    <Spinner v-if="form.processing" />
                    {{ trans('auth.login.button.submit') }}
                </Button>
            </div>

            <div
                class="text-center text-sm text-muted-foreground"
                v-if="canRegister"
            >
                {{ trans('auth.login.helper_text.no_account') }}
                <TextLink :href="register()" :tabindex="5">{{ trans('auth.login.link.sign_up') }}</TextLink>
            </div>
        </form>
    </AuthBase>
</template>
