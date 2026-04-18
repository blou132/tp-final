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

const { t, locale, supportedLocales } = useI18n();

const localeSwitchHref = (localeCode) =>
    route('locale.switch', {
        locale: localeCode,
    });

const navLinks = computed(() => [
    { label: t('welcome.nav_features'), href: '#features' },
    { label: t('welcome.nav_operations'), href: '#operations' },
    { label: t('welcome.nav_security'), href: '#security' },
    { label: t('welcome.nav_faq'), href: '#faq' },
]);

const statCards = computed(() => [
    { label: t('welcome.metric_sla_label'), value: '98%', note: t('welcome.metric_sla_note'), tone: 'bg-sky-500', bar: 98 },
    { label: t('welcome.metric_revenue_label'), value: '€ 1.2M', note: t('welcome.metric_revenue_note'), tone: 'bg-emerald-500', bar: 82 },
    {
        label: t('welcome.metric_resolution_label'),
        value: '2.8h',
        note: t('welcome.metric_resolution_note'),
        tone: 'bg-amber-500',
        bar: 74,
    },
    { label: t('welcome.metric_uptime_label'), value: '99.95%', note: t('welcome.metric_uptime_note'), tone: 'bg-indigo-500', bar: 99 },
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

const whyItems = computed(() => [
    { title: t('welcome.why_1_title'), description: t('welcome.why_1_desc') },
    { title: t('welcome.why_2_title'), description: t('welcome.why_2_desc') },
    { title: t('welcome.why_3_title'), description: t('welcome.why_3_desc') },
]);

const ticketItems = computed(() => [
    t('welcome.tickets_block_item_1'),
    t('welcome.tickets_block_item_2'),
    t('welcome.tickets_block_item_3'),
    t('welcome.tickets_block_item_4'),
]);

const paymentItems = computed(() => [
    t('welcome.payments_block_item_1'),
    t('welcome.payments_block_item_2'),
    t('welcome.payments_block_item_3'),
    t('welcome.payments_block_item_4'),
]);

const securityItems = computed(() => [
    t('welcome.security_block_item_1'),
    t('welcome.security_block_item_2'),
    t('welcome.security_block_item_3'),
    t('welcome.security_block_item_4'),
]);

const workflowSteps = computed(() => [
    { title: t('welcome.workflow_step_1_title'), description: t('welcome.workflow_step_1_desc') },
    { title: t('welcome.workflow_step_2_title'), description: t('welcome.workflow_step_2_desc') },
    { title: t('welcome.workflow_step_3_title'), description: t('welcome.workflow_step_3_desc') },
    { title: t('welcome.workflow_step_4_title'), description: t('welcome.workflow_step_4_desc') },
]);

const benefitItems = computed(() => [
    { title: t('welcome.benefit_1_title'), description: t('welcome.benefit_1_desc') },
    { title: t('welcome.benefit_2_title'), description: t('welcome.benefit_2_desc') },
    { title: t('welcome.benefit_3_title'), description: t('welcome.benefit_3_desc') },
    { title: t('welcome.benefit_4_title'), description: t('welcome.benefit_4_desc') },
]);

const faqItems = computed(() => [
    { question: t('welcome.faq_q1'), answer: t('welcome.faq_a1') },
    { question: t('welcome.faq_q2'), answer: t('welcome.faq_a2') },
    { question: t('welcome.faq_q3'), answer: t('welcome.faq_a3') },
    { question: t('welcome.faq_q4'), answer: t('welcome.faq_a4') },
]);
</script>

<template>
    <Head :title="t('app_name')" />

    <div class="app-shell min-h-screen px-4 py-8 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl">
            <header class="surface-card reveal mb-8 flex flex-wrap items-center justify-between gap-4 px-5 py-4 sm:px-6">
                <div class="flex items-center gap-3">
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-slate-900 text-sm font-bold text-white">SP</span>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">{{ t('welcome.hero_kicker') }}</p>
                        <h1 class="text-lg font-bold text-slate-900">{{ t('app_name') }}</h1>
                    </div>
                </div>

                <nav class="hidden items-center gap-1 rounded-xl border border-slate-200 bg-white p-1 lg:flex">
                    <a
                        v-for="item in navLinks"
                        :key="item.href"
                        :href="item.href"
                        class="rounded-lg px-3 py-1.5 text-xs font-semibold uppercase tracking-wide text-slate-600 transition hover:bg-slate-100 hover:text-slate-900"
                    >
                        {{ item.label }}
                    </a>
                </nav>

                <div class="flex flex-wrap items-center gap-2">
                    <div class="inline-flex items-center gap-1 rounded-xl border border-slate-200 bg-white p-1">
                        <Link
                            v-for="localeCode in supportedLocales"
                            :key="localeCode"
                            :href="localeSwitchHref(localeCode)"
                            :class="[
                                'rounded-lg px-2.5 py-1 text-xs font-semibold uppercase transition',
                                locale === localeCode
                                    ? 'bg-slate-900 text-white'
                                    : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900',
                            ]"
                        >
                            {{ localeCode }}
                        </Link>
                    </div>

                    <Link v-if="canLogin" :href="route('login')" class="btn-secondary">{{ t('welcome.cta_login') }}</Link>
                    <Link v-if="canRegister" :href="route('register')" class="btn-primary">{{ t('welcome.cta_register') }}</Link>
                </div>
            </header>

            <section class="grid gap-6 lg:grid-cols-[1.1fr_0.9fr]" id="hero">
                <article class="surface-card reveal reveal-delay-1 panel-hover relative overflow-hidden p-6 sm:p-8">
                    <div class="absolute right-[-3rem] top-[-3rem] h-44 w-44 rounded-full bg-blue-200/40 blur-3xl" />
                    <div class="absolute bottom-[-3rem] left-[-2rem] h-32 w-32 rounded-full bg-emerald-200/30 blur-3xl" />

                    <div class="relative">
                        <span class="pill">{{ t('welcome.hero_badge') }}</span>
                        <h2 class="mt-4 text-3xl font-bold tracking-tight text-slate-900 sm:text-5xl">
                            <span class="gradient-text">{{ t('welcome.hero_title') }}</span>
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

                        <div class="mt-6 flex flex-wrap gap-2">
                            <span class="pill">{{ t('welcome.hero_chip_1') }}</span>
                            <span class="pill">{{ t('welcome.hero_chip_2') }}</span>
                            <span class="pill">{{ t('welcome.hero_chip_3') }}</span>
                        </div>
                    </div>
                </article>

                <aside class="surface-card-strong reveal reveal-delay-2 panel-hover hero-grid p-6">
                    <p class="section-kicker text-slate-300">{{ t('welcome.live_title') }}</p>
                    <h3 class="mt-2 text-2xl font-semibold">{{ t('welcome.live_subtitle') }}</h3>

                    <div class="mt-5 space-y-2">
                        <div class="insight-item border-slate-700 bg-slate-800/80 text-slate-100">
                            <div class="mb-1 flex items-center justify-between text-xs text-slate-300">
                                <span>{{ t('welcome.live_item_1') }}</span>
                                <span class="mono">42</span>
                            </div>
                            <div class="progress-track bg-slate-700/90">
                                <div class="progress-fill bg-sky-400" style="width: 68%" />
                            </div>
                        </div>
                        <div class="insight-item border-slate-700 bg-slate-800/80 text-slate-100">
                            <div class="mb-1 flex items-center justify-between text-xs text-slate-300">
                                <span>{{ t('welcome.live_item_2') }}</span>
                                <span class="mono">18</span>
                            </div>
                            <div class="progress-track bg-slate-700/90">
                                <div class="progress-fill bg-amber-400" style="width: 52%" />
                            </div>
                        </div>
                        <div class="insight-item border-slate-700 bg-slate-800/80 text-slate-100">
                            <div class="mb-1 flex items-center justify-between text-xs text-slate-300">
                                <span>{{ t('welcome.live_item_3') }}</span>
                                <span class="mono">€ 64k</span>
                            </div>
                            <div class="progress-track bg-slate-700/90">
                                <div class="progress-fill bg-emerald-400" style="width: 84%" />
                            </div>
                        </div>
                        <div class="insight-item border-slate-700 bg-slate-800/80 text-slate-100">
                            <div class="mb-1 flex items-center justify-between text-xs text-slate-300">
                                <span>{{ t('welcome.live_item_4') }}</span>
                                <span class="mono">99.95%</span>
                            </div>
                            <div class="progress-track bg-slate-700/90">
                                <div class="progress-fill bg-indigo-400" style="width: 96%" />
                            </div>
                        </div>
                    </div>

                    <p class="mt-4 text-xs text-slate-300">
                        {{ t('welcome.live_note') }}
                    </p>
                </aside>
            </section>

            <section class="mt-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                <article v-for="item in statCards" :key="item.label" class="surface-card reveal reveal-delay-2 panel-hover p-4">
                    <p class="tiny-muted">{{ item.label }}</p>
                    <p class="mt-2 text-2xl font-bold text-slate-900">{{ item.value }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ item.note }}</p>
                    <div class="progress-track mt-3">
                        <div class="progress-fill" :class="item.tone" :style="{ width: `${item.bar}%` }" />
                    </div>
                </article>
            </section>

            <section class="mt-8" id="features">
                <div class="mb-4">
                    <p class="section-kicker">{{ t('welcome.modules_title') }}</p>
                    <h3 class="mt-2 text-2xl font-bold text-slate-900">{{ t('welcome.modules_subtitle') }}</h3>
                </div>

                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <article v-for="module in moduleCards" :key="module.title" class="surface-card reveal panel-hover p-5">
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

            <section class="mt-8">
                <div class="mb-4">
                    <p class="section-kicker">{{ t('welcome.why_title') }}</p>
                    <h3 class="mt-2 text-2xl font-bold text-slate-900">{{ t('welcome.why_subtitle') }}</h3>
                </div>

                <div class="grid gap-4 md:grid-cols-3">
                    <article v-for="item in whyItems" :key="item.title" class="surface-card reveal panel-hover p-5">
                        <h4 class="text-base font-semibold text-slate-900">{{ item.title }}</h4>
                        <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ item.description }}</p>
                    </article>
                </div>
            </section>

            <section class="mt-8 space-y-4" id="operations">
                <article class="surface-card reveal p-6">
                    <div class="grid gap-5 lg:grid-cols-[1.1fr_0.9fr]">
                        <div>
                            <p class="section-kicker">{{ t('welcome.tickets_block_title') }}</p>
                            <h3 class="mt-2 text-2xl font-bold text-slate-900">{{ t('welcome.tickets_block_subtitle') }}</h3>
                            <ul class="mt-4 space-y-2">
                                <li v-for="item in ticketItems" :key="item" class="insight-item flex items-start gap-3">
                                    <span class="data-dot mt-1 bg-sky-500" />
                                    <span>{{ item }}</span>
                                </li>
                            </ul>
                        </div>
                        <div class="surface-card-soft p-4">
                            <p class="tiny-muted">{{ t('welcome.tickets_block_card_title') }}</p>
                            <div class="mt-3 space-y-2">
                                <div class="flex items-center justify-between rounded-lg bg-white px-3 py-2 text-sm text-slate-700">
                                    <span>{{ t('welcome.tickets_block_card_line_1') }}</span><span class="mono">14</span>
                                </div>
                                <div class="flex items-center justify-between rounded-lg bg-white px-3 py-2 text-sm text-slate-700">
                                    <span>{{ t('welcome.tickets_block_card_line_2') }}</span><span class="mono">8</span>
                                </div>
                                <div class="flex items-center justify-between rounded-lg bg-white px-3 py-2 text-sm text-slate-700">
                                    <span>{{ t('welcome.tickets_block_card_line_3') }}</span><span class="mono">96%</span>
                                </div>
                            </div>
                            <p class="mt-3 text-xs text-slate-500">{{ t('welcome.tickets_block_card_note') }}</p>
                        </div>
                    </div>
                </article>

                <article class="surface-card reveal p-6">
                    <div class="grid gap-5 lg:grid-cols-[0.9fr_1.1fr]">
                        <div class="surface-card-soft p-4">
                            <p class="tiny-muted">{{ t('welcome.payments_block_card_title') }}</p>
                            <div class="mt-3 space-y-2">
                                <div class="flex items-center justify-between rounded-lg bg-white px-3 py-2 text-sm text-slate-700">
                                    <span>{{ t('welcome.payments_block_card_line_1') }}</span><span class="mono">€ 52k</span>
                                </div>
                                <div class="flex items-center justify-between rounded-lg bg-white px-3 py-2 text-sm text-slate-700">
                                    <span>{{ t('welcome.payments_block_card_line_2') }}</span><span class="mono">3.2%</span>
                                </div>
                                <div class="flex items-center justify-between rounded-lg bg-white px-3 py-2 text-sm text-slate-700">
                                    <span>{{ t('welcome.payments_block_card_line_3') }}</span><span class="mono">11m</span>
                                </div>
                            </div>
                            <p class="mt-3 text-xs text-slate-500">{{ t('welcome.payments_block_card_note') }}</p>
                        </div>
                        <div>
                            <p class="section-kicker">{{ t('welcome.payments_block_title') }}</p>
                            <h3 class="mt-2 text-2xl font-bold text-slate-900">{{ t('welcome.payments_block_subtitle') }}</h3>
                            <ul class="mt-4 space-y-2">
                                <li v-for="item in paymentItems" :key="item" class="insight-item flex items-start gap-3">
                                    <span class="data-dot mt-1 bg-emerald-500" />
                                    <span>{{ item }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </article>
            </section>

            <section class="mt-8" id="security">
                <article class="surface-card reveal p-6">
                    <div class="grid gap-5 lg:grid-cols-[1fr_360px]">
                        <div>
                            <p class="section-kicker">{{ t('welcome.security_block_title') }}</p>
                            <h3 class="mt-2 text-2xl font-bold text-slate-900">{{ t('welcome.security_block_subtitle') }}</h3>
                            <ul class="mt-4 grid gap-2 md:grid-cols-2">
                                <li v-for="item in securityItems" :key="item" class="insight-item flex items-start gap-3">
                                    <span class="data-dot mt-1 bg-indigo-500" />
                                    <span>{{ item }}</span>
                                </li>
                            </ul>
                        </div>

                        <div class="surface-card-soft p-4">
                            <p class="tiny-muted">{{ t('welcome.security_block_card_title') }}</p>
                            <div class="mt-3 space-y-2">
                                <div class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700">
                                    {{ t('welcome.security_block_card_line_1') }}
                                </div>
                                <div class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700">
                                    {{ t('welcome.security_block_card_line_2') }}
                                </div>
                                <div class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700">
                                    {{ t('welcome.security_block_card_line_3') }}
                                </div>
                            </div>
                            <p class="mt-3 text-xs text-slate-500">{{ t('welcome.security_block_card_note') }}</p>
                        </div>
                    </div>
                </article>
            </section>

            <section class="mt-8 surface-card reveal p-6 sm:p-8">
                <div class="mb-4">
                    <p class="section-kicker">{{ t('welcome.workflow_title') }}</p>
                    <h3 class="mt-2 text-2xl font-bold text-slate-900">{{ t('welcome.workflow_subtitle') }}</h3>
                </div>

                <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                    <article v-for="(step, index) in workflowSteps" :key="step.title" class="surface-card-soft panel-hover p-4">
                        <span class="mono inline-flex rounded-full border border-slate-200 bg-white px-2 py-0.5 text-xs font-semibold text-slate-700">
                            {{ index + 1 }}
                        </span>
                        <h4 class="mt-3 text-sm font-semibold text-slate-900">{{ step.title }}</h4>
                        <p class="mt-1 text-sm text-slate-600">{{ step.description }}</p>
                    </article>
                </div>
            </section>

            <section class="mt-8" id="benefits">
                <div class="mb-4">
                    <p class="section-kicker">{{ t('welcome.benefits_title') }}</p>
                    <h3 class="mt-2 text-2xl font-bold text-slate-900">{{ t('welcome.benefits_subtitle') }}</h3>
                </div>

                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <article v-for="item in benefitItems" :key="item.title" class="surface-card reveal panel-hover p-5">
                        <h4 class="text-sm font-semibold text-slate-900">{{ item.title }}</h4>
                        <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ item.description }}</p>
                    </article>
                </div>
            </section>

            <section class="mt-8" id="faq">
                <div class="mb-4">
                    <p class="section-kicker">{{ t('welcome.faq_title') }}</p>
                    <h3 class="mt-2 text-2xl font-bold text-slate-900">{{ t('welcome.faq_subtitle') }}</h3>
                </div>

                <div class="grid gap-3 lg:grid-cols-2">
                    <details v-for="faq in faqItems" :key="faq.question" class="faq-item reveal">
                        <summary>{{ faq.question }}</summary>
                        <p>{{ faq.answer }}</p>
                    </details>
                </div>
            </section>

            <section class="mt-8 surface-card reveal p-6 text-center sm:p-8">
                <h3 class="text-2xl font-bold tracking-tight text-slate-900">{{ t('welcome.final_title') }}</h3>
                <p class="mx-auto mt-3 max-w-3xl text-sm text-slate-600 sm:text-base">
                    {{ t('welcome.final_subtitle') }}
                </p>

                <div class="mt-6 flex flex-wrap justify-center gap-2">
                    <Link v-if="canLogin" :href="route('login')" class="btn-primary">{{ t('welcome.cta_login') }}</Link>
                    <Link v-if="canRegister" :href="route('register')" class="btn-secondary">{{ t('welcome.cta_register') }}</Link>
                </div>
            </section>

            <footer class="mt-8 surface-card reveal p-6">
                <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-4">
                    <div>
                        <h4 class="text-sm font-semibold text-slate-900">{{ t('app_name') }}</h4>
                        <p class="mt-2 text-sm text-slate-600">{{ t('welcome.footer_tagline') }}</p>
                        <p class="mt-3 text-xs text-slate-500">Laravel {{ laravelVersion }} • PHP {{ phpVersion }}</p>
                    </div>

                    <div>
                        <p class="tiny-muted">{{ t('welcome.footer_col_product') }}</p>
                        <ul class="mt-2 space-y-1 text-sm text-slate-600">
                            <li>{{ t('welcome.footer_link_dashboard') }}</li>
                            <li>{{ t('welcome.footer_link_tickets') }}</li>
                            <li>{{ t('welcome.footer_link_payments') }}</li>
                        </ul>
                    </div>

                    <div>
                        <p class="tiny-muted">{{ t('welcome.footer_col_resources') }}</p>
                        <ul class="mt-2 space-y-1 text-sm text-slate-600">
                            <li>{{ t('welcome.footer_link_api') }}</li>
                            <li>{{ t('welcome.footer_link_security') }}</li>
                            <li>{{ t('welcome.footer_link_docs') }}</li>
                        </ul>
                    </div>

                    <div>
                        <p class="tiny-muted">{{ t('welcome.footer_col_company') }}</p>
                        <ul class="mt-2 space-y-1 text-sm text-slate-600">
                            <li>{{ t('welcome.footer_link_about') }}</li>
                            <li>{{ t('welcome.footer_link_status') }}</li>
                            <li>{{ t('welcome.footer_link_contact') }}</li>
                        </ul>
                    </div>
                </div>

                <div class="mt-6 border-t border-slate-200 pt-4 text-xs text-slate-500">
                    {{ t('welcome.footer_rights') }}
                </div>
            </footer>
        </div>
    </div>
</template>
