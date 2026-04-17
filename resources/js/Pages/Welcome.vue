<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { useI18n } from '@/composables/useI18n';

defineProps({
    canLogin: {
        type: Boolean,
        default: false,
    },
    canRegister: {
        type: Boolean,
        default: false,
    },
    laravelVersion: {
        type: String,
        default: '',
    },
    phpVersion: {
        type: String,
        default: '',
    },
});

const { t } = useI18n();

const statCards = computed(() => [
    { label: t('welcome.kpi_1_label'), value: '24h', tone: 'bg-blue-500' },
    { label: t('welcome.kpi_2_label'), value: '99.9%', tone: 'bg-emerald-500' },
    { label: t('welcome.kpi_3_label'), value: '+40%', tone: 'bg-amber-500' },
]);

const moduleCards = computed(() => [
    {
        title: t('welcome.module_tickets_title'),
        description: t('welcome.module_tickets_desc'),
        icon: 'M4 7.5h16M4 12h11M4 16.5h8M3.5 4h17A1.5 1.5 0 0 1 22 5.5v13a1.5 1.5 0 0 1-1.5 1.5h-17A1.5 1.5 0 0 1 2 18.5v-13A1.5 1.5 0 0 1 3.5 4z',
    },
    {
        title: t('welcome.module_payments_title'),
        description: t('welcome.module_payments_desc'),
        icon: 'M4 7h16M4 12h16M4 17h7M3.5 4h17A1.5 1.5 0 0 1 22 5.5v13a1.5 1.5 0 0 1-1.5 1.5h-17A1.5 1.5 0 0 1 2 18.5v-13A1.5 1.5 0 0 1 3.5 4z',
    },
    {
        title: t('welcome.module_roles_title'),
        description: t('welcome.module_roles_desc'),
        icon: 'M16 20a4 4 0 0 0-8 0M12 12a3 3 0 1 0 0-6 3 3 0 0 0 0 6M20 20a3.5 3.5 0 0 0-3.5-3.5M17.5 9a2.5 2.5 0 1 0 0-5',
    },
    {
        title: t('welcome.module_audit_title'),
        description: t('welcome.module_audit_desc'),
        icon: 'M4 5h9l4 4v10a1.5 1.5 0 0 1-1.5 1.5h-11A1.5 1.5 0 0 1 3 19V6.5A1.5 1.5 0 0 1 4.5 5zM13 5v4h4',
    },
]);

const workflowSteps = computed(() => [
    {
        title: t('welcome.workflow_step_1_title'),
        description: t('welcome.workflow_step_1_desc'),
    },
    {
        title: t('welcome.workflow_step_2_title'),
        description: t('welcome.workflow_step_2_desc'),
    },
    {
        title: t('welcome.workflow_step_3_title'),
        description: t('welcome.workflow_step_3_desc'),
    },
]);
</script>

<template>
    <Head :title="t('app_name')" />

    <div class="app-shell min-h-screen px-4 py-8 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl">
            <header class="surface-card reveal mb-8 flex flex-wrap items-center justify-between gap-4 px-5 py-4 sm:px-6">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">{{ t('welcome.hero_kicker') }}</p>
                    <h1 class="text-lg font-bold text-slate-900">{{ t('app_name') }}</h1>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <Link v-if="canLogin" :href="route('login')" class="btn-secondary">{{ t('welcome.cta_login') }}</Link>
                    <Link v-if="canRegister" :href="route('register')" class="btn-primary">{{ t('welcome.cta_register') }}</Link>
                </div>
            </header>

            <section class="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
                <article class="surface-card reveal reveal-delay-1 panel-hover relative overflow-hidden p-6 sm:p-8">
                    <div class="absolute right-[-3rem] top-[-3rem] h-40 w-40 rounded-full bg-blue-200/40 blur-3xl" />
                    <div class="absolute bottom-[-3rem] left-[-2rem] h-32 w-32 rounded-full bg-emerald-200/30 blur-3xl" />

                    <div class="relative">
                        <p class="text-xs font-semibold uppercase tracking-wider text-blue-700">{{ t('welcome.hero_kicker') }}</p>
                        <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">
                            {{ t('welcome.hero_title') }}
                        </h2>
                        <p class="mt-4 max-w-2xl text-sm leading-relaxed text-slate-600 sm:text-base">
                            {{ t('welcome.hero_text') }}
                        </p>
                        <p class="mt-3 text-sm text-slate-500">
                            {{ t('welcome.hero_secondary') }}
                        </p>

                        <div class="mt-6 flex flex-wrap gap-2">
                            <Link v-if="canLogin" :href="route('login')" class="btn-primary">{{ t('welcome.cta_login') }}</Link>
                            <Link v-if="canRegister" :href="route('register')" class="btn-secondary">{{ t('welcome.cta_register') }}</Link>
                        </div>
                    </div>
                </article>

                <aside class="surface-card reveal reveal-delay-2 panel-hover p-6">
                    <h3 class="text-base font-semibold text-slate-900">{{ t('welcome.side_title') }}</h3>
                    <ul class="mt-4 space-y-3">
                        <li class="surface-card-soft flex items-start gap-3 p-3 text-sm text-slate-700">
                            <span class="mt-0.5 h-2.5 w-2.5 rounded-full bg-sky-500" />
                            {{ t('welcome.side_item_1') }}
                        </li>
                        <li class="surface-card-soft flex items-start gap-3 p-3 text-sm text-slate-700">
                            <span class="mt-0.5 h-2.5 w-2.5 rounded-full bg-amber-500" />
                            {{ t('welcome.side_item_2') }}
                        </li>
                        <li class="surface-card-soft flex items-start gap-3 p-3 text-sm text-slate-700">
                            <span class="mt-0.5 h-2.5 w-2.5 rounded-full bg-emerald-500" />
                            {{ t('welcome.side_item_3') }}
                        </li>
                        <li class="surface-card-soft flex items-start gap-3 p-3 text-sm text-slate-700">
                            <span class="mt-0.5 h-2.5 w-2.5 rounded-full bg-indigo-500" />
                            {{ t('welcome.side_item_4') }}
                        </li>
                    </ul>

                    <div class="mt-5 grid grid-cols-3 gap-2">
                        <article v-for="item in statCards" :key="item.label" class="surface-card-soft p-3">
                            <p class="tiny-muted">{{ item.label }}</p>
                            <p class="mt-1 text-xl font-bold text-slate-900">{{ item.value }}</p>
                            <div class="mt-2 h-1 rounded-full bg-slate-200">
                                <div class="h-1 rounded-full" :class="item.tone" :style="{ width: '72%' }" />
                            </div>
                        </article>
                    </div>

                    <div class="mt-5 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-500">
                        Laravel {{ laravelVersion }} • PHP {{ phpVersion }}
                    </div>
                </aside>
            </section>

            <section class="mt-6 reveal reveal-delay-2">
                <div class="mb-4">
                    <h3 class="text-xl font-bold text-slate-900">{{ t('welcome.modules_title') }}</h3>
                    <p class="mt-1 text-sm text-slate-500">{{ t('welcome.modules_subtitle') }}</p>
                </div>

                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <article
                        v-for="module in moduleCards"
                        :key="module.title"
                        class="surface-card panel-hover p-5"
                    >
                        <div class="mb-3 inline-flex rounded-xl bg-slate-900 p-2 text-white">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path :d="module.icon" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </div>
                        <h4 class="text-sm font-semibold text-slate-900">{{ module.title }}</h4>
                        <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ module.description }}</p>
                    </article>
                </div>
            </section>

            <section class="mt-6 surface-card reveal reveal-delay-3 p-6 sm:p-8">
                <div class="mb-4">
                    <h3 class="text-xl font-bold text-slate-900">{{ t('welcome.workflow_title') }}</h3>
                    <p class="mt-1 text-sm text-slate-500">{{ t('welcome.workflow_subtitle') }}</p>
                </div>

                <div class="grid gap-3 md:grid-cols-3">
                    <article
                        v-for="(step, index) in workflowSteps"
                        :key="step.title"
                        class="surface-card-soft panel-hover p-4"
                    >
                        <span class="mono inline-flex rounded-full border border-slate-200 bg-white px-2 py-0.5 text-xs font-semibold text-slate-700">
                            {{ index + 1 }}
                        </span>
                        <h4 class="mt-3 text-sm font-semibold text-slate-900">{{ step.title }}</h4>
                        <p class="mt-1 text-sm text-slate-600">{{ step.description }}</p>
                    </article>
                </div>
            </section>

            <section class="mt-6 surface-card reveal reveal-delay-3 p-6 text-center sm:p-8">
                <h3 class="text-2xl font-bold tracking-tight text-slate-900">{{ t('welcome.final_title') }}</h3>
                <p class="mx-auto mt-3 max-w-3xl text-sm text-slate-600 sm:text-base">
                    {{ t('welcome.final_subtitle') }}
                </p>

                <div class="mt-6 flex flex-wrap justify-center gap-2">
                    <Link v-if="canLogin" :href="route('login')" class="btn-primary">{{ t('welcome.cta_login') }}</Link>
                    <Link v-if="canRegister" :href="route('register')" class="btn-secondary">{{ t('welcome.cta_register') }}</Link>
                </div>
            </section>
        </div>
    </div>
</template>
