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

const formatDate = (value) => {
    if (!value) {
        return '-';
    }

    return new Intl.DateTimeFormat(locale.value === 'fr' ? 'fr-FR' : 'en-US', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
};

const excerpt = (value) => {
    if (!value) {
        return '-';
    }

    return value.length > 90 ? `${value.slice(0, 90)}...` : value;
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

        <div class="grid gap-4 md:grid-cols-3">
            <SectionCard :title="t('tickets.page_count')" :description="t('tickets.page_count_hint')">
                <p class="mono text-3xl font-bold text-slate-900">{{ rows.length }}</p>
            </SectionCard>
            <SectionCard :title="t('common.flagged')" :description="t('tickets.flagged_hint')">
                <p class="mono text-3xl font-bold text-slate-900">{{ flaggedCount }}</p>
            </SectionCard>
            <SectionCard :title="t('common.status')" :description="t('tickets.active_filter')">
                <p class="text-sm font-semibold text-slate-700">
                    {{ selectedStatus ? t(`status.${selectedStatus}`, selectedStatus) : t('common.all') }}
                </p>
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

        <div class="mt-4 grid gap-4 2xl:grid-cols-[minmax(0,1fr)_320px]">
            <div>
                <div class="table-shell reveal">
                    <table class="min-w-full divide-y divide-slate-200/70">
                        <thead class="table-head sticky top-0 z-10">
                            <tr>
                                <th class="table-cell text-left">#</th>
                                <th class="table-cell text-left">{{ t('common.title') }}</th>
                                <th class="table-cell text-left">{{ t('common.status') }}</th>
                                <th class="table-cell text-left">{{ t('common.owner') }}</th>
                                <th class="table-cell text-left">{{ t('common.flagged') }}</th>
                                <th class="table-cell text-left">{{ t('common.created_at') }}</th>
                                <th class="table-cell text-left">{{ t('common.actions') }}</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100 bg-white">
                            <tr v-for="ticket in rows" :key="ticket.id" class="table-row">
                                <td class="table-cell mono text-xs text-slate-500">#{{ ticket.id }}</td>
                                <td class="table-cell">
                                    <Link :href="route('tickets.show', ticket.id)" class="font-semibold text-slate-900 hover:text-slate-700">
                                        {{ ticket.title }}
                                    </Link>
                                    <p class="mt-1 text-xs text-slate-500">{{ excerpt(ticket.description) }}</p>
                                </td>
                                <td class="table-cell"><StatusBadge :status="ticket.status" /></td>
                                <td class="table-cell text-slate-600">{{ ticket.user?.email ?? '-' }}</td>
                                <td class="table-cell">
                                    <span
                                        class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset"
                                        :class="
                                            ticket.is_flagged
                                                ? 'bg-amber-50 text-amber-700 ring-amber-200'
                                                : 'bg-slate-100 text-slate-600 ring-slate-200'
                                        "
                                    >
                                        {{ ticket.is_flagged ? t('common.yes') : t('common.no') }}
                                    </span>
                                </td>
                                <td class="table-cell text-slate-600">{{ formatDate(ticket.created_at) }}</td>
                                <td class="table-cell">
                                    <div class="flex flex-wrap gap-1">
                                        <Link :href="route('tickets.show', ticket.id)" class="btn-ghost">{{ t('common.details') }}</Link>
                                        <Link :href="route('tickets.edit', ticket.id)" class="btn-ghost">{{ t('common.edit') }}</Link>
                                        <button
                                            type="button"
                                            class="btn-ghost !text-rose-600 hover:!bg-rose-50"
                                            @click="openDeleteDialog(ticket)"
                                        >
                                            {{ t('common.delete') }}
                                        </button>
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
                            <span class="mono">{{ statusBreakdown.find((item) => item.status === 'open')?.count ?? 0 }}</span>
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
