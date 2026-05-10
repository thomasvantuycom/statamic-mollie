<script setup>
import { IndexFieldtype } from '@statamic/cms';
import { computed } from 'vue';

const emit = defineEmits(IndexFieldtype.emits);
const props = defineProps(IndexFieldtype.props);
const { expose } = IndexFieldtype.use(emit, props);
defineExpose(expose);

const tooltip = computed(() => {
    if (!props.value) {
        return null;
    }

    const tooltips = {
        open: __('Open'),
        pending: __('Pending'),
        authorized: __('Authorized'),
        paid: __('Paid'),
        canceled: __('Canceled'),
        expired: __('Expired'),
        failed: __('Failed'),
    };

    return tooltips[props.value.status];
});

const colorClass = computed(() => {
    if (!props.value) {
        return null;
    }

    const colorClasses = {
        open: 'bg-yellow-400',
        pending: 'bg-yellow-400',
        authorized: 'bg-yellow-400',
        paid: 'bg-green-400',
        canceled: 'bg-red-400',
        expired: 'bg-red-400',
        failed: 'bg-red-400',
    };

    return colorClasses[props.value.status];
});

const amount = computed(() => {
    if (!props.value) {
        return null;
    }

    return `${props.value.amount?.currency} ${props.value.amount?.value}`;
});
</script>

<template>
    <div class="flex items-center gap-2">
        <span
            class="size-2 rounded-full"
            :class="colorClass"
            v-tooltip="tooltip"
        />
        <span v-text="amount" />
    </div>
</template>
