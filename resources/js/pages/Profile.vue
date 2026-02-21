<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';

import DeleteUser from '@/components/DeleteUser.vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/SettingsLayout.vue';
import { update } from '@/routes/profile';
import { send } from '@/routes/verification';
import { type ProfileFormData } from '@/types';

type Props = {
    mustVerifyEmail: boolean;
    status?: string;
};

defineProps<Props>();

const page = usePage();
const user = page.props.auth.user;

const form = useForm<ProfileFormData>({
    name: user.name,
    email: user.email,
});

const submit = (): void => {
    form.submit(update(), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout>
        <Head :title="trans('profile.title')" />

        <h1 class="sr-only">{{ trans('profile.sr_only_title') }}</h1>

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <Heading
                    variant="small"
                    :title="trans('profile.header.title')"
                    :description="trans('profile.header.description')"
                />

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid gap-2">
                        <Label for="name">{{
                            trans('profile.label.name')
                        }}</Label>
                        <Input
                            id="name"
                            v-model="form.name"
                            class="mt-1 block w-full"
                            autocomplete="name"
                            :placeholder="trans('profile.placeholder.name')"
                        />
                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">{{
                            trans('profile.label.email')
                        }}</Label>
                        <Input
                            id="email"
                            v-model="form.email"
                            type="email"
                            class="mt-1 block w-full"
                            autocomplete="username"
                            :placeholder="trans('profile.placeholder.email')"
                        />
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <div v-if="mustVerifyEmail && !user.email_verified_at">
                        <p class="-mt-4 text-sm text-muted-foreground">
                            {{ trans('profile.helper_text.unverified_email') }}
                            <Link
                                :href="send()"
                                as="button"
                                class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                            >
                                {{ trans('profile.link.resend_verification') }}
                            </Link>
                        </p>

                        <div
                            v-if="status === 'verification-link-sent'"
                            class="mt-2 text-sm font-medium text-green-600"
                        >
                            {{ trans('profile.helper_text.verification_sent') }}
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <Button
                            type="submit"
                            :disabled="form.processing"
                            data-test="update-profile-button"
                        >
                            {{ trans('profile.button.save') }}
                        </Button>

                        <Transition
                            enter-active-class="transition ease-in-out"
                            enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out"
                            leave-to-class="opacity-0"
                        >
                            <p
                                v-show="form.recentlySuccessful"
                                class="text-sm text-neutral-600"
                            >
                                {{ trans('profile.helper_text.saved') }}
                            </p>
                        </Transition>
                    </div>
                </form>
            </div>

            <DeleteUser />
        </SettingsLayout>
    </AppLayout>
</template>
