<template>
    <input
        class="appearance-none rounded-md border border-gray-300 px-3 py-2 placeholder-gray-400 shadow-sm focus:border-primary focus:outline-none focus:ring-primary sm:text-sm dark:bg-nosferatu-800 dark:text-neutral-300"
        v-bind="$attrs"
        v-model="inputValue"
        @input="handleInput"
        :type="type"
    />
</template>

<script setup>
import { includes } from 'lodash';
import { ref } from 'vue';

defineProps({
    modelValue: {
        type: [Object, String],
        required: true,
    },
    type: {
        type: String,
        default: 'text',
        validator(value) {
            return includes(['text', 'password'], value);
        },
    },
    isDisabled: {
        type: Boolean,
        default: false,
    },
// iconLeft: {
//   type: [Object, Boolean],
//   default: false,
// },
// iconRight: {
//   type: [Object, Boolean],
//   default: false,
// },
});

const inputValue = ref('');
const emit = defineEmits(['update:modelValue']);
const handleInput = () => emit('update:modelValue', inputValue.value);
</script>
