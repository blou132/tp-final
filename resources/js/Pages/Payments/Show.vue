<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import SectionCard from '@/Components/SectionCard.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from '@/composables/useI18n';

const props = defineProps({
    payment: {
        type: Object,
        required: true,
    },
});

const { t, locale } = useI18n();

const formatDate = (value) => {
    if (!value) {
        return '-';
    }

    return new Intl.DateTimeFormat(locale.value === 'fr' ? 'fr-FR' : 'en-US', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
};

const formatMoney = (value) => {
    const number = Number(value ?? 0);

    return new Intl.NumberFormat(locale.value === 'fr' ? 'fr-FR' : 'en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(number);
};

const amount = computed(() => Number(props.payment.amount ?? 0));

const band = computed(() => {
    if (amount.value >= 1000) {
        return t('payments.band_large');
    }

    if (amount.value >= 300) {
        return t('payments.band_medium');
    }

    return t('payments.band_small');
});

const riskLevel = computed(() => {
    if (props.payment.status === 'failed') {
        return {
            label: t('payments.risk_high'),
            className: 'bg-rose-50 text-rose-700 ring-rose-200',
            note: t('payments.risk_high_note'),
        };
    }

    if (props.payment.status === 'pending') {
        return {
            label: t('payments.risk_medium'),
            className: 'bg-amber-50 text-amber-700 ring-amber-200',
            note: t('payments.risk_medium_note'),
        };
    }

    return {
        label: t('payments.risk_low'),
        className: 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        note: t('payments.risk_low_note'),
    };
});

const reconciliationItems = computed(() => [
    t('payments.reconciliation_item_1'),
    t('payments.reconciliation_item_2'),
    t('payments.reconciliation_item_3'),
]);
</script>

<template>
    <Head :title="t('payments.show')" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="page-title">{{ t('payments.show') }}</h2>
                    <p class="page-subtitle">#{{ payment.id }} • {{ payment.user?.email ?? '-' }}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <Link :href="route('payments.edit', payment.id)" class="btn-secondary">{{ t('common.edit') }}</Link>
                    <Link :href="route('payments.index')" class="btn-ghost">{{ t('common.back') }}</Link>
                </div>
            </div>
        </template>

        <div class="grid gap-4 2xl:grid-cols-[minmax(0,1.55fr)_340px]">
            <div class="space-y-4">
                <SectionCard :title="`${formatMoney(payment.amount)} €`" :description="t('payments.show_subtitle')">
                    <div class="mb-5 flex flex-wrap items-center gap-2">
                        <StatusBadge :status="payment.status" />
                        <span class="pill">{{ t('payments.band_title') }}: {{ band }}</span>
                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset" :class="riskLevel.className">
                            {{ t('payments.risk_level') }}: {{ riskLevel.label }}
                        </span>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4 text-sm text-slate-700">
                        {{ t('payments.transaction_note') }}
                    </div>
                </SectionCard>

                <SectionCard :title="t('payments.reconciliation_title')" :description="t('payments.reconciliation_hint')">
                    <ul class="space-y-2">
                        <li v-for="item in reconciliationItems" :key="item" class="insight-item">
                            {{ item }}
                        </li>
                    </ul>
                </SectionCard>

                <SectionCard :title="t('payments.risk_panel_title')" :description="t('payments.risk_panel_hint')">
                    <div class="grid gap-3 md:grid-cols-2">
                        <div class="insight-item">
                            <p class="tiny-muted">{{ t('payments.risk_level') }}</p>
                            <p class="mt-1 text-sm font-semibold text-slate-900">{{ riskLevel.label }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ riskLevel.note }}</p>
                        </div>
                        <div class="insight-item">
                            <p class="tiny-muted">{{ t('payments.collection_rate') }}</p>
                            <p class="mt-1 text-sm font-semibold text-slate-900">{{ payment.status === 'paid' ? '100%' : '0%' }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ t('payments.risk_panel_collection_hint') }}</p>
                        </div>
                    </div>
                </SectionCard>
            </div>

            <div class="space-y-4">
                <SectionCard :title="t('common.details')" :description="t('payments.meta_hint')">
                    <dl class="space-y-4 text-sm">
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ t('common.owner') }}</dt>
                            <dd class="mt-1 text-slate-800">{{ payment.user?.email ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ t('common.created_at') }}</dt>
                            <dd class="mt-1 text-slate-800">{{ formatDate(payment.created_at) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ t('common.updated_at') }}</dt>
                            <dd class="mt-1 text-slate-800">{{ formatDate(payment.updated_at) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ t('payments.band_title') }}</dt>
                            <dd class="mt-1 text-slate-800">{{ band }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ t('common.id') }}</dt>
                            <dd class="mono mt-1 text-slate-700">#{{ payment.id }}</dd>
                        </div>
                    </dl>
                </SectionCard>

                <SectionCard :title="t('payments.guidance_title')" :description="t('payments.guidance_hint')">
                    <ul class="space-y-2">
                        <li class="insight-item">{{ t('payments.guidance_item_1') }}</li>
                        <li class="insight-item">{{ t('payments.guidance_item_2') }}</li>
                        <li class="insight-item">{{ t('payments.guidance_item_3') }}</li>
                    </ul>
                </SectionCard>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
