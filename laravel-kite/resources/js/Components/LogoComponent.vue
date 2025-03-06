<template>
    <Link
        :href="route('home')"
    >
        <img
            :src="logo"
            :alt="usePage().props.value.name"
            v-bind="$attrs"
        />
    </Link>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/inertia-vue3';
import Logo from '@abenevaut/maskot-2013/dist/app-icon.webp';

const logo = Logo;
</script>
