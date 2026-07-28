<script setup>
const props = defineProps({
    src: {
        type: String,
        default: '/images/aska-logo.png',
    },
    alt: {
        type: String,
        default: 'ASKA logo',
    },
});
</script>

<template>
    <img
        :src="props.src"
        :alt="props.alt"
        class="block w-auto max-w-full object-contain"
        style="height: auto; max-height: 64px;"
    />
</template>
