<template>
    <div
        v-bind="$attrs"
        v-show="isVisible && value"
    >
        <div
            class="p-4 rounded-md shadow"
            :class="[{
                'bg-blue-50': variant === 'info',
                'bg-green-50': variant === 'success',
                'bg-yellow-50': variant === 'warning',
                'bg-red-50': variant === 'error',
            }]"
        >
            <div class="flex">
                <div class="flex-shrink-0">
                    <InformationCircleIcon
                        class="h-5 w-5 text-blue-400"
                        aria-hidden="true"
                        v-if="variant === 'info'"
                    />
                    <CheckCircleIcon
                        class="h-5 w-5 text-green-400"
                        aria-hidden="true"
                        v-if="variant === 'success'"
                    />
                    <ExclamationTriangleIcon
                        class="h-5 w-5 text-yellow-400"
                        aria-hidden="true"
                        v-if="variant === 'warning'"
                    />
                    <StopCircleIcon
                        class="h-5 w-5 text-red-400"
                        aria-hidden="true"
                        v-if="variant === 'error'"
                    />
                </div>
                <div class="ml-3">
                    <p
                        class="text-sm font-medium"
                        :class="[{
                            'text-blue-800': variant === 'info',
                            'text-green-800': variant === 'success',
                            'text-yellow-800': variant === 'warning',
                            'text-red-800': variant === 'error',
                        }]"
                        v-text="value"
                    />
                </div>
                <div
                    class="ml-auto pl-3"
                    v-if="isDismissible"
                >
                    <div class="-mx-1.5 -my-1.5">
                        <button
                            type="button"
                            class="inline-flex rounded-md p-1.5 focus:outline-none focus:ring-2 focus:ring-offset-2"
                            :class="[{
                                'bg-blue-50 text-blue-500 hover:bg-blue-100 focus:ring-blue-600 focus:ring-offset-blue-50': variant === 'info',
                                'bg-green-50 text-green-500 hover:bg-green-100 focus:ring-green-600 focus:ring-offset-green-50': variant === 'success',
                                'bg-yellow-50 text-yellow-500 hover:bg-yellow-100 focus:ring-yellow-600 focus:ring-offset-yellow-50': variant === 'warning',
                                'bg-red-50 text-red-500 hover:bg-red-100 focus:ring-red-600 focus:ring-offset-red-50': variant === 'error',
                            }]"
                            @click="isVisible = false"
                        >
                            <span class="sr-only">Dismiss</span>
                            <XMarkIcon class="h-5 w-5" aria-hidden="true" />
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { includes } from 'lodash';
import { ref } from 'vue';
import { InformationCircleIcon, CheckCircleIcon, ExclamationTriangleIcon, StopCircleIcon, XMarkIcon } from '@heroicons/vue/20/solid';

const isVisible = ref(true);

defineProps({
    value: {
        type: String,
        required: true,
    },
    variant: {
        type: String,
        default: 'info',
        validator(value) {
            return includes(['info', 'success', 'warning', 'error'], value);
        },
    },
    isDismissible: {
        type: Boolean,
        default: false,
    }
});
</script>
