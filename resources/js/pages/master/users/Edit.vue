<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import { Save, X } from 'lucide-vue-next';

import ContentHeader from '@/components/ContentHeader.vue';
import ContentWrapper from '@/components/ContentWrapper.vue';
import InputError from '@/components/InputError.vue';
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
import AppLayout from '@/layouts/AppLayout.vue';
import { index as usersIndex, update as usersUpdate } from '@/routes/master/users';
import type { EditableUser, UserForm, UserStatusOption } from '@/types';

const props = defineProps<{
    user: EditableUser;
    statusOptions: UserStatusOption[];
}>();

const form = useForm<UserForm>({
    name: props.user.name,
    email: props.user.email,
    status: props.user.status,
});

const submit = (): void => {
    form.submit(usersUpdate(props.user.id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="trans('user.title.edit')" />

    <AppLayout>
        <ContentWrapper>
            <ContentHeader
                :title="trans('user.header.edit.title')"
                :description="trans('user.header.edit.description')"
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
                        <Label for="status">{{ trans('user.label.status') }}</Label>
                        <Select
                            v-model="form.status"
                        >
                            <SelectTrigger id="status" class="w-full">
                                <SelectValue
                                    :placeholder="trans('user.label.status')"
                                />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="option in props.statusOptions"
                                    :key="option.value"
                                    :value="option.value"
                                >
                                    {{ option.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="form.errors.status" />
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2">
                    <Button type="submit" :disabled="form.processing">
                        <Save class="size-4" />
                        {{ trans('app.button.save') }}
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
