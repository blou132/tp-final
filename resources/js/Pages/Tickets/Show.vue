<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { useI18n } from '@/composables/useI18n';

const props = defineProps({
    ticket: {
        type: Object,
        required: true,
    },
});

const { t } = useI18n();

const formatDate = (value) => {
    if (!value) {
        return '-';
    }

    return new Date(value).toLocaleString();
};
</script>

<template>
    <Head :title="t('tickets.show')" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-slate-800">{{ t('tickets.show') }}</h2>
        </template>

        <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <dl class="grid gap-4 text-sm text-slate-700 md:grid-cols-2">
                <div>
                    <dt class="font-semibold">{{ t('common.title') }}</dt>
                    <dd>{{ ticket.title }}</dd>
                </div>
                <div>
                    <dt class="font-semibold">{{ t('common.status') }}</dt>
                    <dd>{{ t(`status.${ticket.status}`, ticket.status) }}</dd>
                </div>
                <div class="md:col-span-2">
                    <dt class="font-semibold">{{ t('common.description') }}</dt>
                    <dd class="whitespace-pre-wrap">{{ ticket.description }}</dd>
                </div>
                <div>
                    <dt class="font-semibold">{{ t('common.owner') }}</dt>
                    <dd>{{ ticket.user?.email ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="font-semibold">{{ t('common.flagged') }}</dt>
                    <dd>{{ ticket.is_flagged ? 'Yes' : 'No' }}</dd>
                </div>
                <div>
                    <dt class="font-semibold">{{ t('common.created_at') }}</dt>
                    <dd>{{ formatDate(ticket.created_at) }}</dd>
                </div>
                <div>
                    <dt class="font-semibold">{{ t('common.updated_at') }}</dt>
                    <dd>{{ formatDate(ticket.updated_at) }}</dd>
                </div>
            </dl>

            <div class="mt-6 flex items-center gap-3">
                <Link :href="route('tickets.edit', ticket.id)" class="text-blue-700 underline">
                    {{ t('common.edit') }}
                </Link>
                <Link :href="route('tickets.index')" class="text-slate-600 underline">
                    {{ t('common.back') }}
                </Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
