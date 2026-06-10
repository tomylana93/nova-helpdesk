<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { useTrans } from '@/composables/useTrans';
import { create } from '@/routes/admin/master-data/departments';

defineProps<{
    canCreate: boolean;
    hasSelection: boolean;
}>();

const { trans } = useTrans();
</script>

<template>
    <div class="flex min-w-0 flex-1 items-center gap-2 sm:flex-none">
        <Button
            v-if="canCreate && !hasSelection"
            as-child
            class="min-w-0 flex-1 sm:flex-none"
        >
            <Link :href="create()" prefetch>
                {{ trans('admin.master_data.department.action.create') }}
            </Link>
        </Button>
    </div>
</template>
