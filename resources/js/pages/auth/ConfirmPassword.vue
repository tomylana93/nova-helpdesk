<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { store } from '@/routes/password/confirm';

type ConfirmPasswordFormData = {
    password: string;
};

defineOptions({
    layout: {
        title: 'Confirm your password',
        description:
            'This is a secure area of the application. Please confirm your password before continuing.',
    },
});

const form = useForm<ConfirmPasswordFormData>({
    password: '',
});

function submit(): void {
    form.submit(store(), {
        onSuccess: () => {
            form.resetAndClearErrors();
        },
    });
}
</script>

<template>
    <Head title="Confirm password" />

    <form @submit.prevent="submit">
        <div class="space-y-6">
            <div class="grid gap-2">
                <Label htmlFor="password">Password</Label>
                <PasswordInput
                    id="password"
                    name="password"
                    v-model="form.password"
                    class="mt-1 block w-full"
                    required
                    autocomplete="current-password"
                    autofocus
                />

                <InputError :message="form.errors.password" />
            </div>

            <div class="flex items-center">
                <Button
                    class="w-full"
                    :disabled="form.processing"
                    data-test="confirm-password-button"
                >
                    <Spinner v-if="form.processing" />
                    Confirm password
                </Button>
            </div>
        </div>
    </form>
</template>
