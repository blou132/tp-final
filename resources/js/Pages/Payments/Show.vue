<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import SectionCard from '@/Components/SectionCard.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head, Link } from '@inertiajs/vue3';
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

        <div class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_320px]">
            <SectionCard :title="`${formatMoney(payment.amount)} €`" :description="t('payments.show_subtitle')">
                <div class="mb-5 flex flex-wrap items-center gap-2">
                    <StatusBadge :status="payment.status" />
                </div>

                <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4 text-sm text-slate-700">
                    {{ t('payments.transaction_note') }}
                </div>
            </SectionCard>

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
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ t('common.id') }}</dt>
                        <dd class="mono mt-1 text-slate-700">#{{ payment.id }}</dd>
                    </div>
                </dl>
            </SectionCard>
        </div>
    </AuthenticatedLayout>
</template>
