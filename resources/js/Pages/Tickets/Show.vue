<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import SectionCard from '@/Components/SectionCard.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
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

const ageHours = computed(() => {
    if (!props.ticket?.created_at) {
        return 0;
    }

    return Math.max(0, Math.floor((Date.now() - new Date(props.ticket.created_at).getTime()) / 36e5));
});

const ageLabel = computed(() => {
    if (ageHours.value < 24) {
        return `${ageHours.value}h`;
    }

    const days = Math.floor(ageHours.value / 24);
    const hours = ageHours.value % 24;

    return `${days}d ${hours}h`;
});

const dueStatus = computed(() => {
    if (!props.ticket.due_at || props.ticket.status === 'closed') {
        return {
            label: t('tickets.no_due_date'),
            className: 'bg-slate-100 text-slate-700 ring-slate-200',
        };
    }

    const dueDate = new Date(props.ticket.due_at);

    if (dueDate.getTime() < Date.now()) {
        return {
            label: t('tickets.overdue'),
            className: 'bg-rose-50 text-rose-700 ring-rose-200',
        };
    }

    return {
        label: t('tickets.on_track'),
        className: 'bg-emerald-50 text-emerald-700 ring-emerald-200',
    };
});

const nextAction = computed(() => {
    if (props.ticket.status === 'open') {
        return t('tickets.next_action_open');
    }

    if (props.ticket.status === 'in_progress') {
        return t('tickets.next_action_in_progress');
    }

    return t('tickets.next_action_closed');
});

const timelineItems = computed(() => [
    {
        label: t('tickets.timeline_created'),
        value: formatDate(props.ticket.created_at),
    },
    {
        label: t('tickets.timeline_updated'),
        value: formatDate(props.ticket.updated_at),
    },
    {
        label: t('tickets.timeline_age'),
        value: ageLabel.value,
    },
]);
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

        <div class="grid gap-4 2xl:grid-cols-[minmax(0,1.55fr)_340px]">
            <div class="space-y-4">
                <SectionCard :title="ticket.title" :description="t('tickets.show_subtitle')">
                    <div class="mb-5 flex flex-wrap items-center gap-2">
                        <StatusBadge :status="ticket.status" />
                        <span class="pill">{{ t(`ticket_priority.${ticket.priority}`, ticket.priority) }}</span>
                        <span class="pill">{{ t(`ticket_category.${ticket.category}`, ticket.category) }}</span>
                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset" :class="dueStatus.className">
                            {{ dueStatus.label }}
                        </span>
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

                <SectionCard :title="t('tickets.timeline_title')" :description="t('tickets.timeline_hint')">
                    <div class="grid gap-3 sm:grid-cols-3">
                        <div v-for="item in timelineItems" :key="item.label" class="surface-card-soft px-3 py-3">
                            <p class="tiny-muted">{{ item.label }}</p>
                            <p class="mt-1 text-sm font-semibold text-slate-800">{{ item.value }}</p>
                        </div>
                    </div>
                </SectionCard>

                <SectionCard :title="t('tickets.operational_summary_title')" :description="t('tickets.operational_summary_hint')">
                    <div class="grid gap-3 md:grid-cols-2">
                        <div class="insight-item">
                            <p class="tiny-muted">{{ t('tickets.next_action_title') }}</p>
                            <p class="mt-1 text-sm font-semibold text-slate-900">{{ nextAction }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ t('tickets.next_action_hint') }}</p>
                        </div>
                        <div class="insight-item">
                            <p class="tiny-muted">{{ t('tickets.assignee_label') }}</p>
                            <p class="mt-1 text-sm font-semibold text-slate-900">{{ ticket.assignee?.email ?? t('tickets.unassigned') }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ t('tickets.assignee_hint') }}</p>
                        </div>
                    </div>
                </SectionCard>
            </div>

            <div class="space-y-4">
                <SectionCard :title="t('common.details')" :description="t('tickets.meta_hint')">
                    <dl class="space-y-4 text-sm">
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ t('common.owner') }}</dt>
                            <dd class="mt-1 text-slate-800">{{ ticket.user?.email ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ t('tickets.assignee_label') }}</dt>
                            <dd class="mt-1 text-slate-800">{{ ticket.assignee?.email ?? t('tickets.unassigned') }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ t('tickets.due_at_label') }}</dt>
                            <dd class="mt-1 text-slate-800">{{ formatDate(ticket.due_at) }}</dd>
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
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ t('common.age') }}</dt>
                            <dd class="mono mt-1 text-slate-700">{{ ageLabel }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ t('common.id') }}</dt>
                            <dd class="mono mt-1 text-slate-700">#{{ ticket.id }}</dd>
                        </div>
                    </dl>
                </SectionCard>

                <SectionCard :title="t('tickets.guidance_title')" :description="t('tickets.guidance_hint')">
                    <ul class="space-y-2">
                        <li class="insight-item">{{ t('tickets.guidance_item_1') }}</li>
                        <li class="insight-item">{{ t('tickets.guidance_item_2') }}</li>
                        <li class="insight-item">{{ t('tickets.guidance_item_3') }}</li>
                    </ul>
                </SectionCard>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
