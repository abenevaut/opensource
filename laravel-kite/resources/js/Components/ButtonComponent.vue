<template>
    <button
        class="flex py-2 px-4 justify-center rounded-md border border-transparent text-sm font-medium shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2"
        :class="[{
            'bg-primary text-white hover:bg-primary-300 focus:ring-primary': variant === 'primary' && !isOutlined
        }, {
            'bg-primary text-white hover:bg-primary-300 focus:ring-primary': variant === 'primary' && isOutlined
        }, {
            'opacity-25': isDisabled
        }]"
        v-bind="$attrs"
        :disabled="isDisabled === true || isLoading === true"
    >
        <i
            v-if="isLoading === true && variant === 'primary'"
            class="flex"
        >
            <svg
                class="animate-spin h-5 w-5 mr-2"
                :class="[{
                  'text-white': !isOutlined
                },  {
                  'text-primary-100': isOutlined
                }]"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
            >
                <circle
                    class="opacity-25"
                    cx="12"
                    cy="12"
                    r="10"
                    stroke="currentColor"
                    stroke-width="4"
                />
                <path
                    class="opacity-75"
                    fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                />
            </svg>
        </i>

        <!--        <i-->
        <!--            v-else-if="iconLeft !== false"-->
        <!--            class="flex"-->
        <!--        >-->
        <!--            <base-icon-->
        <!--                :type="iconLeft.type"-->
        <!--                :name="iconLeft.name"-->
        <!--                :size="iconLeft.size"-->
        <!--            />-->
        <!--        </i>-->

        <span
            v-if="value.length > 0"
            v-text="value"
        ></span>

        <!--        <i-->
        <!--            v-if="iconRight !== false"-->
        <!--            class="flex"-->
        <!--        >-->
        <!--            <base-icon-->
        <!--                :type="iconRight.type"-->
        <!--                :name="iconRight.name"-->
        <!--                :size="iconRight.size"-->
        <!--            />-->
        <!--        </i>-->
    </button>
</template>

<script setup>
import { includes } from 'lodash';

defineProps({
    value: {
        type: String,
        default: '',
    },
    variant: {
        type: String,
        default: 'primary',
        validator(value) {
            return includes(['primary'], value);
        },
    },
    isLoading: {
        type: Boolean,
        default: false,
    },
    isDisabled: {
        type: Boolean,
        default: false,
    },
    isOutlined: {
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
