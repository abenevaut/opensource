<template>
    <Head :title="title" />
    <Layout>
        <div class="flex min-h-full flex-col justify-center py-12 sm:px-6 lg:px-8">
            <div class="sm:mx-auto sm:w-full sm:max-w-md">
                <Logo class="mx-auto h-16 w-auto" />
                <h2
                    class="mt-6 text-center text-3xl font-bold tracking-tight text-gray-900 dark:text-gray-200"
                    v-text="title"
                />
                <slot name="subtitle" />
            </div>
            <slot name="alert" />
            <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
                <slot name="content" />
            </div>
        </div>
    </Layout>
</template>

<script setup>
import { Head } from '@inertiajs/inertia-vue3';
import Layout from '@/Layouts/BlankLayout.vue';
import Logo from '@/Components/LogoComponent.vue';

defineProps({
    title: {
        type: String,
        required: true,
    },
});
</script>
