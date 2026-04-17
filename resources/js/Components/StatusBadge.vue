<script setup>
import { computed } from 'vue';
import { useI18n } from '@/composables/useI18n';

const props = defineProps({
    status: {
        type: String,
        required: true,
    },
    small: {
        type: Boolean,
        default: false,
    },
});

const { t } = useI18n();

const palette = {
    open: 'bg-sky-50 text-sky-700 ring-sky-200',
    in_progress: 'bg-amber-50 text-amber-700 ring-amber-200',
    closed: 'bg-emerald-50 text-emerald-700 ring-emerald-200',
    pending: 'bg-amber-50 text-amber-700 ring-amber-200',
    paid: 'bg-emerald-50 text-emerald-700 ring-emerald-200',
    failed: 'bg-rose-50 text-rose-700 ring-rose-200',
};

const dot = {
    open: 'bg-sky-500',
    in_progress: 'bg-amber-500',
    closed: 'bg-emerald-500',
    pending: 'bg-amber-500',
    paid: 'bg-emerald-500',
    failed: 'bg-rose-500',
};

const badgeClass = computed(() => palette[props.status] ?? 'bg-slate-100 text-slate-700 ring-slate-200');
const dotClass = computed(() => dot[props.status] ?? 'bg-slate-500');
const label = computed(() => t(`status.${props.status}`, props.status));
</script>

<template>
    <span
        class="inline-flex items-center gap-1.5 rounded-full ring-1 ring-inset"
        :class="[
            badgeClass,
            small ? 'px-2 py-0.5 text-[11px] font-semibold' : 'px-2.5 py-1 text-xs font-semibold',
        ]"
    >
        <span class="h-1.5 w-1.5 rounded-full" :class="dotClass" />
        {{ label }}
    </span>
</template>
