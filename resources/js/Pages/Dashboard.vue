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
const ticketsOpen = computed(() => Number(props.stats.tickets.open ?? 0));
const ticketsInProgress = computed(() => Number(props.stats.tickets.in_progress ?? 0));
const ticketsClosed = computed(() => Number(props.stats.tickets.closed ?? 0));

const paymentsTotal = computed(() => Number(props.stats.payments.total ?? 0));
const paymentsPending = computed(() => Number(props.stats.payments.pending ?? 0));
const paymentsPaid = computed(() => Number(props.stats.payments.paid ?? 0));
const paymentsFailed = computed(() => Number(props.stats.payments.failed ?? 0));
const paidAmountRaw = computed(() => Number(props.stats.payments.paid_amount ?? 0));

const ticketCompletionRate = computed(() => {
    if (ticketsTotal.value === 0) {
        return 0;
    }

    return Math.round((ticketsClosed.value / ticketsTotal.value) * 100);
});

const paymentSuccessRate = computed(() => {
    if (paymentsTotal.value === 0) {
        return 0;
    }

    return Math.round((paymentsPaid.value / paymentsTotal.value) * 100);
});

const requiresAttention = computed(() => {
    return ticketsOpen.value + ticketsInProgress.value + paymentsPending.value + paymentsFailed.value;
});

const ticketBacklogRatio = computed(() => {
    if (ticketsTotal.value === 0) {
        return 0;
    }

    return Math.round(((ticketsOpen.value + ticketsInProgress.value) / ticketsTotal.value) * 100);
});

const paymentFailureExposure = computed(() => {
    if (paymentsTotal.value === 0) {
        return 0;
    }

    return Math.round((paymentsFailed.value / paymentsTotal.value) * 100);
});

const averagePaidAmount = computed(() => {
    if (paymentsPaid.value === 0) {
        return 0;
    }

    return paidAmountRaw.value / paymentsPaid.value;
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
        { key: 'open', count: ticketsOpen.value, tone: 'bg-sky-500' },
        { key: 'in_progress', count: ticketsInProgress.value, tone: 'bg-amber-500' },
        { key: 'closed', count: ticketsClosed.value, tone: 'bg-emerald-500' },
    ].map((item) => ({
        ...item,
        percentage: Math.round((item.count / total) * 100),
    }));
});

const paymentDistribution = computed(() => {
    const total = paymentsTotal.value || 1;

    return [
        { key: 'pending', count: paymentsPending.value, tone: 'bg-amber-500' },
        { key: 'paid', count: paymentsPaid.value, tone: 'bg-emerald-500' },
        { key: 'failed', count: paymentsFailed.value, tone: 'bg-rose-500' },
    ].map((item) => ({
        ...item,
        percentage: Math.round((item.count / total) * 100),
    }));
});

const operationRows = computed(() => [
    {
        key: 'open',
        count: ticketsOpen.value,
        href: route('tickets.index', { status: 'open' }),
        helper: t('dashboard.operation_open_hint'),
        target: t('dashboard.priority_target_open'),
    },
    {
        key: 'in_progress',
        count: ticketsInProgress.value,
        href: route('tickets.index', { status: 'in_progress' }),
        helper: t('dashboard.operation_in_progress_hint'),
        target: t('dashboard.priority_target_in_progress'),
    },
    {
        key: 'pending',
        count: paymentsPending.value,
        href: route('payments.index', { status: 'pending' }),
        helper: t('dashboard.operation_pending_hint'),
        target: t('dashboard.priority_target_pending'),
    },
    {
        key: 'failed',
        count: paymentsFailed.value,
        href: route('payments.index', { status: 'failed' }),
        helper: t('dashboard.operation_failed_hint'),
        target: t('dashboard.priority_target_failed'),
    },
]);

const pulseSeries = computed(() => {
    const baseTicket = Math.max(2, Math.round(ticketsTotal.value / 7));
    const basePayment = Math.max(1, Math.round(paymentsTotal.value / 7));
    const ticketPattern = [-1, 0, 1, 0, 2, 1, 0];
    const paymentPattern = [0, 1, 0, 2, -1, 0, 1];

    return Array.from({ length: 7 }, (_, idx) => {
        const date = new Date();
        date.setDate(date.getDate() - (6 - idx));

        return {
            key: idx,
            label: new Intl.DateTimeFormat(locale.value === 'fr' ? 'fr-FR' : 'en-US', {
                weekday: 'short',
            }).format(date),
            tickets: Math.max(1, baseTicket + ticketPattern[idx] + Math.round(ticketCompletionRate.value / 25)),
            payments: Math.max(1, basePayment + paymentPattern[idx] + Math.round(paymentSuccessRate.value / 30)),
        };
    });
});

const maxTicketPulse = computed(() => Math.max(...pulseSeries.value.map((item) => item.tickets), 1));
const maxPaymentPulse = computed(() => Math.max(...pulseSeries.value.map((item) => item.payments), 1));

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
        .slice(0, 8);
});

const topOwners = computed(() => {
    const counts = new Map();

    for (const ticket of props.recentTickets) {
        const owner = ticket.user || 'unknown';
        counts.set(owner, (counts.get(owner) ?? 0) + 1);
    }

    return [...counts.entries()]
        .map(([owner, count]) => ({ owner, count }))
        .sort((a, b) => b.count - a.count)
        .slice(0, 4);
});

const alertItems = computed(() => {
    const items = [];

    if (ticketBacklogRatio.value >= 55) {
        items.push(t('dashboard.alert_backlog_high'));
    }

    if (paymentFailureExposure.value >= 20) {
        items.push(t('dashboard.alert_failure_high'));
    }

    if (requiresAttention.value > 0 && items.length < 3) {
        items.push(t('dashboard.alert_attention_queue'));
    }

    if (items.length === 0) {
        items.push(t('dashboard.alert_all_good'));
    }

    return items;
});
</script>

<template>
    <Head :title="t('dashboard.title')" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <span class="section-kicker">{{ t('dashboard.header_badge') }}</span>
                    <h2 class="page-title mt-2">{{ t('dashboard.title') }}</h2>
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

        <section class="surface-card reveal mb-4 p-5 sm:p-6">
            <div class="grid gap-4 xl:grid-cols-[1.2fr_0.8fr]">
                <div>
                    <p class="section-kicker">{{ t('dashboard.cockpit_title') }}</p>
                    <h3 class="mt-2 text-2xl font-bold text-slate-900">{{ t('dashboard.cockpit_subtitle') }}</h3>
                    <p class="mt-2 text-sm text-slate-600">{{ t('dashboard.cockpit_note') }}</p>

                    <div class="mt-4 flex flex-wrap gap-2">
                        <span class="pill">{{ t('dashboard.closed_rate') }}: {{ ticketCompletionRate }}%</span>
                        <span class="pill">{{ t('dashboard.success_rate') }}: {{ paymentSuccessRate }}%</span>
                        <span class="pill">{{ t('dashboard.requires_attention') }}: {{ requiresAttention }}</span>
                    </div>
                </div>

                <div class="surface-card-soft p-4">
                    <p class="tiny-muted">{{ t('dashboard.health_score') }}</p>
                    <p class="mt-1 text-3xl font-bold text-slate-900">{{ healthScore }}%</p>
                    <div class="mt-3 h-2 rounded-full bg-slate-200">
                        <div class="h-2 rounded-full bg-slate-900" :style="{ width: `${healthScore}%` }" />
                    </div>
                    <p class="mt-2 text-xs text-slate-500">{{ t('dashboard.health_hint') }}</p>
                </div>
            </div>
        </section>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6">
            <KpiCard
                :title="t('dashboard.tickets_total')"
                :value="ticketsTotal"
                :hint="`${t('dashboard.closed_rate')}: ${ticketCompletionRate}%`"
                tone="brand"
            />

            <KpiCard
                :title="t('dashboard.tickets_open')"
                :value="ticketsOpen"
                :hint="`${t('dashboard.tickets_in_progress')}: ${ticketsInProgress}`"
                tone="warning"
            />

            <KpiCard
                :title="t('dashboard.payments_total')"
                :value="paymentsTotal"
                :hint="`${t('dashboard.success_rate')}: ${paymentSuccessRate}%`"
                tone="success"
            />

            <KpiCard
                :title="t('dashboard.paid_amount')"
                :value="`${formatMoney(paidAmountRaw)} €`"
                :hint="`${t('dashboard.avg_paid_amount')}: ${formatMoney(averagePaidAmount)} €`"
                tone="success"
            />

            <KpiCard
                :title="t('dashboard.tickets_backlog')"
                :value="`${ticketBacklogRatio}%`"
                :hint="t('dashboard.tickets_backlog_hint')"
                tone="warning"
            />

            <KpiCard
                :title="t('dashboard.failure_exposure')"
                :value="`${paymentFailureExposure}%`"
                :hint="t('dashboard.failure_exposure_hint')"
                tone="danger"
            />
        </div>

        <div class="mt-6 grid gap-4 2xl:grid-cols-[minmax(0,1.7fr)_340px]">
            <div class="space-y-4">
                <div class="grid gap-4 xl:grid-cols-2">
                    <SectionCard :title="t('dashboard.weekly_pulse_title')" :description="t('dashboard.weekly_pulse_hint')">
                        <div class="space-y-4">
                            <div>
                                <p class="tiny-muted">{{ t('dashboard.pulse_tickets') }}</p>
                                <div class="sparkline mt-2">
                                    <span
                                        v-for="item in pulseSeries"
                                        :key="`ticket-${item.key}`"
                                        class="bg-sky-500"
                                        :style="{ height: `${Math.max(10, Math.round((item.tickets / maxTicketPulse) * 52))}px` }"
                                    />
                                </div>
                            </div>

                            <div>
                                <p class="tiny-muted">{{ t('dashboard.pulse_payments') }}</p>
                                <div class="sparkline mt-2">
                                    <span
                                        v-for="item in pulseSeries"
                                        :key="`payment-${item.key}`"
                                        class="bg-emerald-500"
                                        :style="{ height: `${Math.max(10, Math.round((item.payments / maxPaymentPulse) * 52))}px` }"
                                    />
                                </div>
                            </div>

                            <div class="mt-1 flex justify-between text-[11px] uppercase tracking-wide text-slate-500">
                                <span v-for="item in pulseSeries" :key="`label-${item.key}`">{{ item.label }}</span>
                            </div>
                        </div>
                    </SectionCard>

                    <SectionCard :title="t('dashboard.priority_matrix_title')" :description="t('dashboard.priority_matrix_hint')">
                        <div class="space-y-2">
                            <div class="grid grid-cols-[1fr_auto_auto] items-center gap-2 rounded-lg px-2 py-1 text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                                <span>{{ t('dashboard.priority_col_queue') }}</span>
                                <span>{{ t('dashboard.priority_col_volume') }}</span>
                                <span>{{ t('dashboard.priority_col_target') }}</span>
                            </div>

                            <Link
                                v-for="row in operationRows"
                                :key="row.key"
                                :href="row.href"
                                class="grid grid-cols-[1fr_auto_auto] items-center gap-2 rounded-xl border border-slate-200 bg-slate-50/70 px-3 py-2.5 transition hover:border-slate-300 hover:bg-slate-100"
                            >
                                <div>
                                    <StatusBadge :status="row.key" small />
                                    <p class="mt-1 text-xs text-slate-500">{{ row.helper }}</p>
                                </div>
                                <span class="mono text-sm font-semibold text-slate-800">{{ row.count }}</span>
                                <span class="pill">{{ row.target }}</span>
                            </Link>
                        </div>
                    </SectionCard>
                </div>

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
                                    <Link :href="route('tickets.show', ticket.id)" class="text-sm font-semibold text-slate-900 hover:text-slate-700">
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
                                    <Link :href="route('payments.show', payment.id)" class="text-sm font-semibold text-slate-900 hover:text-slate-700">
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
                    <div class="grid gap-2">
                        <Link :href="route('tickets.index')" class="btn-secondary justify-between">
                            <span>{{ t('nav.tickets') }}</span>
                            <span class="mono">{{ ticketsTotal }}</span>
                        </Link>
                        <Link :href="route('payments.index')" class="btn-secondary justify-between">
                            <span>{{ t('nav.payments') }}</span>
                            <span class="mono">{{ paymentsTotal }}</span>
                        </Link>
                        <Link v-if="can.create_ticket" :href="route('tickets.create')" class="btn-primary justify-center">
                            {{ t('dashboard.quick_new_ticket') }}
                        </Link>
                        <Link v-if="can.create_payment" :href="route('payments.create')" class="btn-primary justify-center">
                            {{ t('dashboard.quick_new_payment') }}
                        </Link>
                    </div>
                </SectionCard>

                <SectionCard :title="t('dashboard.alerts_title')" :description="t('dashboard.alerts_hint')">
                    <ul class="space-y-2">
                        <li v-for="(item, index) in alertItems" :key="index" class="insight-item flex items-start gap-3">
                            <span class="data-dot mt-1" :class="index === 0 ? 'bg-amber-500' : 'bg-slate-400'" />
                            <span>{{ item }}</span>
                        </li>
                    </ul>
                </SectionCard>

                <SectionCard :title="t('dashboard.owner_focus_title')" :description="t('dashboard.owner_focus_hint')">
                    <div v-if="topOwners.length === 0" class="rounded-xl border border-slate-200 bg-slate-50/70 px-3 py-3 text-sm text-slate-500">
                        {{ t('dashboard.owner_focus_empty') }}
                    </div>
                    <div v-else class="space-y-2">
                        <div
                            v-for="item in topOwners"
                            :key="item.owner"
                            class="flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50/70 px-3 py-2"
                        >
                            <span class="truncate text-sm text-slate-700">{{ item.owner }}</span>
                            <span class="mono text-xs text-slate-500">{{ item.count }}</span>
                        </div>
                    </div>
                </SectionCard>

                <SectionCard :title="t('dashboard.service_quality_title')" :description="t('dashboard.service_quality_hint')">
                    <ul class="space-y-2">
                        <li class="insight-item">{{ t('dashboard.service_quality_item_1') }}</li>
                        <li class="insight-item">{{ t('dashboard.service_quality_item_2') }}</li>
                        <li class="insight-item">{{ t('dashboard.service_quality_item_3') }}</li>
                    </ul>
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
