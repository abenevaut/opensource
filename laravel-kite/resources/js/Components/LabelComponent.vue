<template>
    <label
        class="font-medium"
        :class="[{
            'text-sm text-gray-700 dark:text-gray-200': variant === 'input-label'
        }, {
            'font-light text-xs italic text-red-500': variant === 'error-label'
        }]"
        v-bind="$attrs"
        v-text="value"
    />
</template>

<script setup>
import { includes } from "lodash";

defineProps({
    value: {
        type: [Object, String],
        required: true,
    },
    variant: {
        type: String,
        default: 'input-label',
        validator(value) {
            return includes(['input-label', 'error-label'], value);
        },
    },
});
</script>
