<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import { Eye, EyeOff, LockKeyhole, RefreshCw } from 'lucide-vue-next';
import { nextTick, onMounted, ref, useTemplateRef } from 'vue';

import AlertError from '@/components/AlertError.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { useTwoFactorAuth } from '@/composables/useTwoFactorAuth';
import { regenerateRecoveryCodes } from '@/routes/two-factor';
import type { EmptyFormData } from '@/types';

const { recoveryCodesList, fetchRecoveryCodes, errors } = useTwoFactorAuth();
const isRecoveryCodesVisible = ref<boolean>(false);
const recoveryCodeSectionRef = useTemplateRef('recoveryCodeSectionRef');
const regenerateForm = useForm<EmptyFormData>({});

const toggleRecoveryCodesVisibility = async (): Promise<void> => {
    if (!isRecoveryCodesVisible.value && !recoveryCodesList.value.length) {
        await fetchRecoveryCodes();
    }

    isRecoveryCodesVisible.value = !isRecoveryCodesVisible.value;

    if (isRecoveryCodesVisible.value) {
        await nextTick();
        recoveryCodeSectionRef.value?.scrollIntoView({ behavior: 'smooth' });
    }
};

const submitRegenerate = (): void => {
    regenerateForm.submit(regenerateRecoveryCodes(), {
        preserveScroll: true,
        onSuccess: () => {
            void fetchRecoveryCodes();
        },
    });
};

onMounted(async () => {
    if (!recoveryCodesList.value.length) {
        await fetchRecoveryCodes();
    }
});
</script>

<template>
    <Card class="w-full">
        <CardHeader>
            <CardTitle class="flex gap-3">
                <LockKeyhole class="size-4" />{{
                    trans('two_factor.recovery.title')
                }}
            </CardTitle>
            <CardDescription>
                {{ trans('two_factor.recovery.description') }}
            </CardDescription>
        </CardHeader>
        <CardContent>
            <div
                class="flex flex-col gap-3 select-none sm:flex-row sm:items-center sm:justify-between"
            >
                <Button @click="toggleRecoveryCodesVisibility" class="w-fit">
                    <component
                        :is="isRecoveryCodesVisible ? EyeOff : Eye"
                        class="size-4"
                    />
                    {{
                        isRecoveryCodesVisible
                            ? trans('two_factor.recovery.button.hide')
                            : trans('two_factor.recovery.button.view')
                    }}
                    {{ trans('two_factor.recovery.button.codes') }}
                </Button>

                <form
                    v-if="isRecoveryCodesVisible && recoveryCodesList.length"
                    @submit.prevent="submitRegenerate"
                >
                    <Button
                        variant="secondary"
                        type="submit"
                        :disabled="regenerateForm.processing"
                    >
                        <RefreshCw />
                        {{ trans('two_factor.recovery.button.regenerate') }}
                    </Button>
                </form>
            </div>
            <div
                :class="[
                    'relative overflow-hidden transition-all duration-300',
                    isRecoveryCodesVisible
                        ? 'h-auto opacity-100'
                        : 'h-0 opacity-0',
                ]"
            >
                <div v-if="errors?.length" class="mt-6">
                    <AlertError :errors="errors" />
                </div>
                <div v-else class="mt-3 space-y-3">
                    <div
                        ref="recoveryCodeSectionRef"
                        class="grid gap-1 rounded-lg bg-muted p-4 font-mono text-sm"
                    >
                        <div v-if="!recoveryCodesList.length" class="space-y-2">
                            <div
                                v-for="n in 8"
                                :key="n"
                                class="h-4 animate-pulse rounded bg-muted-foreground/20"
                            ></div>
                        </div>
                        <div
                            v-else
                            v-for="(code, index) in recoveryCodesList"
                            :key="index"
                        >
                            {{ code }}
                        </div>
                    </div>
                    <p class="text-xs text-muted-foreground select-none">
                        {{ trans('two_factor.recovery.helper_text') }}
                    </p>
                </div>
            </div>
        </CardContent>
    </Card>
</template>
