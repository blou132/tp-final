<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useI18n } from '@/composables/useI18n';

const props = defineProps({
    payments: {
        type: Object,
        required: true,
    },
    statuses: {
        type: Array,
        required: true,
    },
    filters: {
        type: Object,
        required: true,
    },
    can: {
        type: Object,
        required: true,
    },
});

const { t } = useI18n();
const selectedStatus = ref(props.filters.status ?? '');

const applyFilters = () => {
    router.get(
        route('payments.index'),
        {
            status: selectedStatus.value || undefined,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
};

const destroyPayment = (paymentId) => {
    if (!window.confirm('Delete this payment?')) {
        return;
    }

    router.delete(route('payments.destroy', paymentId));
};

const formatDate = (value) => {
    if (!value) {
        return '-';
    }

    return new Date(value).toLocaleString();
};
</script>

<template>
    <Head :title="t('payments.title')" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-4">
                <h2 class="text-xl font-semibold leading-tight text-slate-800">
                    {{ t('payments.title') }}
                </h2>
                <Link
                    v-if="can.create"
                    :href="route('payments.create')"
                    class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700"
                >
                    {{ t('payments.new') }}
                </Link>
            </div>
        </template>

        <div class="mb-4 rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <div class="flex flex-wrap items-end gap-3">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">{{ t('common.status') }}</label>
                    <select
                        v-model="selectedStatus"
                        class="rounded-md border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500"
                    >
                        <option value="">{{ t('common.all') }}</option>
                        <option v-for="status in statuses" :key="status" :value="status">
                            {{ t(`status.${status}`, status) }}
                        </option>
                    </select>
                </div>

                <button
                    type="button"
                    @click="applyFilters"
                    class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm text-slate-700 hover:bg-slate-50"
                >
                    {{ t('common.filter') }}
                </button>
            </div>
        </div>

        <div class="overflow-x-auto rounded-lg border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">#</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">{{ t('common.amount') }}</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">{{ t('common.status') }}</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">{{ t('common.owner') }}</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">{{ t('common.created_at') }}</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">{{ t('common.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    <tr v-if="payments.data.length === 0">
                        <td colspan="6" class="px-4 py-6 text-center text-slate-500">{{ t('payments.empty') }}</td>
                    </tr>
                    <tr v-for="payment in payments.data" :key="payment.id">
                        <td class="px-4 py-3 text-slate-700">{{ payment.id }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ payment.amount }} €</td>
                        <td class="px-4 py-3 text-slate-700">{{ t(`status.${payment.status}`, payment.status) }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ payment.user?.email ?? '-' }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ formatDate(payment.created_at) }}</td>
                        <td class="px-4 py-3 text-slate-700">
                            <div class="flex flex-wrap gap-2">
                                <Link :href="route('payments.show', payment.id)" class="text-slate-700 underline">
                                    {{ t('common.details') }}
                                </Link>
                                <Link :href="route('payments.edit', payment.id)" class="text-blue-700 underline">
                                    {{ t('common.edit') }}
                                </Link>
                                <button
                                    type="button"
                                    class="text-rose-700 underline"
                                    @click="destroyPayment(payment.id)"
                                >
                                    {{ t('common.delete') }}
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="mt-4 flex items-center justify-between text-sm text-slate-600">
            <Link
                v-if="payments.prev_page_url"
                :href="payments.prev_page_url"
                class="rounded border border-slate-300 bg-white px-3 py-1 hover:bg-slate-50"
            >
                <-
            </Link>
            <span v-else class="rounded border border-slate-200 bg-slate-100 px-3 py-1 text-slate-400"><-</span>

            <span>{{ payments.current_page }} / {{ payments.last_page }}</span>

            <Link
                v-if="payments.next_page_url"
                :href="payments.next_page_url"
                class="rounded border border-slate-300 bg-white px-3 py-1 hover:bg-slate-50"
            >
                ->
            </Link>
            <span v-else class="rounded border border-slate-200 bg-slate-100 px-3 py-1 text-slate-400">-></span>
        </div>
    </AuthenticatedLayout>
</template>
