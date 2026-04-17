<script setup>
defineProps({
    title: {
        type: String,
        required: true,
    },
    description: {
        type: String,
        default: '',
    },
});
</script>

<template>
    <div class="surface-card-soft flex flex-col items-center justify-center rounded-2xl border-dashed px-6 py-10 text-center">
        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-500">
            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M4 7h16M4 12h16M4 17h9" stroke-linecap="round" />
            </svg>
        </div>
        <h4 class="mt-4 text-base font-semibold text-slate-900">{{ title }}</h4>
        <p v-if="description" class="mt-1 max-w-md text-sm text-slate-500">{{ description }}</p>
        <div v-if="$slots.actions" class="mt-4">
            <slot name="actions" />
        </div>
    </div>
</template>
