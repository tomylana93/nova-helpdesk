<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import type { HTMLAttributes } from 'vue';
import { computed } from 'vue';
import { cn } from '@/lib/utils';
import type { SharedPageProps } from '@/types';

type Props = {
    class?: HTMLAttributes['class'];
    imageClass?: HTMLAttributes['class'];
    nameClass?: HTMLAttributes['class'];
};

const props = defineProps<Props>();
const page = usePage<SharedPageProps>();

const brandName = computed(() => page.props.name);
const style = computed(() => page.props.style);
const branding = computed(() => page.props.branding);
const isLogoMode = computed(() => style.value.site_logo_style === 'logo');
const lightAssetSource = computed(() =>
    isLogoMode.value ? branding.value.logo : branding.value.icon,
);
const darkAssetSource = computed(() =>
    isLogoMode.value ? branding.value.logo_alt : branding.value.icon_alt,
);
</script>

<template>
    <div :class="cn('flex items-center gap-2', props.class)">
        <img
            :src="lightAssetSource"
            :alt="brandName"
            :class="
                cn(
                    isLogoMode
                        ? 'h-9 w-auto max-w-[180px] object-contain dark:hidden'
                        : 'size-9 object-contain dark:hidden',
                    props.imageClass,
                )
            "
        />
        <img
            :src="darkAssetSource"
            :alt="brandName"
            :class="
                cn(
                    isLogoMode
                        ? 'hidden h-9 w-auto max-w-[180px] object-contain dark:block'
                        : 'hidden size-9 object-contain dark:block',
                    props.imageClass,
                )
            "
        />

        <span
            v-if="!isLogoMode"
            :class="
                cn(
                    'truncate text-sm leading-tight font-semibold',
                    props.nameClass,
                )
            "
        >
            {{ brandName }}
        </span>
    </div>
</template>
