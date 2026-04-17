<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useI18n } from '@/composables/useI18n';

const props = defineProps({
    tickets: {
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
        route('tickets.index'),
        {
            status: selectedStatus.value || undefined,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
};

const destroyTicket = (ticketId) => {
    if (!window.confirm('Delete this ticket?')) {
        return;
    }

    router.delete(route('tickets.destroy', ticketId));
};

const formatDate = (value) => {
    if (!value) {
        return '-';
    }

    return new Date(value).toLocaleString();
};
</script>

<template>
    <Head :title="t('tickets.title')" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between gap-4">
                <h2 class="text-xl font-semibold leading-tight text-slate-800">
                    {{ t('tickets.title') }}
                </h2>
                <Link
                    v-if="can.create"
                    :href="route('tickets.create')"
                    class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700"
                >
                    {{ t('tickets.new') }}
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
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">{{ t('common.title') }}</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">{{ t('common.status') }}</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">{{ t('common.owner') }}</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">{{ t('common.flagged') }}</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">{{ t('common.created_at') }}</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-700">{{ t('common.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    <tr v-if="tickets.data.length === 0">
                        <td colspan="7" class="px-4 py-6 text-center text-slate-500">{{ t('tickets.empty') }}</td>
                    </tr>
                    <tr v-for="ticket in tickets.data" :key="ticket.id">
                        <td class="px-4 py-3 text-slate-700">{{ ticket.id }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ ticket.title }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ t(`status.${ticket.status}`, ticket.status) }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ ticket.user?.email ?? '-' }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ ticket.is_flagged ? 'Yes' : 'No' }}</td>
                        <td class="px-4 py-3 text-slate-700">{{ formatDate(ticket.created_at) }}</td>
                        <td class="px-4 py-3 text-slate-700">
                            <div class="flex flex-wrap gap-2">
                                <Link :href="route('tickets.show', ticket.id)" class="text-slate-700 underline">
                                    {{ t('common.details') }}
                                </Link>
                                <Link :href="route('tickets.edit', ticket.id)" class="text-blue-700 underline">
                                    {{ t('common.edit') }}
                                </Link>
                                <button
                                    type="button"
                                    class="text-rose-700 underline"
                                    @click="destroyTicket(ticket.id)"
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
                v-if="tickets.prev_page_url"
                :href="tickets.prev_page_url"
                class="rounded border border-slate-300 bg-white px-3 py-1 hover:bg-slate-50"
            >
                <-
            </Link>
            <span v-else class="rounded border border-slate-200 bg-slate-100 px-3 py-1 text-slate-400"><-</span>

            <span>{{ tickets.current_page }} / {{ tickets.last_page }}</span>

            <Link
                v-if="tickets.next_page_url"
                :href="tickets.next_page_url"
                class="rounded border border-slate-300 bg-white px-3 py-1 hover:bg-slate-50"
            >
                ->
            </Link>
            <span v-else class="rounded border border-slate-200 bg-slate-100 px-3 py-1 text-slate-400">-></span>
        </div>
    </AuthenticatedLayout>
</template>
