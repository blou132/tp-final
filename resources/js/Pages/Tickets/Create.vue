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
    priorities: {
        type: Array,
        default: () => [],
    },
    categories: {
        type: Array,
        default: () => [],
    },
    assignableUsers: {
        type: Array,
        default: () => [],
    },
});

const { t } = useI18n();

const form = useForm({
    title: '',
    description: '',
    status: props.statuses[0] ?? 'open',
    priority: props.priorities[1] ?? props.priorities[0] ?? 'medium',
    category: props.categories[0] ?? 'general',
    due_at: '',
    assigned_to: '',
});

const currentStatus = computed(() => form.status);
const hasAssignableUsers = computed(() => props.assignableUsers.length > 0);

const submit = () => {
    form.transform((data) => ({
        ...data,
        due_at: data.due_at || null,
        assigned_to: hasAssignableUsers.value && data.assigned_to !== '' ? Number(data.assigned_to) : null,
    })).post(route('tickets.store'));
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

                    <div class="grid gap-3 md:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-slate-700">{{ t('common.status') }}</label>
                            <select v-model="form.status" class="select-base">
                                <option v-for="status in statuses" :key="status" :value="status">
                                    {{ t(`status.${status}`, status) }}
                                </option>
                            </select>
                            <p v-if="form.errors.status" class="mt-1 text-sm text-rose-600">{{ form.errors.status }}</p>
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-slate-700">{{ t('tickets.priority_label') }}</label>
                            <select v-model="form.priority" class="select-base">
                                <option v-for="priority in priorities" :key="priority" :value="priority">
                                    {{ t(`ticket_priority.${priority}`, priority) }}
                                </option>
                            </select>
                            <p v-if="form.errors.priority" class="mt-1 text-sm text-rose-600">{{ form.errors.priority }}</p>
                        </div>
                    </div>

                    <div class="grid gap-3 md:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-slate-700">{{ t('tickets.category_label') }}</label>
                            <select v-model="form.category" class="select-base">
                                <option v-for="category in categories" :key="category" :value="category">
                                    {{ t(`ticket_category.${category}`, category) }}
                                </option>
                            </select>
                            <p v-if="form.errors.category" class="mt-1 text-sm text-rose-600">{{ form.errors.category }}</p>
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-semibold text-slate-700">{{ t('tickets.due_at_label') }}</label>
                            <input v-model="form.due_at" type="datetime-local" class="input-base" />
                            <p v-if="form.errors.due_at" class="mt-1 text-sm text-rose-600">{{ form.errors.due_at }}</p>
                        </div>
                    </div>

                    <div v-if="hasAssignableUsers">
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700">{{ t('tickets.assignee_label') }}</label>
                        <select v-model="form.assigned_to" class="select-base">
                            <option value="">{{ t('tickets.unassigned') }}</option>
                            <option v-for="user in assignableUsers" :key="user.id" :value="String(user.id)">
                                {{ user.name }} • {{ user.email }}
                            </option>
                        </select>
                        <p v-if="form.errors.assigned_to" class="mt-1 text-sm text-rose-600">{{ form.errors.assigned_to }}</p>
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
                    <div class="grid grid-cols-2 gap-2 text-xs text-slate-600">
                        <span class="pill">{{ t('tickets.priority_label') }}: {{ t(`ticket_priority.${form.priority}`, form.priority) }}</span>
                        <span class="pill">{{ t('tickets.category_label') }}: {{ t(`ticket_category.${form.category}`, form.category) }}</span>
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
