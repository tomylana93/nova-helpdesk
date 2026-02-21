<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import { PlusSquare, X } from 'lucide-vue-next';

import ContentHeader from '@/components/ContentHeader.vue';
import ContentWrapper from '@/components/ContentWrapper.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { index as usersIndex, store as usersStore } from '@/routes/master/users';
import type { UserForm } from '@/types';

const form = useForm<UserForm>({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = (): void => {
    form.submit(usersStore(), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="trans('user.title.create')" />

    <AppLayout>
        <ContentWrapper>
            <ContentHeader
                :title="trans('user.header.create.title')"
                :description="trans('user.header.create.description')"
            />

            <form @submit.prevent="submit" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="grid gap-2">
                        <Label for="name">{{ trans('user.label.name') }}</Label>
                        <Input
                            id="name"
                            v-model="form.name"
                            autocomplete="name"
                        />
                        <InputError :message="form.errors.name" />
                    </div>
    
                    <div class="grid gap-2">
                        <Label for="email">{{ trans('user.label.email') }}</Label>
                        <Input
                            id="email"
                            v-model="form.email"
                            type="email"
                            autocomplete="email"
                        />
                        <InputError :message="form.errors.email" />
                    </div>
    
                    <div class="grid gap-2">
                        <Label for="password">{{
                            trans('user.label.password')
                        }}</Label>
                        <Input
                            id="password"
                            v-model="form.password"
                            type="password"
                            autocomplete="new-password"
                        />
                        <InputError :message="form.errors.password" />
                    </div>
    
                    <div class="grid gap-2">
                        <Label for="password_confirmation">{{
                            trans('user.label.password_confirmation')
                        }}</Label>
                        <Input
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            type="password"
                            autocomplete="new-password"
                        />
                        <InputError :message="form.errors.password_confirmation" />
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2">
                    <Button type="submit" :disabled="form.processing">
                        <PlusSquare class="size-4" />
                        {{ trans('app.button.create') }}
                    </Button>
                    <Button :as="Link" :href="usersIndex()" variant="outline" type="button">
                        <X class="size-4" />
                        {{ trans('app.button.cancel') }}
                    </Button>
                </div>
            </form>
        </ContentWrapper>
    </AppLayout>
</template>
