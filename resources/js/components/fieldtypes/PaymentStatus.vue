<script setup>
import { Fieldtype } from '@statamic/cms';
import { Badge } from '@statamic/cms/ui';
import { computed } from 'vue';

const emit = defineEmits(Fieldtype.emits);
const props = defineProps(Fieldtype.props);
const { expose } = Fieldtype.use(emit, props);
defineExpose(expose);

const text = computed(() => {
    if (!props.value) {
        return null;
    }

    const texts = {
        open: __('Open'),
        pending: __('Pending'),
        authorized: __('Authorized'),
        paid: __('Paid'),
        canceled: __('Canceled'),
        expired: __('Expired'),
        failed: __('Failed'),
    };

    return texts[props.value];
});

const color = computed(() => {
    if (!props.value) {
        return null;
    }

    const colors = {
        open: 'yellow',
        pending: 'yellow',
        authorized: 'yellow',
        paid: 'green',
        canceled: 'red',
        expired: 'red',
        failed: 'red',
    };

    return colors[props.value];
});
</script>

<template>
    <Badge :text :color size="lg" />
</template>
