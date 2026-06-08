<script setup lang="ts">
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import { statusClasses } from '@/lib/utils';
import type { UserStatus } from '@/types';

const props = defineProps<{
    label: string;
    status: UserStatus;
}>();

const badgeClass = computed(() => statusClasses(props.status));
</script>

<template>
    <Badge variant="outline" :class="badgeClass">
        {{ label }}
    </Badge>
</template>
