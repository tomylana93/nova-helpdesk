<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import { ShieldBan, ShieldCheck } from 'lucide-vue-next';
import { onUnmounted, ref } from 'vue';

import Heading from '@/components/Heading.vue';
import TwoFactorRecoveryCodes from '@/components/TwoFactorRecoveryCodes.vue';
import TwoFactorSetupModal from '@/components/TwoFactorSetupModal.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { useTwoFactorAuth } from '@/composables/useTwoFactorAuth';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/SettingsLayout.vue';
import { disable, enable } from '@/routes/two-factor';
import type { EmptyFormData } from '@/types';

type Props = {
    requiresConfirmation?: boolean;
    twoFactorEnabled?: boolean;
};

withDefaults(defineProps<Props>(), {
    requiresConfirmation: false,
    twoFactorEnabled: false,
});

const { hasSetupData, clearTwoFactorAuthData } = useTwoFactorAuth();
const showSetupModal = ref<boolean>(false);
const enableForm = useForm<EmptyFormData>({});
const disableForm = useForm<EmptyFormData>({});

const submitEnable = (): void => {
    enableForm.submit(enable(), {
        onSuccess: () => {
            showSetupModal.value = true;
        },
    });
};

const submitDisable = (): void => {
    disableForm.submit(disable(), {
        preserveScroll: true,
    });
};

onUnmounted(() => {
    clearTwoFactorAuthData();
});
</script>

<template>
    <AppLayout>
        <Head :title="trans('two_factor.title')" />

        <h1 class="sr-only">{{ trans('two_factor.sr_only_title') }}</h1>

        <SettingsLayout>
            <div class="space-y-6">
                <Heading
                    variant="small"
                    :title="trans('two_factor.header.title')"
                    :description="trans('two_factor.header.description')"
                />

                <div
                    v-if="!twoFactorEnabled"
                    class="flex flex-col items-start justify-start space-y-4"
                >
                    <Badge variant="destructive">{{
                        trans('two_factor.badge.disabled')
                    }}</Badge>

                    <p class="text-muted-foreground">
                        {{
                            trans('two_factor.helper_text.disabled_description')
                        }}
                    </p>

                    <div>
                        <Button
                            v-if="hasSetupData"
                            @click="showSetupModal = true"
                        >
                            <ShieldCheck />{{
                                trans('two_factor.button.continue_setup')
                            }}
                        </Button>
                        <form v-else @submit.prevent="submitEnable">
                            <Button
                                type="submit"
                                :disabled="enableForm.processing"
                            >
                                <ShieldCheck />{{
                                    trans('two_factor.button.enable')
                                }}
                            </Button>
                        </form>
                    </div>
                </div>

                <div
                    v-else
                    class="flex flex-col items-start justify-start space-y-4"
                >
                    <Badge variant="default">{{
                        trans('two_factor.badge.enabled')
                    }}</Badge>

                    <p class="text-muted-foreground">
                        {{
                            trans('two_factor.helper_text.enabled_description')
                        }}
                    </p>

                    <TwoFactorRecoveryCodes />

                    <div class="relative inline">
                        <form @submit.prevent="submitDisable">
                            <Button
                                variant="destructive"
                                type="submit"
                                :disabled="disableForm.processing"
                            >
                                <ShieldBan />
                                {{ trans('two_factor.button.disable') }}
                            </Button>
                        </form>
                    </div>
                </div>

                <TwoFactorSetupModal
                    v-model:isOpen="showSetupModal"
                    :requiresConfirmation="requiresConfirmation"
                    :twoFactorEnabled="twoFactorEnabled"
                />
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
