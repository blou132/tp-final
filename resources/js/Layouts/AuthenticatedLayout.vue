<script setup>
import { computed, ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { useI18n } from '@/composables/useI18n';

const page = usePage();
const mobileSidebarOpen = ref(false);
const { t, locale, supportedLocales } = useI18n();

const flashSuccess = computed(() => page.props.flash?.success ?? null);
const flashError = computed(() => page.props.flash?.error ?? null);

const pageTitleMap = {
    dashboard: 'nav.dashboard',
    tickets: 'nav.tickets',
    payments: 'nav.payments',
    profile: 'nav.profile',
};

const currentPageTitle = computed(() => {
    if (route().current('dashboard')) {
        return t(pageTitleMap.dashboard);
    }

    if (route().current('tickets.*')) {
        return t(pageTitleMap.tickets);
    }

    if (route().current('payments.*')) {
        return t(pageTitleMap.payments);
    }

    if (route().current('profile.*')) {
        return t(pageTitleMap.profile);
    }

    return t('app_name');
});

const roleLabel = computed(() => {
    const roles = page.props.auth?.roles ?? [];

    if (roles.includes('admin')) {
        return 'admin';
    }

    if (roles.includes('user')) {
        return 'user';
    }

    return '-';
});

const todayLabel = computed(() =>
    new Intl.DateTimeFormat(locale.value === 'fr' ? 'fr-FR' : 'en-US', {
        dateStyle: 'medium',
    }).format(new Date()),
);

const navItems = [
    {
        key: 'dashboard',
        routeName: 'dashboard',
        current: 'dashboard',
        icon: 'M3 12.5 12 4l9 8.5v7a1.5 1.5 0 0 1-1.5 1.5h-5.5V14h-4v7H4.5A1.5 1.5 0 0 1 3 19.5z',
    },
    {
        key: 'tickets',
        routeName: 'tickets.index',
        current: 'tickets.*',
        icon: 'M4 7.5h16M4 12h10M4 16.5h8M3.5 4h17A1.5 1.5 0 0 1 22 5.5v13a1.5 1.5 0 0 1-1.5 1.5h-17A1.5 1.5 0 0 1 2 18.5v-13A1.5 1.5 0 0 1 3.5 4z',
    },
    {
        key: 'payments',
        routeName: 'payments.index',
        current: 'payments.*',
        icon: 'M4 7h16M4 12h16M4 17h8M3.5 4h17A1.5 1.5 0 0 1 22 5.5v13a1.5 1.5 0 0 1-1.5 1.5h-17A1.5 1.5 0 0 1 2 18.5v-13A1.5 1.5 0 0 1 3.5 4z',
    },
];

const closeMobileSidebar = () => {
    mobileSidebarOpen.value = false;
};
</script>

<template>
    <div class="app-shell">
        <div class="min-h-screen md:grid md:grid-cols-[272px_minmax(0,1fr)]">
            <aside class="hidden border-r border-slate-200/80 bg-white/85 px-4 py-6 backdrop-blur md:flex md:flex-col">
                <Link
                    :href="route('dashboard')"
                    class="flex items-center gap-3 rounded-2xl px-2 py-2 transition hover:bg-slate-100/70"
                >
                    <ApplicationLogo class="h-9 w-9 fill-current text-slate-800" />
                    <div>
                        <p class="text-sm font-bold tracking-tight text-slate-900">{{ t('app_name') }}</p>
                        <p class="text-xs text-slate-500">{{ t('common.support_workspace') }}</p>
                    </div>
                </Link>

                <div class="mt-6 rounded-xl border border-slate-200/80 bg-slate-50/85 px-3 py-2 text-xs text-slate-600">
                    <span class="font-semibold uppercase tracking-wide text-slate-500">{{ t('common.role_label') }}</span>
                    <span class="mono ml-2 rounded bg-white px-2 py-0.5 text-[11px] font-semibold text-slate-700 ring-1 ring-slate-200">
                        {{ roleLabel }}
                    </span>
                </div>

                <nav class="mt-6 space-y-1.5">
                    <Link
                        v-for="item in navItems"
                        :key="item.key"
                        :href="route(item.routeName)"
                        :class="[
                            'flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition',
                            route().current(item.current)
                                ? 'bg-slate-900 text-white shadow-md shadow-slate-900/10'
                                : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900',
                        ]"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path :d="item.icon" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        {{ t(`nav.${item.key}`) }}
                    </Link>
                </nav>

                <div class="mt-auto rounded-2xl border border-slate-200 bg-white px-4 py-4 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ t('common.productivity') }}</p>
                    <p class="mt-2 text-sm font-medium text-slate-800">{{ t('common.manage_flow') }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ t('common.manage_flow_hint') }}</p>
                </div>
            </aside>

            <div class="relative flex min-h-screen flex-col">
                <header class="sticky top-0 z-30 border-b border-slate-200/80 bg-white/88 backdrop-blur">
                    <div class="mx-auto flex h-16 w-full max-w-[1480px] items-center justify-between px-4 sm:px-8">
                        <div class="flex items-center gap-3">
                            <button
                                type="button"
                                class="btn-ghost md:hidden"
                                @click="mobileSidebarOpen = true"
                                :aria-label="t('common.menu')"
                            >
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M4 7h16M4 12h16M4 17h16" stroke-linecap="round" />
                                </svg>
                            </button>

                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ t('common.workspace') }}</p>
                                <h1 class="text-base font-semibold text-slate-900">{{ currentPageTitle }}</h1>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 sm:gap-3">
                            <div class="metric-chip hidden lg:inline-flex">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500" />
                                {{ todayLabel }}
                            </div>

                            <div class="hidden items-center gap-1 rounded-xl border border-slate-200 bg-white p-1 sm:flex">
                                <Link
                                    v-for="localeCode in supportedLocales"
                                    :key="localeCode"
                                    :href="route('locale.switch', localeCode)"
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

                            <Dropdown align="right" width="48">
                                <template #trigger>
                                    <button
                                        type="button"
                                        class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 transition hover:border-slate-300 hover:text-slate-900"
                                    >
                                        <span class="flex h-7 w-7 items-center justify-center rounded-full bg-slate-900 text-xs font-semibold text-white">
                                            {{ $page.props.auth.user.name.slice(0, 1).toUpperCase() }}
                                        </span>
                                        <span class="hidden sm:inline">{{ $page.props.auth.user.name }}</span>
                                        <svg class="h-4 w-4 text-slate-500" viewBox="0 0 20 20" fill="currentColor">
                                            <path
                                                fill-rule="evenodd"
                                                d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.112l3.71-3.88a.75.75 0 0 1 1.08 1.04l-4.25 4.445a.75.75 0 0 1-1.08 0L5.21 8.273a.75.75 0 0 1 .02-1.06Z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                    </button>
                                </template>

                                <template #content>
                                    <DropdownLink :href="route('profile.edit')">{{ t('nav.profile') }}</DropdownLink>
                                    <DropdownLink :href="route('logout')" method="post" as="button">{{ t('nav.logout') }}</DropdownLink>
                                </template>
                            </Dropdown>
                        </div>
                    </div>
                </header>

                <div class="border-b border-slate-200/70 bg-white/55">
                    <div class="mx-auto flex w-full max-w-[1480px] flex-wrap items-center gap-2 px-4 py-2 sm:px-8">
                        <span class="tiny-muted">{{ t('common.quick_access') }}</span>
                        <Link :href="route('tickets.index', { status: 'open' })" class="metric-chip hover:bg-slate-50">
                            {{ t('status.open') }}
                        </Link>
                        <Link :href="route('tickets.index', { status: 'in_progress' })" class="metric-chip hover:bg-slate-50">
                            {{ t('status.in_progress') }}
                        </Link>
                        <Link :href="route('payments.index', { status: 'pending' })" class="metric-chip hover:bg-slate-50">
                            {{ t('status.pending') }}
                        </Link>
                        <Link :href="route('payments.index', { status: 'failed' })" class="metric-chip hover:bg-slate-50">
                            {{ t('status.failed') }}
                        </Link>
                    </div>
                </div>

                <div v-if="flashSuccess || flashError" class="mx-auto w-full max-w-[1480px] px-4 pt-4 sm:px-8">
                    <div class="relative">
                        <div
                            v-if="flashSuccess"
                            class="mb-3 flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
                        >
                            <svg class="mt-0.5 h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="m5 13 4 4L19 7" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <span>{{ flashSuccess }}</span>
                        </div>

                        <div
                            v-if="flashError"
                            class="mb-3 flex items-start gap-3 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800"
                        >
                            <svg class="mt-0.5 h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="9" />
                                <path d="M12 8v5M12 16h.01" stroke-linecap="round" />
                            </svg>
                            <span>{{ flashError }}</span>
                        </div>
                    </div>
                </div>

                <main class="flex-1">
                    <div class="mx-auto w-full max-w-[1480px] px-4 pb-8 pt-6 sm:px-8 sm:pt-8">
                        <div v-if="$slots.header" class="mb-6">
                            <slot name="header" />
                        </div>

                        <slot />
                    </div>
                </main>
            </div>
        </div>

        <Transition
            enter-active-class="duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="mobileSidebarOpen" class="fixed inset-0 z-40 bg-slate-900/45 md:hidden" @click="closeMobileSidebar" />
        </Transition>

        <Transition
            enter-active-class="duration-200 ease-out"
            enter-from-class="-translate-x-full"
            enter-to-class="translate-x-0"
            leave-active-class="duration-150 ease-in"
            leave-from-class="translate-x-0"
            leave-to-class="-translate-x-full"
        >
            <aside
                v-if="mobileSidebarOpen"
                class="fixed inset-y-0 left-0 z-50 w-72 border-r border-slate-200 bg-white p-4 shadow-2xl md:hidden"
            >
                <div class="mb-4 flex items-center justify-between">
                    <Link :href="route('dashboard')" class="flex items-center gap-2" @click="closeMobileSidebar">
                        <ApplicationLogo class="h-8 w-8 fill-current text-slate-800" />
                        <span class="text-sm font-bold text-slate-900">{{ t('app_name') }}</span>
                    </Link>
                    <button type="button" class="btn-ghost" @click="closeMobileSidebar">✕</button>
                </div>

                <nav class="space-y-1.5">
                    <Link
                        v-for="item in navItems"
                        :key="item.key"
                        :href="route(item.routeName)"
                        @click="closeMobileSidebar"
                        :class="[
                            'flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition',
                            route().current(item.current)
                                ? 'bg-slate-900 text-white'
                                : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900',
                        ]"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path :d="item.icon" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        {{ t(`nav.${item.key}`) }}
                    </Link>
                </nav>

                <div class="mt-4 flex gap-2">
                    <Link
                        v-for="localeCode in supportedLocales"
                        :key="localeCode"
                        :href="route('locale.switch', localeCode)"
                        @click="closeMobileSidebar"
                        :class="[
                            'rounded-lg px-3 py-1.5 text-xs font-semibold uppercase',
                            locale === localeCode ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700',
                        ]"
                    >
                        {{ localeCode }}
                    </Link>
                </div>
            </aside>
        </Transition>
    </div>
</template>
