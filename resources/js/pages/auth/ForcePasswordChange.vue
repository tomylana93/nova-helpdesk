<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { useTrans } from '@/composables/useTrans';
import { update } from '@/routes/password/force';

defineOptions({
    layout: {
        title: 'Change password',
        description: 'Change your default password to continue',
    },
});

defineProps<{
    passwordRules: string;
}>();

const { trans } = useTrans();
</script>

<template>
    <Head :title="trans('auth.force_password.title')" />

    <Form
        v-bind="update.form()"
        :reset-on-success="['password', 'password_confirmation']"
        v-slot="{ errors, processing }"
    >
        <div class="grid gap-6">
            <div class="grid gap-2">
                <Label for="password">
                    {{ trans('auth.force_password.label.password') }}
                </Label>
                <PasswordInput
                    id="password"
                    name="password"
                    autocomplete="new-password"
                    class="mt-1 block w-full"
                    autofocus
                    :passwordrules="passwordRules"
                />
                <InputError :message="errors.password" />
            </div>

            <div class="grid gap-2">
                <Label for="password_confirmation">
                    {{
                        trans('auth.force_password.label.password_confirmation')
                    }}
                </Label>
                <PasswordInput
                    id="password_confirmation"
                    name="password_confirmation"
                    autocomplete="new-password"
                    class="mt-1 block w-full"
                    :passwordrules="passwordRules"
                />
                <InputError :message="errors.password_confirmation" />
            </div>

            <Button
                type="submit"
                class="mt-4 w-full"
                :disabled="processing"
                data-test="force-password-button"
            >
                <Spinner v-if="processing" />
                {{ trans('auth.force_password.action.submit') }}
            </Button>
        </div>
    </Form>
</template>
