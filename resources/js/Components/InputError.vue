<script setup>
defineProps({
    message: {
        type: String,
        default: '',
    },
});
</script>

<template>
    <div v-show="message" class="flex items-center gap-1.5 text-sm text-rose-600">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10" />
            <path d="M12 8v5M12 16h.01" stroke-linecap="round" />
        </svg>
        <p>{{ message }}</p>
    </div>
</template>
