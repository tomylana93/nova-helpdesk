<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { PlusCircle } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { useTrans } from '@/composables/useTrans';
import { create } from '@/routes/tickets';

type Props = {
    canCreate: boolean;
    hasSelection: boolean;
};

defineProps<Props>();

const { trans } = useTrans();
</script>

<template>
    <div class="flex items-center gap-2">
        <Button v-if="canCreate" :as="Link" :href="create()">
            <PlusCircle class="size-4" />
            {{ trans('helpdesk.ticket.action.create') }}
        </Button>
    </div>
</template>
