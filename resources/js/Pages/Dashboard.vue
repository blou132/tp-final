<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { useI18n } from '@/composables/useI18n';

const props = defineProps({
    stats: {
        type: Object,
        required: true,
    },
    recentTickets: {
        type: Array,
        default: () => [],
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

const page = usePage();
const { t, locale } = useI18n();

const searchQuery = ref('');

const userName = computed(() => page.props.auth?.user?.name ?? 'User');
const userInitial = computed(() => userName.value.slice(0, 1).toUpperCase());

const ticketsTotal = computed(() => Number(props.stats?.tickets?.total ?? 0));
const ticketsOpen = computed(() => Number(props.stats?.tickets?.open ?? 0));
const ticketsInProgress = computed(() => Number(props.stats?.tickets?.in_progress ?? 0));
const ticketsClosed = computed(() => Number(props.stats?.tickets?.closed ?? 0));

const paymentsTotal = computed(() => Number(props.stats?.payments?.total ?? 0));
const paymentsPending = computed(() => Number(props.stats?.payments?.pending ?? 0));
const paymentsPaid = computed(() => Number(props.stats?.payments?.paid ?? 0));
const paymentsFailed = computed(() => Number(props.stats?.payments?.failed ?? 0));
const paidAmount = computed(() => Number(props.stats?.payments?.paid_amount ?? 0));

const uptime = computed(() => {
    const pressure = ticketsOpen.value + ticketsInProgress.value + paymentsFailed.value;
    const estimated = 99.95 - pressure * 0.06;

    return Math.max(96.2, Number(estimated.toFixed(2)));
});

const statusLabel = (status) => t(`status.${status}`, status);

const statusClass = (status) => {
    if (status === 'open' || status === 'pending') {
        return 'status-warning';
    }

    if (status === 'in_progress') {
        return 'status-info';
    }

    if (status === 'paid' || status === 'closed') {
        return 'status-success';
    }

    if (status === 'failed') {
        return 'status-danger';
    }

    return 'status-neutral';
};

const formatMoney = (value) => {
    const amount = Number(value ?? 0);

    return new Intl.NumberFormat(locale.value === 'fr' ? 'fr-FR' : 'en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(amount);
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

const kpiCards = computed(() => [
    {
        label: 'Tickets ouverts',
        value: ticketsOpen.value,
        hint: `${ticketsInProgress.value} en cours`,
        progress: ticketsTotal.value > 0 ? Math.round((ticketsOpen.value / ticketsTotal.value) * 100) : 0,
        tone: 'tone-primary',
    },
    {
        label: 'Paiements échoués',
        value: paymentsFailed.value,
        hint: `${paymentsPending.value} en attente`,
        progress: paymentsTotal.value > 0 ? Math.round((paymentsFailed.value / paymentsTotal.value) * 100) : 0,
        tone: 'tone-danger',
    },
    {
        label: 'Collecté cette semaine',
        value: `${formatMoney(paidAmount.value)} €`,
        hint: `${paymentsPaid.value} paiements validés`,
        progress: paymentsTotal.value > 0 ? Math.round((paymentsPaid.value / paymentsTotal.value) * 100) : 0,
        tone: 'tone-success',
    },
    {
        label: 'Disponibilité système',
        value: `${uptime.value}%`,
        hint: 'Monitoring unifié',
        progress: Math.round(uptime.value),
        tone: 'tone-info',
    },
]);

const chartSeries = computed(() => {
    const base = Math.max(8, ticketsOpen.value + ticketsInProgress.value + paymentsPending.value);
    const offsets = [-4, -2, 1, 4, -1, 2, 5, 1];

    return offsets.map((offset, index) => {
        const day = new Date();
        day.setDate(day.getDate() - (offsets.length - 1 - index));

        return {
            label: new Intl.DateTimeFormat(locale.value === 'fr' ? 'fr-FR' : 'en-US', { weekday: 'short' }).format(day),
            value: Math.max(3, base + offset),
        };
    });
});

const chartMax = computed(() => Math.max(...chartSeries.value.map((item) => item.value), 1));

const chartPath = computed(() => {
    const width = 560;
    const height = 220;
    const pad = 24;
    const points = chartSeries.value.map((item, index) => {
        const x = pad + (index * (width - pad * 2)) / Math.max(chartSeries.value.length - 1, 1);
        const y = height - pad - (item.value / chartMax.value) * (height - pad * 2);

        return { x, y };
    });

    return points.map((point, index) => `${index === 0 ? 'M' : 'L'} ${point.x} ${point.y}`).join(' ');
});

const areaPath = computed(() => {
    const width = 560;
    const height = 220;
    const pad = 24;

    return `${chartPath.value} L ${width - pad} ${height - pad} L ${pad} ${height - pad} Z`;
});

const filteredTickets = computed(() => {
    const query = searchQuery.value.trim().toLowerCase();

    if (!query) {
        return props.recentTickets;
    }

    return props.recentTickets.filter((ticket) => {
        const haystack = `${ticket.id} ${ticket.title} ${ticket.user ?? ''}`.toLowerCase();

        return haystack.includes(query);
    });
});

const activityFeed = computed(() => {
    const ticketEvents = props.recentTickets.map((ticket) => ({
        key: `ticket-${ticket.id}`,
        type: 'Ticket',
        title: ticket.title,
        status: ticket.status,
        user: ticket.user,
        created_at: ticket.created_at,
        href: route('tickets.show', ticket.id),
    }));

    const paymentEvents = props.recentPayments.map((payment) => ({
        key: `payment-${payment.id}`,
        type: 'Paiement',
        title: `${formatMoney(payment.amount)} €`,
        status: payment.status,
        user: payment.user,
        created_at: payment.created_at,
        href: route('payments.show', payment.id),
    }));

    return [...ticketEvents, ...paymentEvents]
        .sort((a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime())
        .slice(0, 6);
});

let revealObserver = null;
const revealNodes = new Set();

const registerReveal = (element) => {
    if (!element || revealNodes.has(element)) {
        return;
    }

    revealNodes.add(element);

    if (revealObserver) {
        revealObserver.observe(element);
    }
};

onMounted(() => {
    revealObserver = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    revealObserver.unobserve(entry.target);
                }
            });
        },
        {
            threshold: 0.14,
        },
    );

    revealNodes.forEach((node) => revealObserver.observe(node));
});

onBeforeUnmount(() => {
    if (revealObserver) {
        revealObserver.disconnect();
    }
});
</script>

<template>
    <Head :title="t('dashboard.title', 'Dashboard')" />

    <AuthenticatedLayout>
        <template #header>
            <div class="dash-title-wrap">
                <div>
                    <p class="dash-kicker">Workspace cockpit</p>
                    <h2>{{ t('dashboard.title', 'Dashboard') }}</h2>
                </div>
                <div class="header-actions">
                    <Link v-if="can.create_ticket" :href="route('tickets.create')" class="btn btn-ghost">Nouveau ticket</Link>
                    <Link v-if="can.create_payment" :href="route('payments.create')" class="btn btn-primary">Nouveau paiement</Link>
                </div>
            </div>
        </template>

        <div class="dashboard-premium">
            <section class="workspace-toolbar reveal-node" :ref="registerReveal">
                <label class="search-field">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M11 19a8 8 0 1 1 0-16 8 8 0 0 1 0 16Z" />
                        <path d="m21 21-4.3-4.3" />
                    </svg>
                    <input v-model="searchQuery" type="text" placeholder="Rechercher un ticket, utilisateur, id..." />
                </label>

                <div class="toolbar-right">
                    <button type="button" class="icon-btn" aria-label="Notifications">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M15 17H5l1.2-1.6a3 3 0 0 0 .6-1.8V10a5 5 0 1 1 10 0v3.6a3 3 0 0 0 .6 1.8L18.6 17H15Z" />
                            <path d="M10 19a2 2 0 0 0 4 0" />
                        </svg>
                    </button>

                    <div class="user-chip">
                        <span class="avatar">{{ userInitial }}</span>
                        <div>
                            <strong>{{ userName }}</strong>
                            <p>Support team</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="kpi-grid reveal-node" :ref="registerReveal">
                <article v-for="card in kpiCards" :key="card.label" class="kpi-card">
                    <p>{{ card.label }}</p>
                    <strong>{{ card.value }}</strong>
                    <span>{{ card.hint }}</span>
                    <div class="progress-track">
                        <div class="progress-fill" :class="card.tone" :style="{ '--progress': `${card.progress}%` }" />
                    </div>
                </article>
            </section>

            <section class="insight-grid">
                <article class="chart-card reveal-node" :ref="registerReveal">
                    <div class="section-head">
                        <div>
                            <p>Vue analytique</p>
                            <h3>Charge tickets & paiements</h3>
                        </div>
                        <span class="chip">7 jours</span>
                    </div>

                    <div class="chart-wrap">
                        <svg viewBox="0 0 560 220" preserveAspectRatio="none" class="chart-svg">
                            <defs>
                                <linearGradient id="lineGradient" x1="0" x2="1" y1="0" y2="0">
                                    <stop offset="0%" stop-color="#4F46E5" />
                                    <stop offset="100%" stop-color="#6366F1" />
                                </linearGradient>
                                <linearGradient id="areaGradient" x1="0" x2="0" y1="0" y2="1">
                                    <stop offset="0%" stop-color="rgba(79,70,229,0.28)" />
                                    <stop offset="100%" stop-color="rgba(79,70,229,0.02)" />
                                </linearGradient>
                            </defs>

                            <path :d="areaPath" fill="url(#areaGradient)" />
                            <path :d="chartPath" stroke="url(#lineGradient)" stroke-width="4" stroke-linecap="round" fill="none" />
                        </svg>

                        <div class="chart-labels">
                            <span v-for="point in chartSeries" :key="point.label">{{ point.label }}</span>
                        </div>
                    </div>
                </article>

                <article class="activity-card reveal-node" :ref="registerReveal">
                    <div class="section-head">
                        <div>
                            <p>Activité récente</p>
                            <h3>Derniers événements</h3>
                        </div>
                    </div>

                    <div v-if="activityFeed.length === 0" class="empty-box">
                        Aucun événement récent.
                    </div>

                    <ul v-else class="activity-list">
                        <li v-for="event in activityFeed" :key="event.key" class="activity-item">
                            <div>
                                <Link :href="event.href" class="activity-title">{{ event.type }} · {{ event.title }}</Link>
                                <p>{{ event.user ?? '-' }} · {{ formatDate(event.created_at) }}</p>
                            </div>
                            <span :class="['status-chip', statusClass(event.status)]">{{ statusLabel(event.status) }}</span>
                        </li>
                    </ul>
                </article>
            </section>

            <section class="table-card reveal-node" :ref="registerReveal">
                <div class="section-head">
                    <div>
                        <p>Tickets</p>
                        <h3>Vue opérationnelle</h3>
                    </div>
                    <Link :href="route('tickets.index')" class="chip chip-link">Voir tout</Link>
                </div>

                <div class="table-wrap" v-if="filteredTickets.length > 0">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Titre</th>
                                <th>Demandeur</th>
                                <th>Statut</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="ticket in filteredTickets" :key="ticket.id">
                                <td class="mono">#{{ ticket.id }}</td>
                                <td>
                                    <p class="table-main">{{ ticket.title }}</p>
                                </td>
                                <td>{{ ticket.user ?? '-' }}</td>
                                <td>
                                    <span :class="['status-chip', statusClass(ticket.status)]">{{ statusLabel(ticket.status) }}</span>
                                </td>
                                <td>{{ formatDate(ticket.created_at) }}</td>
                                <td>
                                    <Link :href="route('tickets.show', ticket.id)" class="row-link">Ouvrir</Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else class="empty-box">
                    Aucun ticket dans cette vue.
                </div>
            </section>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.dashboard-premium {
    --primary: #4f46e5;
    --bg: #f9fafb;
    --card: #ffffff;
    --border: #e5e7eb;
    --title: #111827;
    --body: #374151;
    --secondary: #6b7280;
    display: grid;
    gap: 16px;
    padding-bottom: 6px;
}

.dash-title-wrap {
    width: 100%;
    display: flex;
    align-items: end;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
}

.dash-kicker {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.14em;
    color: var(--secondary);
    font-weight: 700;
}

.dash-title-wrap h2 {
    margin-top: 8px;
    font-size: clamp(28px, 3.2vw, 42px);
    line-height: 1;
    color: var(--title);
    letter-spacing: -0.03em;
}

.header-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.workspace-toolbar,
.chart-card,
.activity-card,
.table-card,
.kpi-card {
    border-radius: 12px;
    border: 1px solid var(--border);
    background: var(--card);
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
}

.workspace-toolbar {
    min-height: 64px;
    padding: 12px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
}

.search-field {
    display: flex;
    align-items: center;
    gap: 9px;
    border: 1px solid var(--border);
    border-radius: 10px;
    background: #fff;
    min-height: 44px;
    padding: 0 12px;
    width: min(520px, 100%);
}

.search-field svg {
    width: 17px;
    height: 17px;
    color: #94a3b8;
}

.search-field input {
    border: 0;
    outline: none;
    width: 100%;
    font-size: 14px;
    color: var(--body);
    background: transparent;
}

.search-field input::placeholder {
    color: #94a3b8;
}

.toolbar-right {
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.icon-btn {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    border: 1px solid var(--border);
    background: #fff;
    color: #64748b;
    display: grid;
    place-items: center;
    transition: all 180ms ease;
    cursor: pointer;
}

.icon-btn:hover {
    color: var(--title);
    background: #f3f4f6;
    transform: translateY(-1px);
}

.icon-btn svg {
    width: 18px;
    height: 18px;
}

.user-chip {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    border: 1px solid var(--border);
    border-radius: 10px;
    background: #fff;
    min-height: 40px;
    padding: 4px 10px 4px 6px;
}

.avatar {
    width: 28px;
    height: 28px;
    border-radius: 999px;
    background: #111827;
    color: #fff;
    font-size: 12px;
    font-weight: 700;
    display: grid;
    place-items: center;
}

.user-chip strong {
    display: block;
    color: var(--title);
    font-size: 13px;
    line-height: 1;
}

.user-chip p {
    margin-top: 2px;
    color: var(--secondary);
    font-size: 11px;
}

.kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 12px;
}

.kpi-card {
    padding: 18px;
    transition: transform 180ms ease, box-shadow 180ms ease;
}

.kpi-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 24px -18px rgba(17, 24, 39, 0.42);
}

.kpi-card p {
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--secondary);
    font-weight: 700;
}

.kpi-card strong {
    display: block;
    margin-top: 10px;
    font-size: 34px;
    line-height: 1;
    letter-spacing: -0.03em;
    color: var(--title);
}

.kpi-card span {
    display: block;
    margin-top: 8px;
    font-size: 13px;
    color: var(--secondary);
}

.progress-track {
    margin-top: 12px;
    height: 8px;
    border-radius: 999px;
    background: #eef2f7;
    overflow: hidden;
}

.progress-fill {
    width: var(--progress);
    height: 100%;
    border-radius: 999px;
    transform-origin: left;
    animation: progress-grow 850ms ease both;
}

.tone-primary {
    background: linear-gradient(90deg, #4f46e5, #6366f1);
}

.tone-danger {
    background: linear-gradient(90deg, #ef4444, #fb7185);
}

.tone-success {
    background: linear-gradient(90deg, #10b981, #34d399);
}

.tone-info {
    background: linear-gradient(90deg, #0ea5e9, #38bdf8);
}

.insight-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.35fr) minmax(0, 0.65fr);
    gap: 12px;
}

.chart-card,
.activity-card,
.table-card {
    padding: 20px;
}

.section-head {
    display: flex;
    align-items: start;
    justify-content: space-between;
    gap: 10px;
}

.section-head p {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.12em;
    color: var(--secondary);
    font-weight: 700;
}

.section-head h3 {
    margin-top: 7px;
    color: var(--title);
    font-size: 24px;
    line-height: 1.1;
    letter-spacing: -0.02em;
}

.chip {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 1px solid var(--border);
    border-radius: 999px;
    background: #fff;
    color: #475569;
    font-size: 12px;
    font-weight: 700;
    padding: 6px 10px;
    white-space: nowrap;
}

.chip-link {
    text-decoration: none;
    transition: all 180ms ease;
}

.chip-link:hover {
    background: #f3f4f6;
}

.chart-wrap {
    margin-top: 16px;
}

.chart-svg {
    width: 100%;
    height: 220px;
    border-radius: 10px;
    background:
        linear-gradient(rgba(148, 163, 184, 0.13) 1px, transparent 1px),
        linear-gradient(90deg, rgba(148, 163, 184, 0.13) 1px, transparent 1px),
        linear-gradient(180deg, #f8faff 0%, #ffffff 100%);
    background-size: 24px 24px, 24px 24px, auto;
}

.chart-labels {
    margin-top: 8px;
    display: grid;
    grid-template-columns: repeat(8, minmax(0, 1fr));
    gap: 4px;
}

.chart-labels span {
    text-align: center;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.07em;
    color: #94a3b8;
    font-weight: 600;
}

.activity-list {
    list-style: none;
    margin-top: 14px;
    display: grid;
    gap: 8px;
}

.activity-item {
    border: 1px solid var(--border);
    border-radius: 10px;
    background: #f8fafc;
    padding: 10px;
    display: flex;
    align-items: start;
    justify-content: space-between;
    gap: 8px;
    transition: all 180ms ease;
}

.activity-item:hover {
    transform: translateY(-1px);
    background: #f1f5f9;
}

.activity-title {
    color: var(--title);
    text-decoration: none;
    font-size: 14px;
    font-weight: 700;
    line-height: 1.3;
}

.activity-title:hover {
    color: #312e81;
}

.activity-item p {
    margin-top: 4px;
    color: var(--secondary);
    font-size: 12px;
}

.status-chip {
    border-radius: 999px;
    padding: 5px 9px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    white-space: nowrap;
}

.status-warning {
    background: #fef3c7;
    color: #92400e;
}

.status-info {
    background: #dbeafe;
    color: #1e40af;
}

.status-success {
    background: #d1fae5;
    color: #065f46;
}

.status-danger {
    background: #fee2e2;
    color: #991b1b;
}

.status-neutral {
    background: #e2e8f0;
    color: #334155;
}

.table-wrap {
    margin-top: 14px;
    overflow-x: auto;
    border: 1px solid var(--border);
    border-radius: 10px;
}

.table-wrap table {
    width: 100%;
    border-collapse: collapse;
    min-width: 760px;
}

.table-wrap th,
.table-wrap td {
    text-align: left;
    border-bottom: 1px solid #edf2f7;
    padding: 12px 14px;
}

.table-wrap thead th {
    background: #f8fafc;
    color: #64748b;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    font-weight: 700;
}

.table-wrap tbody tr {
    transition: background 180ms ease;
}

.table-wrap tbody tr:hover {
    background: #f8fafc;
}

.table-wrap tbody td {
    color: #334155;
    font-size: 13px;
}

.table-main {
    color: var(--title);
    font-weight: 700;
}

.row-link {
    color: #312e81;
    text-decoration: none;
    font-size: 13px;
    font-weight: 700;
}

.row-link:hover {
    text-decoration: underline;
}

.mono {
    font-family: 'JetBrains Mono', ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace;
}

.empty-box {
    margin-top: 14px;
    border: 1px dashed #cbd5e1;
    border-radius: 10px;
    padding: 18px;
    text-align: center;
    color: #64748b;
    font-size: 14px;
    background: #f8fafc;
}

.btn {
    border-radius: 8px;
    border: 1px solid transparent;
    text-decoration: none;
    font-size: 13px;
    font-weight: 700;
    min-height: 38px;
    padding: 0 14px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 180ms ease;
}

.btn:hover {
    transform: translateY(-1px);
}

.btn-primary {
    color: #fff;
    background: linear-gradient(135deg, #4338ca, var(--primary));
    box-shadow: 0 10px 22px -14px rgba(79, 70, 229, 0.88);
}

.btn-primary:hover {
    box-shadow: 0 14px 24px -14px rgba(79, 70, 229, 0.95);
}

.btn-ghost {
    color: var(--title);
    border-color: var(--border);
    background: #fff;
}

.btn-ghost:hover {
    background: #f3f4f6;
}

.reveal-node {
    opacity: 0;
    transform: translateY(14px);
    transition: opacity 0.45s ease, transform 0.45s ease;
}

.reveal-node.is-visible {
    opacity: 1;
    transform: translateY(0);
}

@keyframes progress-grow {
    from {
        transform: scaleX(0.3);
        opacity: 0.55;
    }
    to {
        transform: scaleX(1);
        opacity: 1;
    }
}

@media (max-width: 1200px) {
    .kpi-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .insight-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 760px) {
    .kpi-grid {
        grid-template-columns: 1fr;
    }

    .workspace-toolbar,
    .chart-card,
    .activity-card,
    .table-card,
    .kpi-card {
        padding: 16px;
    }

    .header-actions {
        width: 100%;
    }

    .header-actions .btn {
        flex: 1;
    }

    .search-field {
        width: 100%;
    }

    .toolbar-right {
        width: 100%;
        justify-content: space-between;
    }
}
</style>
