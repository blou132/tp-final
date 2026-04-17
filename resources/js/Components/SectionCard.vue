<script setup>
defineProps({
    title: {
        type: String,
        default: '',
    },
    description: {
        type: String,
        default: '',
    },
    padded: {
        type: Boolean,
        default: true,
    },
});
</script>

<template>
    <section class="surface-card panel-hover reveal" :class="padded ? 'p-5 sm:p-6' : ''">
        <header v-if="title || description || $slots.actions" class="mb-4 flex flex-wrap items-start justify-between gap-3">
            <div>
                <h3 v-if="title" class="text-base font-semibold text-slate-900">{{ title }}</h3>
                <p v-if="description" class="mt-1 text-sm text-slate-500">{{ description }}</p>
            </div>
            <div v-if="$slots.actions">
                <slot name="actions" />
            </div>
        </header>

        <slot />
    </section>
</template>
