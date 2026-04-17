<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { useI18n } from '@/composables/useI18n';

defineProps({
    stats: {
        type: Object,
        required: true,
    },
    recentTickets: {
        type: Array,
        required: true,
    },
});

const { t } = useI18n();
</script>

<template>
    <Head :title="t('dashboard.title')" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-slate-800">
                {{ t('dashboard.title') }}
            </h2>
        </template>

        <p class="mb-6 text-sm text-slate-600">{{ t('dashboard.welcome') }}</p>

        <div class="grid gap-4 md:grid-cols-3">
            <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-sm text-slate-500">{{ t('dashboard.tickets_total') }}</p>
                <p class="text-3xl font-bold text-slate-900">{{ stats.tickets.total }}</p>
                <p class="mt-2 text-xs text-slate-500">
                    {{ t('status.open') }}: {{ stats.tickets.open }} | {{ t('status.in_progress') }}:
                    {{ stats.tickets.in_progress }} | {{ t('status.closed') }}: {{ stats.tickets.closed }}
                </p>
            </div>

            <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-sm text-slate-500">{{ t('dashboard.payments_total') }}</p>
                <p class="text-3xl font-bold text-slate-900">{{ stats.payments.total }}</p>
                <p class="mt-2 text-xs text-slate-500">
                    {{ t('status.pending') }}: {{ stats.payments.pending }} | {{ t('status.paid') }}:
                    {{ stats.payments.paid }} | {{ t('status.failed') }}: {{ stats.payments.failed }}
                </p>
            </div>

            <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-sm text-slate-500">{{ t('dashboard.paid_amount') }}</p>
                <p class="text-3xl font-bold text-slate-900">{{ stats.payments.paid_amount }} €</p>
            </div>
        </div>

        <div class="mt-6 rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <h3 class="text-base font-semibold text-slate-800">{{ t('dashboard.recent_tickets') }}</h3>

            <div v-if="recentTickets.length === 0" class="mt-3 text-sm text-slate-500">
                {{ t('tickets.empty') }}
            </div>

            <ul v-else class="mt-3 divide-y divide-slate-100">
                <li v-for="ticket in recentTickets" :key="ticket.id" class="py-2 text-sm text-slate-700">
                    <span class="font-medium">#{{ ticket.id }}</span> - {{ ticket.title }}
                    <span class="text-slate-500">({{ t(`status.${ticket.status}`, ticket.status) }})</span>
                    <span class="text-slate-400">- {{ ticket.user }}</span>
                </li>
            </ul>
        </div>
    </AuthenticatedLayout>
</template>
