<script setup>
import { computed } from 'vue';
import { useI18n } from '@/composables/useI18n';

const props = defineProps({
    search: {
        type: String,
        default: '',
    },
    status: {
        type: String,
        default: '',
    },
    sort: {
        type: String,
        default: '',
    },
    statuses: {
        type: Array,
        default: () => [],
    },
    sortOptions: {
        type: Array,
        default: () => [],
    },
    searchPlaceholder: {
        type: String,
        default: '',
    },
});

const emit = defineEmits(['update:search', 'update:status', 'update:sort', 'apply', 'reset']);
const { t } = useI18n();

const searchModel = computed({
    get: () => props.search,
    set: (value) => emit('update:search', value),
});

const statusModel = computed({
    get: () => props.status,
    set: (value) => emit('update:status', value),
});

const sortModel = computed({
    get: () => props.sort,
    set: (value) => emit('update:sort', value),
});
</script>

<template>
    <div class="surface-card reveal p-4 sm:p-5">
        <div class="grid gap-3 lg:grid-cols-[minmax(0,1fr)_220px_220px_auto_auto] lg:items-end">
            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">
                    {{ t('common.search') }}
                </label>
                <div class="relative">
                    <svg
                        class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                    >
                        <circle cx="11" cy="11" r="7" />
                        <path d="m20 20-3.5-3.5" stroke-linecap="round" />
                    </svg>
                    <input v-model="searchModel" type="text" class="input-base pl-9" :placeholder="searchPlaceholder" />
                </div>
            </div>

            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">
                    {{ t('common.status') }}
                </label>
                <select v-model="statusModel" class="select-base">
                    <option value="">{{ t('common.all') }}</option>
                    <option v-for="item in statuses" :key="item" :value="item">
                        {{ t(`status.${item}`, item) }}
                    </option>
                </select>
            </div>

            <div>
                <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">
                    {{ t('common.sort') }}
                </label>
                <select v-model="sortModel" class="select-base">
                    <option v-for="option in sortOptions" :key="option.value" :value="option.value">
                        {{ option.label }}
                    </option>
                </select>
            </div>

            <button type="button" class="btn-secondary" @click="$emit('apply')">{{ t('common.filter') }}</button>
            <button type="button" class="btn-ghost" @click="$emit('reset')">{{ t('common.reset') }}</button>
        </div>
    </div>
</template>
