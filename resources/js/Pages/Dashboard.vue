<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import EmptyState from '@/Components/EmptyState.vue';
import KpiCard from '@/Components/KpiCard.vue';
import SectionCard from '@/Components/SectionCard.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from '@/composables/useI18n';

const props = defineProps({
    stats: {
        type: Object,
        required: true,
    },
    recentTickets: {
        type: Array,
        required: true,
    },
    recentPayments: {
        type: Array,
        default: () => [],
    },
    can: {
        type: Object,
        default: () => ({
            create_ticket: false,
            create_payment: false,
        }),
    },
});

const { t, locale } = useI18n();

const ticketsTotal = computed(() => Number(props.stats.tickets.total ?? 0));
const paymentsTotal = computed(() => Number(props.stats.payments.total ?? 0));

const ticketCompletionRate = computed(() => {
    if (ticketsTotal.value === 0) {
        return 0;
    }

    return Math.round((Number(props.stats.tickets.closed ?? 0) / ticketsTotal.value) * 100);
});

const paymentSuccessRate = computed(() => {
    if (paymentsTotal.value === 0) {
        return 0;
    }

    return Math.round((Number(props.stats.payments.paid ?? 0) / paymentsTotal.value) * 100);
});

const requiresAttention = computed(() => {
    return (
        Number(props.stats.tickets.open ?? 0) +
        Number(props.stats.tickets.in_progress ?? 0) +
        Number(props.stats.payments.pending ?? 0) +
        Number(props.stats.payments.failed ?? 0)
    );
});

const healthScore = computed(() => {
    const totalWork = ticketsTotal.value + paymentsTotal.value;

    if (totalWork === 0) {
        return 100;
    }

    const pressure = requiresAttention.value / totalWork;

    return Math.max(0, Math.min(100, Math.round(100 - pressure * 45)));
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

const ticketDistribution = computed(() => {
    const total = ticketsTotal.value || 1;

    return [
        { key: 'open', count: Number(props.stats.tickets.open ?? 0), tone: 'bg-sky-500' },
        { key: 'in_progress', count: Number(props.stats.tickets.in_progress ?? 0), tone: 'bg-amber-500' },
        { key: 'closed', count: Number(props.stats.tickets.closed ?? 0), tone: 'bg-emerald-500' },
    ].map((item) => ({
        ...item,
        percentage: Math.round((item.count / total) * 100),
    }));
});

const paymentDistribution = computed(() => {
    const total = paymentsTotal.value || 1;

    return [
        { key: 'pending', count: Number(props.stats.payments.pending ?? 0), tone: 'bg-amber-500' },
        { key: 'paid', count: Number(props.stats.payments.paid ?? 0), tone: 'bg-emerald-500' },
        { key: 'failed', count: Number(props.stats.payments.failed ?? 0), tone: 'bg-rose-500' },
    ].map((item) => ({
        ...item,
        percentage: Math.round((item.count / total) * 100),
    }));
});

const operationRows = computed(() => [
    {
        key: 'open',
        count: Number(props.stats.tickets.open ?? 0),
        href: route('tickets.index', { status: 'open' }),
        helper: t('dashboard.operation_open_hint'),
    },
    {
        key: 'in_progress',
        count: Number(props.stats.tickets.in_progress ?? 0),
        href: route('tickets.index', { status: 'in_progress' }),
        helper: t('dashboard.operation_in_progress_hint'),
    },
    {
        key: 'pending',
        count: Number(props.stats.payments.pending ?? 0),
        href: route('payments.index', { status: 'pending' }),
        helper: t('dashboard.operation_pending_hint'),
    },
    {
        key: 'failed',
        count: Number(props.stats.payments.failed ?? 0),
        href: route('payments.index', { status: 'failed' }),
        helper: t('dashboard.operation_failed_hint'),
    },
]);

const activityTimeline = computed(() => {
    const ticketEvents = props.recentTickets.map((ticket) => ({
        key: `ticket-${ticket.id}`,
        type: 'ticket',
        id: ticket.id,
        status: ticket.status,
        title: ticket.title,
        user: ticket.user,
        created_at: ticket.created_at,
        href: route('tickets.show', ticket.id),
    }));

    const paymentEvents = props.recentPayments.map((payment) => ({
        key: `payment-${payment.id}`,
        type: 'payment',
        id: payment.id,
        status: payment.status,
        title: `${formatMoney(payment.amount)} €`,
        user: payment.user,
        created_at: payment.created_at,
        href: route('payments.show', payment.id),
    }));

    return [...ticketEvents, ...paymentEvents]
        .sort((a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime())
        .slice(0, 6);
});
</script>

<template>
    <Head :title="t('dashboard.title')" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h2 class="page-title">{{ t('dashboard.title') }}</h2>
                    <p class="page-subtitle">{{ t('dashboard.welcome') }}</p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <Link v-if="can.create_ticket" :href="route('tickets.create')" class="btn-secondary">
                        {{ t('dashboard.quick_new_ticket') }}
                    </Link>
                    <Link v-if="can.create_payment" :href="route('payments.create')" class="btn-primary">
                        {{ t('dashboard.quick_new_payment') }}
                    </Link>
                </div>
            </div>
        </template>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <KpiCard
                :title="t('dashboard.tickets_total')"
                :value="stats.tickets.total"
                :hint="`${t('dashboard.closed_rate')}: ${ticketCompletionRate}%`"
                tone="brand"
            >
                <template #icon>
                    <div class="rounded-xl bg-blue-100 p-2 text-blue-700">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 7h16M4 12h16M4 17h8" stroke-linecap="round" />
                        </svg>
                    </div>
                </template>
            </KpiCard>

            <KpiCard
                :title="t('dashboard.tickets_open')"
                :value="stats.tickets.open"
                :hint="`${t('dashboard.tickets_in_progress')}: ${stats.tickets.in_progress}`"
                tone="warning"
            >
                <template #icon>
                    <div class="rounded-xl bg-amber-100 p-2 text-amber-700">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 6v6l4 2" stroke-linecap="round" stroke-linejoin="round" />
                            <circle cx="12" cy="12" r="9" />
                        </svg>
                    </div>
                </template>
            </KpiCard>

            <KpiCard
                :title="t('dashboard.payments_total')"
                :value="stats.payments.total"
                :hint="`${t('dashboard.success_rate')}: ${paymentSuccessRate}%`"
                tone="success"
            >
                <template #icon>
                    <div class="rounded-xl bg-emerald-100 p-2 text-emerald-700">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="6" width="18" height="12" rx="2" />
                            <path d="M3 10h18" stroke-linecap="round" />
                        </svg>
                    </div>
                </template>
            </KpiCard>

            <KpiCard
                :title="t('dashboard.paid_amount')"
                :value="`${formatMoney(stats.payments.paid_amount)} €`"
                :hint="`${t('dashboard.requires_attention')}: ${requiresAttention}`"
                tone="success"
            >
                <template #icon>
                    <div class="rounded-xl bg-slate-100 p-2 text-slate-700">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path
                                d="M12 3v18M7 7.5a4 4 0 0 1 4-3h2a3 3 0 0 1 0 6h-2a3 3 0 0 0 0 6h2a4 4 0 0 0 4-3"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                    </div>
                </template>
            </KpiCard>
        </div>

        <div class="mt-6 grid gap-4 2xl:grid-cols-[minmax(0,1.65fr)_340px]">
            <div class="space-y-4">
                <div class="grid gap-4 xl:grid-cols-2">
                    <SectionCard :title="t('dashboard.ticket_distribution')" :description="t('dashboard.ticket_distribution_hint')">
                        <div class="space-y-3">
                            <div
                                v-for="item in ticketDistribution"
                                :key="item.key"
                                class="rounded-xl border border-slate-200/80 bg-slate-50/70 p-3"
                            >
                                <div class="mb-2 flex items-center justify-between text-sm">
                                    <StatusBadge :status="item.key" small />
                                    <span class="mono text-xs text-slate-500">{{ item.count }} ({{ item.percentage }}%)</span>
                                </div>
                                <div class="h-2 rounded-full bg-slate-200">
                                    <div class="h-2 rounded-full" :class="item.tone" :style="{ width: `${item.percentage}%` }" />
                                </div>
                            </div>
                        </div>
                    </SectionCard>

                    <SectionCard :title="t('dashboard.payment_distribution')" :description="t('dashboard.payment_distribution_hint')">
                        <div class="space-y-3">
                            <div
                                v-for="item in paymentDistribution"
                                :key="item.key"
                                class="rounded-xl border border-slate-200/80 bg-slate-50/70 p-3"
                            >
                                <div class="mb-2 flex items-center justify-between text-sm">
                                    <StatusBadge :status="item.key" small />
                                    <span class="mono text-xs text-slate-500">{{ item.count }} ({{ item.percentage }}%)</span>
                                </div>
                                <div class="h-2 rounded-full bg-slate-200">
                                    <div class="h-2 rounded-full" :class="item.tone" :style="{ width: `${item.percentage}%` }" />
                                </div>
                            </div>
                        </div>
                    </SectionCard>
                </div>

                <div class="grid gap-4 xl:grid-cols-2">
                    <SectionCard :title="t('dashboard.recent_tickets')" :description="t('dashboard.recent_tickets_hint')">
                        <EmptyState
                            v-if="recentTickets.length === 0"
                            :title="t('tickets.empty')"
                            :description="t('dashboard.empty_tickets_hint')"
                        />

                        <ul v-else class="space-y-2">
                            <li
                                v-for="ticket in recentTickets"
                                :key="ticket.id"
                                class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-200/80 bg-slate-50/80 px-3 py-3"
                            >
                                <div>
                                    <Link
                                        :href="route('tickets.show', ticket.id)"
                                        class="text-sm font-semibold text-slate-900 hover:text-slate-700"
                                    >
                                        #{{ ticket.id }} - {{ ticket.title }}
                                    </Link>
                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ ticket.user ?? '-' }} • {{ formatDate(ticket.created_at) }}
                                    </p>
                                </div>
                                <StatusBadge :status="ticket.status" small />
                            </li>
                        </ul>
                    </SectionCard>

                    <SectionCard :title="t('dashboard.recent_payments')" :description="t('dashboard.recent_payments_hint')">
                        <EmptyState
                            v-if="recentPayments.length === 0"
                            :title="t('payments.empty')"
                            :description="t('dashboard.empty_payments_hint')"
                        />

                        <ul v-else class="space-y-2">
                            <li
                                v-for="payment in recentPayments"
                                :key="payment.id"
                                class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-200/80 bg-slate-50/80 px-3 py-3"
                            >
                                <div>
                                    <Link
                                        :href="route('payments.show', payment.id)"
                                        class="text-sm font-semibold text-slate-900 hover:text-slate-700"
                                    >
                                        #{{ payment.id }} - {{ formatMoney(payment.amount) }} €
                                    </Link>
                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ payment.user ?? '-' }} • {{ formatDate(payment.created_at) }}
                                    </p>
                                </div>
                                <StatusBadge :status="payment.status" small />
                            </li>
                        </ul>
                    </SectionCard>
                </div>
            </div>

            <div class="space-y-4">
                <SectionCard :title="t('dashboard.operations_center')" :description="t('dashboard.operations_center_hint')">
                    <div class="mb-3 rounded-xl border border-slate-200 bg-slate-50/80 p-3">
                        <div class="mb-1 flex items-center justify-between text-sm font-semibold text-slate-700">
                            <span>{{ t('dashboard.health_score') }}</span>
                            <span class="mono">{{ healthScore }}%</span>
                        </div>
                        <div class="h-2 rounded-full bg-slate-200">
                            <div class="h-2 rounded-full bg-slate-800" :style="{ width: `${healthScore}%` }" />
                        </div>
                        <p class="mt-2 text-xs text-slate-500">
                            {{ t('dashboard.health_hint') }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Link
                            v-for="row in operationRows"
                            :key="row.key"
                            :href="row.href"
                            class="flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50/70 px-3 py-2.5 transition hover:border-slate-300 hover:bg-slate-100"
                        >
                            <div>
                                <StatusBadge :status="row.key" small />
                                <p class="mt-1 text-xs text-slate-500">{{ row.helper }}</p>
                            </div>
                            <span class="mono text-sm font-semibold text-slate-800">{{ row.count }}</span>
                        </Link>
                    </div>
                </SectionCard>

                <SectionCard :title="t('dashboard.quick_actions_title')" :description="t('dashboard.quick_actions_hint')">
                    <div class="grid gap-2">
                        <Link :href="route('tickets.index')" class="btn-secondary justify-between">
                            <span>{{ t('nav.tickets') }}</span>
                            <span class="mono">{{ stats.tickets.total }}</span>
                        </Link>
                        <Link :href="route('payments.index')" class="btn-secondary justify-between">
                            <span>{{ t('nav.payments') }}</span>
                            <span class="mono">{{ stats.payments.total }}</span>
                        </Link>
                        <Link v-if="can.create_ticket" :href="route('tickets.create')" class="btn-primary justify-center">
                            {{ t('dashboard.quick_new_ticket') }}
                        </Link>
                        <Link v-if="can.create_payment" :href="route('payments.create')" class="btn-primary justify-center">
                            {{ t('dashboard.quick_new_payment') }}
                        </Link>
                    </div>
                </SectionCard>

                <SectionCard :title="t('dashboard.activity_stream')" :description="t('dashboard.activity_stream_hint')">
                    <div v-if="activityTimeline.length === 0" class="rounded-xl border border-slate-200 bg-slate-50/70 px-3 py-4 text-sm text-slate-500">
                        {{ t('dashboard.no_activity') }}
                    </div>

                    <ul v-else class="space-y-2">
                        <li
                            v-for="event in activityTimeline"
                            :key="event.key"
                            class="rounded-xl border border-slate-200 bg-slate-50/70 px-3 py-2.5"
                        >
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <Link :href="event.href" class="text-sm font-semibold text-slate-900 hover:text-slate-700">
                                        {{ event.type === 'ticket' ? t('dashboard.activity_ticket') : t('dashboard.activity_payment') }}
                                        #{{ event.id }}
                                    </Link>
                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ event.user ?? '-' }} • {{ formatDate(event.created_at) }}
                                    </p>
                                </div>
                                <StatusBadge :status="event.status" small />
                            </div>
                        </li>
                    </ul>
                </SectionCard>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
