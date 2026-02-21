<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import AppHead from '@/components/AppHead.vue';

import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthBase from '@/layouts/AuthLayout.vue';
import { login } from '@/routes';
import { store } from '@/routes/register';
import type { RegisterFormData } from '@/types';

const form = useForm<RegisterFormData>({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = (): void => {
    form.submit(store(), {
        onSuccess: () => {
            form.reset('password', 'password_confirmation');
        },
    });
};
</script>

<template>
    <AppHead :title="trans('auth.register.title')" />

    <AuthBase
        :title="trans('auth.register.header.title')"
        :description="trans('auth.register.header.description')"
    >
        <form @submit.prevent="submit" class="flex flex-col gap-6">
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="name">{{
                        trans('auth.register.label.name')
                    }}</Label>
                    <Input
                        id="name"
                        v-model="form.name"
                        type="text"
                        autofocus
                        :tabindex="1"
                        autocomplete="name"
                        :placeholder="trans('auth.register.placeholder.name')"
                    />
                    <InputError :message="form.errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="email">{{
                        trans('auth.register.label.email')
                    }}</Label>
                    <Input
                        id="email"
                        v-model="form.email"
                        type="email"
                        :tabindex="2"
                        autocomplete="email"
                        :placeholder="trans('auth.register.placeholder.email')"
                    />
                    <InputError :message="form.errors.email" />
                </div>

                <div class="grid gap-2">
                    <Label for="password">{{
                        trans('auth.register.label.password')
                    }}</Label>
                    <Input
                        id="password"
                        v-model="form.password"
                        type="password"
                        :tabindex="3"
                        autocomplete="new-password"
                        :placeholder="
                            trans('auth.register.placeholder.password')
                        "
                    />
                    <InputError :message="form.errors.password" />
                </div>

                <div class="grid gap-2">
                    <Label for="password_confirmation">{{
                        trans('auth.register.label.password_confirmation')
                    }}</Label>
                    <Input
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        type="password"
                        :tabindex="4"
                        autocomplete="new-password"
                        :placeholder="
                            trans(
                                'auth.register.placeholder.password_confirmation',
                            )
                        "
                    />
                    <InputError :message="form.errors.password_confirmation" />
                </div>

                <Button
                    type="submit"
                    class="mt-2 w-full"
                    :tabindex="5"
                    :disabled="form.processing"
                    data-test="register-user-button"
                >
                    <Spinner v-if="form.processing" />
                    {{ trans('auth.register.button.submit') }}
                </Button>
            </div>

            <div class="text-center text-sm text-muted-foreground">
                {{ trans('auth.register.helper_text.have_account') }}
                <TextLink
                    :href="login()"
                    class="underline underline-offset-4"
                    :tabindex="6"
                    >{{ trans('auth.register.link.log_in') }}</TextLink
                >
            </div>
        </form>
    </AuthBase>
</template>
