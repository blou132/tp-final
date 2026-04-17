<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import SectionCard from '@/Components/SectionCard.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from '@/composables/useI18n';

const props = defineProps({
    statuses: {
        type: Array,
        required: true,
    },
});

const { t, locale } = useI18n();

const form = useForm({
    amount: '',
    status: props.statuses[0] ?? 'pending',
});

const currentStatus = computed(() => form.status);

const formatMoney = (value) => {
    const number = Number(value ?? 0);

    return new Intl.NumberFormat(locale.value === 'fr' ? 'fr-FR' : 'en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(number);
};

const submit = () => {
    form.post(route('payments.store'));
};
</script>

<template>
    <Head :title="t('payments.create')" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="page-title">{{ t('payments.create') }}</h2>
                    <p class="page-subtitle">{{ t('payments.create_subtitle') }}</p>
                </div>
                <Link :href="route('payments.index')" class="btn-secondary">{{ t('common.back') }}</Link>
            </div>
        </template>

        <div class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_320px]">
            <SectionCard :title="t('payments.form_title')" :description="t('payments.form_description')">
                <form @submit.prevent="submit" class="space-y-5">
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700">{{ t('common.amount') }}</label>
                        <input
                            v-model="form.amount"
                            type="number"
                            step="0.01"
                            min="0"
                            class="input-base"
                            :placeholder="t('payments.amount_placeholder')"
                        />
                        <p v-if="form.errors.amount" class="mt-1 text-sm text-rose-600">{{ form.errors.amount }}</p>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700">{{ t('common.status') }}</label>
                        <select v-model="form.status" class="select-base">
                            <option v-for="status in statuses" :key="status" :value="status">
                                {{ t(`status.${status}`, status) }}
                            </option>
                        </select>
                        <p v-if="form.errors.status" class="mt-1 text-sm text-rose-600">{{ form.errors.status }}</p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2 pt-2">
                        <button type="submit" :disabled="form.processing" class="btn-primary">
                            {{ t('common.save') }}
                        </button>
                        <Link :href="route('payments.index')" class="btn-ghost">{{ t('common.cancel') }}</Link>
                    </div>
                </form>
            </SectionCard>

            <SectionCard :title="t('payments.preview')" :description="t('payments.preview_hint')">
                <div class="space-y-4 text-sm text-slate-600">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ t('common.status') }}</p>
                        <div class="mt-2"><StatusBadge :status="currentStatus" /></div>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ t('common.amount') }}</p>
                        <p class="mono mt-1 text-lg font-semibold text-slate-900">{{ formatMoney(form.amount) }} €</p>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-600">
                        {{ t('payments.capture_hint') }}
                    </div>
                </div>
            </SectionCard>
        </div>
    </AuthenticatedLayout>
</template>
