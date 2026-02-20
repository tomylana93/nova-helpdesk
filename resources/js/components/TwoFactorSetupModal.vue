<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { useClipboard } from '@vueuse/core';
import { trans } from 'laravel-vue-i18n';
import { Check, Copy, ScanLine } from 'lucide-vue-next';
import { computed, nextTick, ref, useTemplateRef, watch } from 'vue';
import AlertError from '@/components/AlertError.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    InputOTP,
    InputOTPGroup,
    InputOTPSlot,
} from '@/components/ui/input-otp';
import { Spinner } from '@/components/ui/spinner';
import { useAppearance } from '@/composables/useAppearance';
import { useTwoFactorAuth } from '@/composables/useTwoFactorAuth';
import { confirm } from '@/routes/two-factor';
import type { TwoFactorConfigContent, TwoFactorConfirmFormData } from '@/types';

type Props = {
    requiresConfirmation: boolean;
    twoFactorEnabled: boolean;
};

const { resolvedAppearance } = useAppearance();

const props = defineProps<Props>();
const isOpen = defineModel<boolean>('isOpen');

const { copy, copied } = useClipboard();
const { qrCodeSvg, manualSetupKey, clearSetupData, fetchSetupData, errors } =
    useTwoFactorAuth();

const showVerificationStep = ref(false);
const confirmForm = useForm<TwoFactorConfirmFormData>({
    code: '',
});

const pinInputContainerRef = useTemplateRef('pinInputContainerRef');

const modalConfig = computed<TwoFactorConfigContent>(() => {
    if (props.twoFactorEnabled) {
        return {
            title: trans('two_factor.modal.enabled.title'),
            description: trans('two_factor.modal.enabled.description'),
            buttonText: trans('two_factor.modal.enabled.button_text'),
        };
    }

    if (showVerificationStep.value) {
        return {
            title: trans('two_factor.modal.verify.title'),
            description: trans('two_factor.modal.verify.description'),
            buttonText: trans('two_factor.modal.verify.button_text'),
        };
    }

    return {
        title: trans('two_factor.modal.setup.title'),
        description: trans('two_factor.modal.setup.description'),
        buttonText: trans('two_factor.modal.setup.button_text'),
    };
});

const handleModalNextStep = (): void => {
    if (props.requiresConfirmation) {
        showVerificationStep.value = true;

        nextTick(() => {
            pinInputContainerRef.value?.querySelector('input')?.focus();
        });

        return;
    }

    clearSetupData();
    isOpen.value = false;
};

const submitConfirmation = (): void => {
    confirmForm.submit(confirm(), {
        preserveScroll: true,
        onSuccess: () => {
            isOpen.value = false;
        },
        onFinish: () => {
            confirmForm.reset('code');
        },
    });
};

const resetModalState = (): void => {
    if (props.twoFactorEnabled) {
        clearSetupData();
    }

    showVerificationStep.value = false;
    confirmForm.reset('code');
    confirmForm.clearErrors();
};

watch(
    () => isOpen.value,
    async (opened) => {
        if (!opened) {
            resetModalState();
            return;
        }

        if (!qrCodeSvg.value) {
            await fetchSetupData();
        }
    },
);
</script>

<template>
    <Dialog :open="isOpen" @update:open="isOpen = $event">
        <DialogContent class="sm:max-w-md">
            <DialogHeader class="flex items-center justify-center">
                <div
                    class="mb-3 w-auto rounded-full border border-border bg-card p-0.5 shadow-sm"
                >
                    <div
                        class="relative overflow-hidden rounded-full border border-border bg-muted p-2.5"
                    >
                        <div
                            class="absolute inset-0 grid grid-cols-5 opacity-50"
                        >
                            <div
                                v-for="i in 5"
                                :key="`col-${i}`"
                                class="border-r border-border last:border-r-0"
                            />
                        </div>
                        <div
                            class="absolute inset-0 grid grid-rows-5 opacity-50"
                        >
                            <div
                                v-for="i in 5"
                                :key="`row-${i}`"
                                class="border-b border-border last:border-b-0"
                            />
                        </div>
                        <ScanLine
                            class="relative z-20 size-6 text-foreground"
                        />
                    </div>
                </div>
                <DialogTitle>{{ modalConfig.title }}</DialogTitle>
                <DialogDescription class="text-center">
                    {{ modalConfig.description }}
                </DialogDescription>
            </DialogHeader>

            <div
                class="relative flex w-auto flex-col items-center justify-center space-y-5"
            >
                <template v-if="!showVerificationStep">
                    <AlertError v-if="errors?.length" :errors="errors" />
                    <template v-else>
                        <div
                            class="relative mx-auto flex max-w-md items-center overflow-hidden"
                        >
                            <div
                                class="relative mx-auto aspect-square w-64 overflow-hidden rounded-lg border border-border"
                            >
                                <div
                                    v-if="!qrCodeSvg"
                                    class="absolute inset-0 z-10 flex aspect-square h-auto w-full animate-pulse items-center justify-center bg-background"
                                >
                                    <Spinner class="size-6" />
                                </div>
                                <div
                                    v-else
                                    class="relative z-10 overflow-hidden border p-5"
                                >
                                    <div
                                        v-html="qrCodeSvg"
                                        class="flex aspect-square size-full items-center justify-center"
                                        :style="{
                                            filter:
                                                resolvedAppearance === 'dark'
                                                    ? 'invert(1) brightness(1.5)'
                                                    : undefined,
                                        }"
                                    />
                                </div>
                            </div>
                        </div>

                        <div class="flex w-full items-center space-x-5">
                            <Button class="w-full" @click="handleModalNextStep">
                                {{ modalConfig.buttonText }}
                            </Button>
                        </div>

                        <div
                            class="relative flex w-full items-center justify-center"
                        >
                            <div
                                class="absolute inset-0 top-1/2 h-px w-full bg-border"
                            />
                            <span class="relative bg-card px-2 py-1">{{
                                trans('two_factor.modal.manual_entry_or')
                            }}</span>
                        </div>

                        <div
                            class="flex w-full items-center justify-center space-x-2"
                        >
                            <div
                                class="flex w-full items-stretch overflow-hidden rounded-xl border border-border"
                            >
                                <div
                                    v-if="!manualSetupKey"
                                    class="flex h-full w-full items-center justify-center bg-muted p-3"
                                >
                                    <Spinner />
                                </div>
                                <template v-else>
                                    <input
                                        type="text"
                                        readonly
                                        :value="manualSetupKey"
                                        class="h-full w-full bg-background p-3 text-foreground"
                                    />
                                    <button
                                        @click="copy(manualSetupKey || '')"
                                        class="relative block h-auto border-l border-border px-3 hover:bg-muted"
                                    >
                                        <Check
                                            v-if="copied"
                                            class="w-4 text-green-500"
                                        />
                                        <Copy v-else class="w-4" />
                                    </button>
                                </template>
                            </div>
                        </div>
                    </template>
                </template>

                <template v-else>
                    <form @submit.prevent="submitConfirmation">
                        <div
                            ref="pinInputContainerRef"
                            class="relative w-full space-y-3"
                        >
                            <div
                                class="flex w-full flex-col items-center justify-center space-y-3 py-2"
                            >
                                <InputOTP
                                    id="otp"
                                    v-model="confirmForm.code"
                                    :maxlength="6"
                                    :disabled="confirmForm.processing"
                                >
                                    <InputOTPGroup>
                                        <InputOTPSlot
                                            v-for="index in 6"
                                            :key="index"
                                            :index="index - 1"
                                        />
                                    </InputOTPGroup>
                                </InputOTP>
                                <InputError
                                    :message="confirmForm.errors.code"
                                />
                            </div>

                            <div class="flex w-full items-center space-x-5">
                                <Button
                                    type="button"
                                    variant="outline"
                                    class="w-auto flex-1"
                                    @click="showVerificationStep = false"
                                    :disabled="confirmForm.processing"
                                >
                                    {{ trans('two_factor.modal.button.back') }}
                                </Button>
                                <Button
                                    type="submit"
                                    class="w-auto flex-1"
                                    :disabled="
                                        confirmForm.processing ||
                                        confirmForm.code.length < 6
                                    "
                                >
                                    {{
                                        trans('two_factor.modal.button.confirm')
                                    }}
                                </Button>
                            </div>
                        </div>
                    </form>
                </template>
            </div>
        </DialogContent>
    </Dialog>
</template>
