<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { useI18n } from '@/composables/useI18n';

const props = defineProps({
    payment: {
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
    <Head :title="t('payments.show')" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-slate-800">{{ t('payments.show') }}</h2>
        </template>

        <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <dl class="grid gap-4 text-sm text-slate-700 md:grid-cols-2">
                <div>
                    <dt class="font-semibold">{{ t('common.amount') }}</dt>
                    <dd>{{ payment.amount }} €</dd>
                </div>
                <div>
                    <dt class="font-semibold">{{ t('common.status') }}</dt>
                    <dd>{{ t(`status.${payment.status}`, payment.status) }}</dd>
                </div>
                <div>
                    <dt class="font-semibold">{{ t('common.owner') }}</dt>
                    <dd>{{ payment.user?.email ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="font-semibold">{{ t('common.created_at') }}</dt>
                    <dd>{{ formatDate(payment.created_at) }}</dd>
                </div>
                <div>
                    <dt class="font-semibold">{{ t('common.updated_at') }}</dt>
                    <dd>{{ formatDate(payment.updated_at) }}</dd>
                </div>
            </dl>

            <div class="mt-6 flex items-center gap-3">
                <Link :href="route('payments.edit', payment.id)" class="text-blue-700 underline">
                    {{ t('common.edit') }}
                </Link>
                <Link :href="route('payments.index')" class="text-slate-600 underline">
                    {{ t('common.back') }}
                </Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
