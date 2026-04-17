<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import SectionCard from '@/Components/SectionCard.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head, Link } from '@inertiajs/vue3';
import { useI18n } from '@/composables/useI18n';

const props = defineProps({
    ticket: {
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
</script>

<template>
    <Head :title="t('tickets.show')" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="page-title">{{ t('tickets.show') }}</h2>
                    <p class="page-subtitle">#{{ ticket.id }} • {{ ticket.user?.email ?? '-' }}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <Link :href="route('tickets.edit', ticket.id)" class="btn-secondary">{{ t('common.edit') }}</Link>
                    <Link :href="route('tickets.index')" class="btn-ghost">{{ t('common.back') }}</Link>
                </div>
            </div>
        </template>

        <div class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_320px]">
            <SectionCard :title="ticket.title" :description="t('tickets.show_subtitle')">
                <div class="mb-5 flex flex-wrap items-center gap-2">
                    <StatusBadge :status="ticket.status" />
                    <span
                        class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset"
                        :class="
                            ticket.is_flagged
                                ? 'bg-amber-50 text-amber-700 ring-amber-200'
                                : 'bg-slate-100 text-slate-600 ring-slate-200'
                        "
                    >
                        {{ t('common.flagged') }}: {{ ticket.is_flagged ? t('common.yes') : t('common.no') }}
                    </span>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4">
                    <p class="whitespace-pre-wrap text-sm leading-relaxed text-slate-700">{{ ticket.description }}</p>
                </div>
            </SectionCard>

            <SectionCard :title="t('common.details')" :description="t('tickets.meta_hint')">
                <dl class="space-y-4 text-sm">
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ t('common.owner') }}</dt>
                        <dd class="mt-1 text-slate-800">{{ ticket.user?.email ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ t('common.created_at') }}</dt>
                        <dd class="mt-1 text-slate-800">{{ formatDate(ticket.created_at) }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ t('common.updated_at') }}</dt>
                        <dd class="mt-1 text-slate-800">{{ formatDate(ticket.updated_at) }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ t('common.id') }}</dt>
                        <dd class="mono mt-1 text-slate-700">#{{ ticket.id }}</dd>
                    </div>
                </dl>
            </SectionCard>
        </div>
    </AuthenticatedLayout>
</template>
