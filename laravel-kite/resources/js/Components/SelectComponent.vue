<template>
    <select
        class="appearance-none rounded-md border border-gray-300 px-3 py-2 placeholder-gray-400 shadow-sm focus:border-primary focus:outline-none focus:ring-primary sm:text-sm dark:bg-nosferatu-800 dark:text-neutral-300"
        v-bind="$attrs"
        :value="value"
        :disabled="isDisabled === true"
    >
        <option v-for="item in choices" :value="item">
            {{ item }}
        </option>
    </select>
</template>

<script setup>
defineProps({
    value: {
        type: [Object, String],
        required: true,
    },
    choices: {
        type: [Object, Array],
        required: true,
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
</script>
