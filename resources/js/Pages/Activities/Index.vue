<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import EmptyState from '@/Components/EmptyState.vue';
import SectionCard from '@/Components/SectionCard.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { useI18n } from '@/composables/useI18n';

const props = defineProps({
    logs: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({
            entity_type: '',
            action: '',
            actor_id: 0,
        }),
    },
    can: {
        type: Object,
        default: () => ({
            admin_scope: false,
        }),
    },
    actorOptions: {
        type: Array,
        default: () => [],
    },
    source_unavailable: {
        type: Boolean,
        default: false,
    },
});

const { t, locale } = useI18n();

const selectedEntity = ref(props.filters.entity_type ?? '');
const actionQuery = ref(props.filters.action ?? '');
const selectedActor = ref(props.filters.actor_id ? String(props.filters.actor_id) : '');

const rows = computed(() => [...props.logs]);

const applyFilters = () => {
    router.get(
        route('activities.index'),
        {
            entity_type: selectedEntity.value || undefined,
            action: actionQuery.value.trim() || undefined,
            actor_id: props.can.admin_scope && selectedActor.value ? selectedActor.value : undefined,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
};

const resetFilters = () => {
    selectedEntity.value = '';
    actionQuery.value = '';
    selectedActor.value = '';

    router.get(route('activities.index'), {}, { preserveState: true, replace: true });
};

const formatDate = (value) => {
    if (!value) {
        return '-';
    }

    return new Intl.DateTimeFormat(locale.value === 'fr' ? 'fr-FR' : 'en-US', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
};

const metadataPreview = (value) => {
    if (!value || Object.keys(value).length === 0) {
        return '-';
    }

    const rendered = JSON.stringify(value);

    return rendered.length > 110 ? `${rendered.slice(0, 110)}...` : rendered;
};

const statusTone = (action) => {
    if (action.includes('deleted')) {
        return 'bg-rose-50 text-rose-700 ring-rose-200';
    }

    if (action.includes('updated')) {
        return 'bg-amber-50 text-amber-700 ring-amber-200';
    }

    if (action.includes('created')) {
        return 'bg-emerald-50 text-emerald-700 ring-emerald-200';
    }

    return 'bg-slate-100 text-slate-700 ring-slate-200';
};

const entityHref = (row) => {
    const entityId = Number(row.entity_id);

    if (!Number.isInteger(entityId) || entityId <= 0) {
        return null;
    }

    if (row.entity_type === 'ticket') {
        return route('tickets.show', entityId);
    }

    if (row.entity_type === 'payment') {
        return route('payments.show', entityId);
    }

    return null;
};
</script>

<template>
    <Head :title="t('activities.title')" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h2 class="page-title">{{ t('activities.title') }}</h2>
                    <p class="page-subtitle">{{ t('activities.subtitle') }}</p>
                </div>
                <div class="metric-chip">
                    <span class="mono">{{ rows.length }}</span>
                    <span>{{ t('activities.rows_label') }}</span>
                </div>
            </div>
        </template>

        <div class="space-y-4">
            <SectionCard :title="t('activities.filters_title')" :description="t('activities.filters_hint')">
                <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ t('activities.filter_entity') }}</label>
                        <select v-model="selectedEntity" class="select-base">
                            <option value="">{{ t('common.all') }}</option>
                            <option value="ticket">{{ t('nav.tickets') }}</option>
                            <option value="payment">{{ t('nav.payments') }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ t('activities.filter_action') }}</label>
                        <input v-model="actionQuery" type="text" class="input-base" :placeholder="t('activities.action_placeholder')" />
                    </div>

                    <div v-if="can.admin_scope">
                        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">{{ t('activities.filter_actor') }}</label>
                        <select v-model="selectedActor" class="select-base">
                            <option value="">{{ t('common.all') }}</option>
                            <option v-for="actor in actorOptions" :key="actor.id" :value="String(actor.id)">
                                {{ actor.name }} • {{ actor.email }}
                            </option>
                        </select>
                    </div>

                    <div class="flex items-end gap-2">
                        <button type="button" class="btn-primary" @click="applyFilters">{{ t('common.filter') }}</button>
                        <button type="button" class="btn-secondary" @click="resetFilters">{{ t('common.reset') }}</button>
                    </div>
                </div>
            </SectionCard>

            <div v-if="source_unavailable" class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                {{ t('activities.mongo_unavailable') }}
            </div>

            <div class="table-shell reveal">
                <table class="min-w-[920px] w-full divide-y divide-slate-200/70">
                    <thead class="table-head sticky top-0 z-10">
                        <tr>
                            <th class="table-cell text-left">{{ t('common.created_at') }}</th>
                            <th class="table-cell text-left">{{ t('activities.col_action') }}</th>
                            <th class="table-cell text-left">{{ t('activities.col_entity') }}</th>
                            <th class="table-cell text-left">{{ t('activities.col_record') }}</th>
                            <th class="table-cell text-left">{{ t('activities.col_actor') }}</th>
                            <th class="table-cell text-left">{{ t('activities.col_metadata') }}</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 bg-white">
                        <tr v-for="row in rows" :key="row.id" class="table-row">
                            <td class="table-cell text-slate-600">{{ formatDate(row.created_at) }}</td>
                            <td class="table-cell">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset" :class="statusTone(row.action)">
                                    {{ row.action }}
                                </span>
                            </td>
                            <td class="table-cell text-slate-700">{{ row.entity_type }}</td>
                            <td class="table-cell">
                                <Link v-if="entityHref(row)" :href="entityHref(row)" class="font-semibold text-slate-900 hover:text-slate-700">
                                    #{{ row.entity_id }}
                                </Link>
                                <span v-else class="text-slate-700">#{{ row.entity_id }}</span>
                            </td>
                            <td class="table-cell text-slate-600">
                                {{ row.actor?.email ?? (row.actor_id ? `#${row.actor_id}` : '-') }}
                            </td>
                            <td class="table-cell mono text-xs text-slate-500">{{ metadataPreview(row.metadata) }}</td>
                        </tr>

                        <tr v-if="rows.length === 0">
                            <td colspan="6" class="p-6">
                                <EmptyState :title="t('activities.empty')" :description="t('activities.empty_hint')" />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
