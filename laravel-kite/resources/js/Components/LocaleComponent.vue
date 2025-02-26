<template>
    <div>
        <Label
            for="locale"
            class="block"
            value="Language"
            variant="input-label"
        />
        <div class="mt-1">
            <Select
                id="locale"
                class="block w-full"
                :value="$i18n.locale"
                :choices="usePage().props.value.app.locales"
                @change="setLocale($event.target.value)"
            />
        </div>
    </div>
</template>

<script setup>
import { usePage } from '@inertiajs/inertia-vue3';
import { setLocale } from '@/bootstrap.js';
import Select from '@/Components/SelectComponent.vue';
import Label from '@/Components/LabelComponent.vue';
</script>
