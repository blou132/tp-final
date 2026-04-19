<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import EmptyState from '@/Components/EmptyState.vue';
import FilterBar from '@/Components/FilterBar.vue';
import SectionCard from '@/Components/SectionCard.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { useI18n } from '@/composables/useI18n';

const props = defineProps({
    tickets: {
        type: Object,
        required: true,
    },
    statuses: {
        type: Array,
        required: true,
    },
    filters: {
        type: Object,
        required: true,
    },
    can: {
        type: Object,
        required: true,
    },
});

const { t, locale } = useI18n();

const selectedStatus = ref(props.filters.status ?? '');
const searchQuery = ref('');
const sortBy = ref('newest');
const deletingTicket = ref(null);

const sortOptions = computed(() => [
    { value: 'newest', label: t('common.sort_newest') },
    { value: 'oldest', label: t('common.sort_oldest') },
    { value: 'title_asc', label: t('common.sort_title_asc') },
    { value: 'title_desc', label: t('common.sort_title_desc') },
]);

const applyFilters = () => {
    router.get(
        route('tickets.index'),
        {
            status: selectedStatus.value || undefined,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
};

const resetFilters = () => {
    selectedStatus.value = '';
    searchQuery.value = '';
    sortBy.value = 'newest';

    router.get(route('tickets.index'), {}, { preserveState: true, replace: true });
};

const openDeleteDialog = (ticket) => {
    deletingTicket.value = ticket;
};

const closeDeleteDialog = () => {
    deletingTicket.value = null;
};

const confirmDelete = () => {
    if (!deletingTicket.value) {
        return;
    }

    router.delete(route('tickets.destroy', deletingTicket.value.id), {
        onFinish: () => {
            deletingTicket.value = null;
        },
    });
};

const rows = computed(() => {
    const query = searchQuery.value.trim().toLowerCase();

    let collection = [...props.tickets.data];

    if (selectedStatus.value) {
        collection = collection.filter((ticket) => ticket.status === selectedStatus.value);
    }

    if (query) {
        collection = collection.filter((ticket) => {
            const text = [ticket.id, ticket.title, ticket.description, ticket.user?.email, ticket.user?.name]
                .filter(Boolean)
                .join(' ')
                .toLowerCase();

            return text.includes(query);
        });
    }

    switch (sortBy.value) {
        case 'oldest':
            collection.sort((a, b) => new Date(a.created_at).getTime() - new Date(b.created_at).getTime());
            break;
        case 'title_asc':
            collection.sort((a, b) => (a.title ?? '').localeCompare(b.title ?? '', locale.value));
            break;
        case 'title_desc':
            collection.sort((a, b) => (b.title ?? '').localeCompare(a.title ?? '', locale.value));
            break;
        case 'newest':
        default:
            collection.sort((a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime());
            break;
    }

    return collection;
});

const flaggedCount = computed(() => rows.value.filter((ticket) => ticket.is_flagged).length);
const openCount = computed(() => rows.value.filter((ticket) => ticket.status === 'open').length);
const inProgressCount = computed(() => rows.value.filter((ticket) => ticket.status === 'in_progress').length);
const closedCount = computed(() => rows.value.filter((ticket) => ticket.status === 'closed').length);

const openRate = computed(() => {
    if (rows.value.length === 0) {
        return 0;
    }

    return Math.round((openCount.value / rows.value.length) * 100);
});

const closedRate = computed(() => {
    if (rows.value.length === 0) {
        return 0;
    }

    return Math.round((closedCount.value / rows.value.length) * 100);
});

const getAgeHours = (ticket) => {
    if (!ticket?.created_at) {
        return 0;
    }

    return Math.max(0, Math.floor((Date.now() - new Date(ticket.created_at).getTime()) / 36e5));
};

const staleCount = computed(() =>
    rows.value.filter((ticket) => ['open', 'in_progress'].includes(ticket.status) && getAgeHours(ticket) >= 48).length,
);

const freshCount = computed(() =>
    rows.value.filter((ticket) => ['open', 'in_progress'].includes(ticket.status) && getAgeHours(ticket) < 24).length,
);

const highImpactCount = computed(() =>
    rows.value.filter((ticket) => ticket.is_flagged || getAgeHours(ticket) >= 72).length,
);

const statusBreakdown = computed(() => {
    const total = rows.value.length || 1;

    return props.statuses.map((status) => {
        const count = rows.value.filter((ticket) => ticket.status === status).length;

        return {
            status,
            count,
            ratio: Math.round((count / total) * 100),
        };
    });
});

const topRequesters = computed(() => {
    const map = new Map();

    for (const ticket of rows.value) {
        const owner = ticket.user?.email ?? '-';
        map.set(owner, (map.get(owner) ?? 0) + 1);
    }

    return [...map.entries()]
        .map(([owner, count]) => ({ owner, count }))
        .sort((a, b) => b.count - a.count)
        .slice(0, 5);
});

const formatDate = (value) => {
    if (!value) {
        return '-';
    }

    return new Intl.DateTimeFormat(locale.value === 'fr' ? 'fr-FR' : 'en-US', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
};

const formatAge = (ticket) => {
    const hours = getAgeHours(ticket);

    if (hours < 24) {
        return `${hours}h`;
    }

    const days = Math.floor(hours / 24);
    const remaining = hours % 24;

    return `${days}d ${remaining}h`;
};

const excerpt = (value) => {
    if (!value) {
        return '-';
    }

    return value.length > 90 ? `${value.slice(0, 90)}...` : value;
};

const priorityInfo = (ticket) => {
    const ageHours = getAgeHours(ticket);

    if (ticket.is_flagged || ageHours >= 72) {
        return { label: t('tickets.priority_critical'), className: 'bg-rose-50 text-rose-700 ring-rose-200' };
    }

    if (ticket.status === 'open' && ageHours >= 36) {
        return { label: t('tickets.priority_high'), className: 'bg-amber-50 text-amber-700 ring-amber-200' };
    }

    return { label: t('tickets.priority_normal'), className: 'bg-slate-100 text-slate-700 ring-slate-200' };
};
</script>

<template>
    <Head :title="t('tickets.title')" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h2 class="page-title">{{ t('tickets.title') }}</h2>
                    <p class="page-subtitle">{{ t('tickets.list_subtitle') }}</p>
                </div>
                <Link v-if="can.create" :href="route('tickets.create')" class="btn-primary">
                    {{ t('tickets.new') }}
                </Link>
            </div>
        </template>

        <div class="grid gap-4 sm:grid-cols-2 2xl:grid-cols-5">
            <SectionCard :title="t('tickets.page_count')" :description="t('tickets.page_count_hint')">
                <p class="mono text-3xl font-bold text-slate-900">{{ rows.length }}</p>
            </SectionCard>
            <SectionCard :title="t('tickets.open_rate')" :description="t('tickets.open_rate_hint')">
                <p class="mono text-3xl font-bold text-slate-900">{{ openRate }}%</p>
            </SectionCard>
            <SectionCard :title="t('tickets.closed_rate')" :description="t('tickets.closed_rate_hint')">
                <p class="mono text-3xl font-bold text-emerald-700">{{ closedRate }}%</p>
            </SectionCard>
            <SectionCard :title="t('tickets.stale_count')" :description="t('tickets.stale_count_hint')">
                <p class="mono text-3xl font-bold text-amber-700">{{ staleCount }}</p>
            </SectionCard>
            <SectionCard :title="t('common.flagged')" :description="t('tickets.flagged_hint')">
                <p class="mono text-3xl font-bold text-slate-900">{{ flaggedCount }}</p>
            </SectionCard>
        </div>

        <div class="mt-4">
            <FilterBar
                v-model:search="searchQuery"
                v-model:status="selectedStatus"
                v-model:sort="sortBy"
                :statuses="statuses"
                :sort-options="sortOptions"
                :search-placeholder="t('tickets.search_placeholder')"
                @apply="applyFilters"
                @reset="resetFilters"
            />
        </div>

        <div class="mt-4 grid gap-4 2xl:grid-cols-[minmax(0,1fr)_340px]">
            <div>
                <div class="table-shell reveal">
                    <table class="min-w-[860px] w-full divide-y divide-slate-200/70">
                        <thead class="table-head sticky top-0 z-10">
                            <tr>
                                <th class="table-cell text-left">#</th>
                                <th class="table-cell text-left">{{ t('common.title') }}</th>
                                <th class="table-cell text-left">{{ t('common.status') }}</th>
                                <th class="table-cell text-left hidden lg:table-cell">{{ t('tickets.priority') }}</th>
                                <th class="table-cell text-left hidden 2xl:table-cell">{{ t('common.owner') }}</th>
                                <th class="table-cell text-left hidden xl:table-cell">{{ t('common.age') }}</th>
                                <th class="table-cell text-left">{{ t('common.actions') }}</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100 bg-white">
                            <tr v-for="ticket in rows" :key="ticket.id" class="table-row">
                                <td class="table-cell mono text-xs text-slate-500">#{{ ticket.id }}</td>
                                <td class="table-cell">
                                    <Link
                                        v-if="ticket.can?.view"
                                        :href="route('tickets.show', ticket.id)"
                                        class="font-semibold text-slate-900 hover:text-slate-700"
                                    >
                                        {{ ticket.title }}
                                    </Link>
                                    <p v-else class="font-semibold text-slate-900">
                                        {{ ticket.title }}
                                    </p>
                                    <p class="mt-1 text-xs text-slate-500">{{ excerpt(ticket.description) }}</p>
                                    <p class="mt-1 text-xs text-slate-400 2xl:hidden">
                                        {{ ticket.user?.email ?? '-' }} • {{ formatDate(ticket.created_at) }}
                                    </p>
                                </td>
                                <td class="table-cell"><StatusBadge :status="ticket.status" /></td>
                                <td class="table-cell hidden lg:table-cell">
                                    <span
                                        class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset"
                                        :class="priorityInfo(ticket).className"
                                    >
                                        {{ priorityInfo(ticket).label }}
                                    </span>
                                </td>
                                <td class="table-cell text-slate-600 hidden 2xl:table-cell">{{ ticket.user?.email ?? '-' }}</td>
                                <td class="table-cell text-slate-600 hidden xl:table-cell">{{ formatAge(ticket) }}</td>
                                <td class="table-cell">
                                    <div class="flex flex-wrap items-center gap-1">
                                        <Link v-if="ticket.can?.view" :href="route('tickets.show', ticket.id)" class="btn-ghost">
                                            {{ t('common.details') }}
                                        </Link>
                                        <Link v-if="ticket.can?.update" :href="route('tickets.edit', ticket.id)" class="btn-ghost">
                                            {{ t('common.edit') }}
                                        </Link>
                                        <button
                                            v-if="ticket.can?.delete"
                                            type="button"
                                            class="btn-ghost !text-rose-600 hover:!bg-rose-50"
                                            @click="openDeleteDialog(ticket)"
                                        >
                                            {{ t('common.delete') }}
                                        </button>
                                        <span
                                            v-if="!ticket.can?.view && !ticket.can?.update && !ticket.can?.delete"
                                            class="text-xs font-medium text-slate-400"
                                        >
                                            —
                                        </span>
                                    </div>
                                </td>
                            </tr>

                            <tr v-if="rows.length === 0">
                                <td colspan="7" class="p-6">
                                    <EmptyState :title="t('tickets.empty')" :description="t('tickets.empty_hint')">
                                        <template #actions>
                                            <Link v-if="can.create" :href="route('tickets.create')" class="btn-primary">
                                                {{ t('tickets.new') }}
                                            </Link>
                                        </template>
                                    </EmptyState>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 flex flex-wrap items-center justify-between gap-2 text-sm text-slate-600">
                    <Link v-if="tickets.prev_page_url" :href="tickets.prev_page_url" class="btn-secondary">{{ t('common.previous') }}</Link>
                    <span v-else class="btn-secondary cursor-not-allowed opacity-50">{{ t('common.previous') }}</span>

                    <span class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold tracking-wide text-slate-500">
                        {{ tickets.current_page }} / {{ tickets.last_page }}
                    </span>

                    <Link v-if="tickets.next_page_url" :href="tickets.next_page_url" class="btn-secondary">{{ t('common.next') }}</Link>
                    <span v-else class="btn-secondary cursor-not-allowed opacity-50">{{ t('common.next') }}</span>
                </div>
            </div>

            <div class="space-y-4">
                <SectionCard :title="t('tickets.status_overview')" :description="t('tickets.status_overview_hint')">
                    <div class="space-y-2.5">
                        <Link
                            v-for="item in statusBreakdown"
                            :key="item.status"
                            :href="route('tickets.index', { status: item.status })"
                            class="block rounded-xl border border-slate-200 bg-slate-50/70 px-3 py-2.5 transition hover:border-slate-300 hover:bg-slate-100"
                        >
                            <div class="flex items-center justify-between text-sm">
                                <StatusBadge :status="item.status" small />
                                <span class="mono text-xs text-slate-500">{{ item.count }}</span>
                            </div>
                            <div class="mt-2 h-1.5 rounded-full bg-slate-200">
                                <div class="h-1.5 rounded-full bg-slate-700" :style="{ width: `${item.ratio}%` }" />
                            </div>
                        </Link>
                    </div>
                </SectionCard>

                <SectionCard :title="t('tickets.aging_title')" :description="t('tickets.aging_hint')">
                    <div class="space-y-2">
                        <div class="flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50/70 px-3 py-2">
                            <span class="text-sm text-slate-700">{{ t('tickets.fresh_count') }}</span>
                            <span class="mono text-xs text-slate-500">{{ freshCount }}</span>
                        </div>
                        <div class="flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50/70 px-3 py-2">
                            <span class="text-sm text-slate-700">{{ t('tickets.stale_count') }}</span>
                            <span class="mono text-xs text-slate-500">{{ staleCount }}</span>
                        </div>
                        <div class="flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50/70 px-3 py-2">
                            <span class="text-sm text-slate-700">{{ t('tickets.high_impact') }}</span>
                            <span class="mono text-xs text-slate-500">{{ highImpactCount }}</span>
                        </div>
                    </div>
                </SectionCard>

                <SectionCard :title="t('tickets.top_requesters_title')" :description="t('tickets.top_requesters_hint')">
                    <div v-if="topRequesters.length === 0" class="rounded-xl border border-slate-200 bg-slate-50/70 px-3 py-3 text-sm text-slate-500">
                        {{ t('tickets.top_requesters_empty') }}
                    </div>
                    <div v-else class="space-y-2">
                        <div
                            v-for="item in topRequesters"
                            :key="item.owner"
                            class="flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50/70 px-3 py-2"
                        >
                            <span class="truncate text-sm text-slate-700">{{ item.owner }}</span>
                            <span class="mono text-xs text-slate-500">{{ item.count }}</span>
                        </div>
                    </div>
                </SectionCard>

                <SectionCard :title="t('tickets.quick_actions_title')" :description="t('tickets.quick_actions_hint')">
                    <div class="grid gap-2">
                        <Link v-if="can.create" :href="route('tickets.create')" class="btn-primary justify-center">
                            {{ t('tickets.new') }}
                        </Link>
                        <Link :href="route('tickets.index')" class="btn-secondary justify-between">
                            <span>{{ t('common.all') }}</span>
                            <span class="mono">{{ props.tickets.total }}</span>
                        </Link>
                        <Link :href="route('tickets.index', { status: 'open' })" class="btn-secondary justify-between">
                            <span>{{ t('status.open') }}</span>
                            <span class="mono">{{ openCount }}</span>
                        </Link>
                        <Link :href="route('tickets.index', { status: 'in_progress' })" class="btn-secondary justify-between">
                            <span>{{ t('status.in_progress') }}</span>
                            <span class="mono">{{ inProgressCount }}</span>
                        </Link>
                    </div>
                </SectionCard>
            </div>
        </div>

        <ConfirmDialog
            :show="Boolean(deletingTicket)"
            :title="t('tickets.delete_title')"
            :message="t('tickets.delete_message')"
            :confirm-label="t('common.delete')"
            :cancel-label="t('common.cancel')"
            danger
            @close="closeDeleteDialog"
            @confirm="confirmDelete"
        />
    </AuthenticatedLayout>
</template>
