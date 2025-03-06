<template>
    <Head>
        <title>abenevaut.dev</title>
        <link rel="icon" type="image/svg+xml" :href="logo" />
    </Head>
    <slot/>
</template>

<script setup>
import { onMounted } from 'vue';
import { useDark } from '@vueuse/core';
import { Head } from '@inertiajs/inertia-vue3';
import Logo from '@abenevaut/maskot-2013/dist/app-icon.webp';

const logo = Logo;

onMounted(() => {
    useDark();
});
</script>
