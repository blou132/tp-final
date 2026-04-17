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
    payments: {
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
const deletingPayment = ref(null);

const sortOptions = computed(() => [
    { value: 'newest', label: t('common.sort_newest') },
    { value: 'oldest', label: t('common.sort_oldest') },
    { value: 'amount_desc', label: t('common.sort_amount_desc') },
    { value: 'amount_asc', label: t('common.sort_amount_asc') },
]);

const applyFilters = () => {
    router.get(
        route('payments.index'),
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

    router.get(route('payments.index'), {}, { preserveState: true, replace: true });
};

const openDeleteDialog = (payment) => {
    deletingPayment.value = payment;
};

const closeDeleteDialog = () => {
    deletingPayment.value = null;
};

const confirmDelete = () => {
    if (!deletingPayment.value) {
        return;
    }

    router.delete(route('payments.destroy', deletingPayment.value.id), {
        onFinish: () => {
            deletingPayment.value = null;
        },
    });
};

const rows = computed(() => {
    const query = searchQuery.value.trim().toLowerCase();

    let collection = [...props.payments.data];

    if (selectedStatus.value) {
        collection = collection.filter((payment) => payment.status === selectedStatus.value);
    }

    if (query) {
        collection = collection.filter((payment) => {
            const text = [payment.id, payment.amount, payment.user?.email, payment.user?.name]
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
        case 'amount_desc':
            collection.sort((a, b) => Number(b.amount) - Number(a.amount));
            break;
        case 'amount_asc':
            collection.sort((a, b) => Number(a.amount) - Number(b.amount));
            break;
        case 'newest':
        default:
            collection.sort((a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime());
            break;
    }

    return collection;
});

const totalAmount = computed(() => rows.value.reduce((sum, payment) => sum + Number(payment.amount ?? 0), 0));
const paidAmount = computed(() =>
    rows.value
        .filter((payment) => payment.status === 'paid')
        .reduce((sum, payment) => sum + Number(payment.amount ?? 0), 0),
);

const statusBreakdown = computed(() => {
    const total = rows.value.length || 1;

    return props.statuses.map((status) => {
        const count = rows.value.filter((payment) => payment.status === status).length;

        return {
            status,
            count,
            ratio: Math.round((count / total) * 100),
        };
    });
});

const formatMoney = (value) => {
    const number = Number(value ?? 0);

    return new Intl.NumberFormat(locale.value === 'fr' ? 'fr-FR' : 'en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(number);
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
</script>

<template>
    <Head :title="t('payments.title')" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h2 class="page-title">{{ t('payments.title') }}</h2>
                    <p class="page-subtitle">{{ t('payments.list_subtitle') }}</p>
                </div>
                <Link v-if="can.create" :href="route('payments.create')" class="btn-primary">
                    {{ t('payments.new') }}
                </Link>
            </div>
        </template>

        <div class="grid gap-4 md:grid-cols-3">
            <SectionCard :title="t('payments.page_count')" :description="t('payments.page_count_hint')">
                <p class="mono text-3xl font-bold text-slate-900">{{ rows.length }}</p>
            </SectionCard>
            <SectionCard :title="t('payments.total_amount')" :description="t('payments.total_amount_hint')">
                <p class="mono text-3xl font-bold text-slate-900">{{ formatMoney(totalAmount) }} €</p>
            </SectionCard>
            <SectionCard :title="t('payments.paid_amount')" :description="t('payments.paid_amount_hint')">
                <p class="mono text-3xl font-bold text-emerald-700">{{ formatMoney(paidAmount) }} €</p>
            </SectionCard>
        </div>

        <div class="mt-4">
            <FilterBar
                v-model:search="searchQuery"
                v-model:status="selectedStatus"
                v-model:sort="sortBy"
                :statuses="statuses"
                :sort-options="sortOptions"
                :search-placeholder="t('payments.search_placeholder')"
                @apply="applyFilters"
                @reset="resetFilters"
            />
        </div>

        <div class="mt-4 grid gap-4 2xl:grid-cols-[minmax(0,1fr)_320px]">
            <div>
                <div class="table-shell reveal">
                    <table class="min-w-[680px] w-full divide-y divide-slate-200/70">
                        <thead class="table-head sticky top-0 z-10">
                            <tr>
                                <th class="table-cell text-left">#</th>
                                <th class="table-cell text-left">{{ t('common.amount') }}</th>
                                <th class="table-cell text-left">{{ t('common.status') }}</th>
                                <th class="table-cell text-left hidden xl:table-cell">{{ t('common.owner') }}</th>
                                <th class="table-cell text-left hidden xl:table-cell">{{ t('common.created_at') }}</th>
                                <th class="table-cell text-left">{{ t('common.actions') }}</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100 bg-white">
                            <tr v-for="payment in rows" :key="payment.id" class="table-row">
                                <td class="table-cell mono text-xs text-slate-500">#{{ payment.id }}</td>
                                <td class="table-cell text-sm font-semibold text-slate-900">{{ formatMoney(payment.amount) }} €</td>
                                <td class="table-cell"><StatusBadge :status="payment.status" /></td>
                                <td class="table-cell text-slate-600 hidden xl:table-cell">{{ payment.user?.email ?? '-' }}</td>
                                <td class="table-cell text-slate-600 hidden xl:table-cell">{{ formatDate(payment.created_at) }}</td>
                                <td class="table-cell">
                                    <div class="flex flex-wrap gap-1">
                                        <Link :href="route('payments.show', payment.id)" class="btn-ghost">{{ t('common.details') }}</Link>
                                        <Link :href="route('payments.edit', payment.id)" class="btn-ghost">{{ t('common.edit') }}</Link>
                                        <button
                                            type="button"
                                            class="btn-ghost !text-rose-600 hover:!bg-rose-50"
                                            @click="openDeleteDialog(payment)"
                                        >
                                            {{ t('common.delete') }}
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <tr v-if="rows.length === 0">
                                <td colspan="6" class="p-6">
                                    <EmptyState :title="t('payments.empty')" :description="t('payments.empty_hint')">
                                        <template #actions>
                                            <Link v-if="can.create" :href="route('payments.create')" class="btn-primary">
                                                {{ t('payments.new') }}
                                            </Link>
                                        </template>
                                    </EmptyState>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 flex flex-wrap items-center justify-between gap-2 text-sm text-slate-600">
                    <Link v-if="payments.prev_page_url" :href="payments.prev_page_url" class="btn-secondary">{{ t('common.previous') }}</Link>
                    <span v-else class="btn-secondary cursor-not-allowed opacity-50">{{ t('common.previous') }}</span>

                    <span class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold tracking-wide text-slate-500">
                        {{ payments.current_page }} / {{ payments.last_page }}
                    </span>

                    <Link v-if="payments.next_page_url" :href="payments.next_page_url" class="btn-secondary">{{ t('common.next') }}</Link>
                    <span v-else class="btn-secondary cursor-not-allowed opacity-50">{{ t('common.next') }}</span>
                </div>
            </div>

            <div class="space-y-4">
                <SectionCard :title="t('payments.status_overview')" :description="t('payments.status_overview_hint')">
                    <div class="space-y-2.5">
                        <Link
                            v-for="item in statusBreakdown"
                            :key="item.status"
                            :href="route('payments.index', { status: item.status })"
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

                <SectionCard :title="t('payments.quick_actions_title')" :description="t('payments.quick_actions_hint')">
                    <div class="grid gap-2">
                        <Link v-if="can.create" :href="route('payments.create')" class="btn-primary justify-center">
                            {{ t('payments.new') }}
                        </Link>
                        <Link :href="route('payments.index')" class="btn-secondary justify-between">
                            <span>{{ t('common.all') }}</span>
                            <span class="mono">{{ props.payments.total }}</span>
                        </Link>
                        <Link :href="route('payments.index', { status: 'pending' })" class="btn-secondary justify-between">
                            <span>{{ t('status.pending') }}</span>
                            <span class="mono">{{ statusBreakdown.find((item) => item.status === 'pending')?.count ?? 0 }}</span>
                        </Link>
                    </div>
                </SectionCard>
            </div>
        </div>

        <ConfirmDialog
            :show="Boolean(deletingPayment)"
            :title="t('payments.delete_title')"
            :message="t('payments.delete_message')"
            :confirm-label="t('common.delete')"
            :cancel-label="t('common.cancel')"
            danger
            @close="closeDeleteDialog"
            @confirm="confirmDelete"
        />
    </AuthenticatedLayout>
</template>
