<script setup lang="ts">
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { useInitials } from '@/composables/useInitials';

type Props = {
    avatar?: string;
    name: string;
};

const props = defineProps<Props>();

const { getInitials } = useInitials();
</script>

<template>
    <Avatar class="size-8 overflow-hidden rounded-lg">
        <AvatarImage
            v-if="props.avatar"
            :src="props.avatar"
            :alt="props.name"
        />
        <AvatarFallback class="rounded-lg text-black dark:text-white">
            {{ getInitials(props.name) }}
        </AvatarFallback>
    </Avatar>
</template>
