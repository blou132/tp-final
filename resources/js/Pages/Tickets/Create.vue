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

const { t } = useI18n();

const form = useForm({
    title: '',
    description: '',
    status: props.statuses[0] ?? 'open',
});

const currentStatus = computed(() => form.status);

const submit = () => {
    form.post(route('tickets.store'));
};
</script>

<template>
    <Head :title="t('tickets.create')" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="page-title">{{ t('tickets.create') }}</h2>
                    <p class="page-subtitle">{{ t('tickets.create_subtitle') }}</p>
                </div>
                <Link :href="route('tickets.index')" class="btn-secondary">{{ t('common.back') }}</Link>
            </div>
        </template>

        <div class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_320px]">
            <SectionCard :title="t('tickets.form_title')" :description="t('tickets.form_description')">
                <form @submit.prevent="submit" class="space-y-5">
                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700">{{ t('common.title') }}</label>
                        <input v-model="form.title" type="text" class="input-base" :placeholder="t('tickets.title_placeholder')" />
                        <p v-if="form.errors.title" class="mt-1 text-sm text-rose-600">{{ form.errors.title }}</p>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700">{{ t('common.description') }}</label>
                        <textarea
                            v-model="form.description"
                            rows="7"
                            class="textarea-base"
                            :placeholder="t('tickets.description_placeholder')"
                        />
                        <p v-if="form.errors.description" class="mt-1 text-sm text-rose-600">{{ form.errors.description }}</p>
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
                        <Link :href="route('tickets.index')" class="btn-ghost">{{ t('common.cancel') }}</Link>
                    </div>
                </form>
            </SectionCard>

            <SectionCard :title="t('tickets.preview')" :description="t('tickets.preview_hint')">
                <div class="space-y-4 text-sm text-slate-600">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ t('common.status') }}</p>
                        <div class="mt-2"><StatusBadge :status="currentStatus" /></div>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ t('common.title') }}</p>
                        <p class="mt-1 text-sm font-medium text-slate-900">{{ form.title || t('tickets.title_placeholder') }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ t('common.description') }}</p>
                        <p class="mt-1 whitespace-pre-wrap text-sm text-slate-700">
                            {{ form.description || t('tickets.description_placeholder') }}
                        </p>
                    </div>
                    <div class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800">
                        {{ t('tickets.moderation_hint') }}
                    </div>
                </div>
            </SectionCard>
        </div>
    </AuthenticatedLayout>
</template>
