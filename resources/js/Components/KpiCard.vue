<script setup>
import { computed } from 'vue';

const props = defineProps({
    title: {
        type: String,
        required: true,
    },
    value: {
        type: [String, Number],
        required: true,
    },
    hint: {
        type: String,
        default: '',
    },
    tone: {
        type: String,
        default: 'neutral',
    },
});

const toneClass = computed(() => {
    switch (props.tone) {
        case 'brand':
            return 'border-blue-100 bg-gradient-to-br from-blue-50 to-white';
        case 'success':
            return 'border-emerald-100 bg-gradient-to-br from-emerald-50 to-white';
        case 'warning':
            return 'border-amber-100 bg-gradient-to-br from-amber-50 to-white';
        case 'danger':
            return 'border-rose-100 bg-gradient-to-br from-rose-50 to-white';
        default:
            return 'border-slate-200/80 bg-white';
    }
});
</script>

<template>
    <article class="surface-card panel-hover reveal p-5" :class="toneClass">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ title }}</p>
                <p class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ value }}</p>
                <p v-if="hint" class="mt-2 text-xs text-slate-500">{{ hint }}</p>
            </div>
            <div class="mt-1">
                <slot name="icon" />
            </div>
        </div>
    </article>
</template>
