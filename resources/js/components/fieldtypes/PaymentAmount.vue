<script setup>
import { Fieldtype } from '@statamic/cms';
import { Input, Select } from '@statamic/cms/ui';
import { computed } from 'vue';

const emit = defineEmits(Fieldtype.emits);
const props = defineProps(Fieldtype.props);
const { expose, isReadOnly, update } = Fieldtype.use(emit, props);
defineExpose(expose);

const currencyOptions = computed(() =>
    [...props.meta.currencies].sort().map((currency) => ({
        value: currency,
        label: currency,
    })),
);

const currencyChanged = (currency) => {
    update({ ...props.value, currency });
};

const valueChanged = (value) => {
    update({ ...props.value, value });
};
</script>

<template>
    <div class="flex gap-2" v-if="currencyOptions.length > 1 && !isReadOnly">
        <Select
            class="w-32"
            :model-value="value.currency"
            @update:model-value="currencyChanged"
            :options="currencyOptions"
        />
        <Input
            class="flex-1"
            :model-value="value.value"
            @update:model-value="valueChanged"
        />
    </div>
    <div v-else>
        <Input
            class="flex-1"
            :model-value="value.value"
            @update:model-value="valueChanged"
            :prepend="value.currency"
            :read-only="isReadOnly"
        />
    </div>
</template>
