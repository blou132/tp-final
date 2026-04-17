<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { useI18n } from '@/composables/useI18n';

const props = defineProps({
    statuses: {
        type: Array,
        required: true,
    },
});

const { t } = useI18n();

const form = useForm({
    title: '',
    description: '',
    status: props.statuses[0] ?? 'open',
});

const submit = () => {
    form.post(route('tickets.store'));
};
</script>

<template>
    <Head :title="t('tickets.create')" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-slate-800">{{ t('tickets.create') }}</h2>
        </template>

        <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">{{ t('common.title') }}</label>
                    <input
                        v-model="form.title"
                        type="text"
                        class="w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500"
                    />
                    <p v-if="form.errors.title" class="mt-1 text-sm text-rose-600">{{ form.errors.title }}</p>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">{{ t('common.description') }}</label>
                    <textarea
                        v-model="form.description"
                        rows="5"
                        class="w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500"
                    />
                    <p v-if="form.errors.description" class="mt-1 text-sm text-rose-600">{{ form.errors.description }}</p>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">{{ t('common.status') }}</label>
                    <select
                        v-model="form.status"
                        class="w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500"
                    >
                        <option v-for="status in statuses" :key="status" :value="status">
                            {{ t(`status.${status}`, status) }}
                        </option>
                    </select>
                    <p v-if="form.errors.status" class="mt-1 text-sm text-rose-600">{{ form.errors.status }}</p>
                </div>

                <div class="flex items-center gap-3">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700 disabled:opacity-50"
                    >
                        {{ t('common.save') }}
                    </button>
                    <Link :href="route('tickets.index')" class="text-sm text-slate-600 underline">
                        {{ t('common.cancel') }}
                    </Link>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
