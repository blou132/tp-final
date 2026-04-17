<script setup>
import { computed, ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { useI18n } from '@/composables/useI18n';

const page = usePage();
const showingNavigationDropdown = ref(false);
const { t, locale } = useI18n();

const flashSuccess = computed(() => page.props.flash?.success ?? null);
</script>

<template>
    <div>
        <div class="min-h-screen bg-slate-100">
            <nav class="border-b border-slate-200 bg-white">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 justify-between">
                        <div class="flex">
                            <div class="flex shrink-0 items-center">
                                <Link :href="route('dashboard')">
                                    <ApplicationLogo class="block h-9 w-auto fill-current text-slate-800" />
                                </Link>
                            </div>

                            <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                                <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
                                    {{ t('nav.dashboard') }}
                                </NavLink>
                                <NavLink :href="route('tickets.index')" :active="route().current('tickets.*')">
                                    {{ t('nav.tickets') }}
                                </NavLink>
                                <NavLink :href="route('payments.index')" :active="route().current('payments.*')">
                                    {{ t('nav.payments') }}
                                </NavLink>
                            </div>
                        </div>

                        <div class="hidden sm:flex sm:items-center sm:gap-3">
                            <div class="flex items-center gap-2 rounded-md border border-slate-200 px-2 py-1 text-xs text-slate-600">
                                <Link
                                    :href="route('locale.switch', 'fr')"
                                    :class="[
                                        'rounded px-2 py-1',
                                        locale === 'fr' ? 'bg-slate-900 text-white' : 'hover:bg-slate-100',
                                    ]"
                                >
                                    FR
                                </Link>
                                <Link
                                    :href="route('locale.switch', 'en')"
                                    :class="[
                                        'rounded px-2 py-1',
                                        locale === 'en' ? 'bg-slate-900 text-white' : 'hover:bg-slate-100',
                                    ]"
                                >
                                    EN
                                </Link>
                            </div>

                            <div class="relative ms-3">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                class="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-slate-600 transition duration-150 ease-in-out hover:text-slate-800 focus:outline-none"
                                            >
                                                {{ $page.props.auth.user.name }}

                                                <svg
                                                    class="-me-0.5 ms-2 h-4 w-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <DropdownLink :href="route('profile.edit')">
                                            {{ t('nav.profile') }}
                                        </DropdownLink>
                                        <DropdownLink :href="route('logout')" method="post" as="button">
                                            {{ t('nav.logout') }}
                                        </DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <div class="-me-2 flex items-center sm:hidden">
                            <button
                                @click="showingNavigationDropdown = !showingNavigationDropdown"
                                class="inline-flex items-center justify-center rounded-md p-2 text-slate-400 transition duration-150 ease-in-out hover:bg-slate-100 hover:text-slate-500 focus:bg-slate-100 focus:text-slate-500 focus:outline-none"
                            >
                                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path
                                        :class="{
                                            hidden: showingNavigationDropdown,
                                            'inline-flex': !showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{
                                            hidden: !showingNavigationDropdown,
                                            'inline-flex': showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div :class="{ block: showingNavigationDropdown, hidden: !showingNavigationDropdown }" class="sm:hidden">
                    <div class="space-y-1 pb-3 pt-2">
                        <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')">
                            {{ t('nav.dashboard') }}
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('tickets.index')" :active="route().current('tickets.*')">
                            {{ t('nav.tickets') }}
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('payments.index')" :active="route().current('payments.*')">
                            {{ t('nav.payments') }}
                        </ResponsiveNavLink>
                    </div>

                    <div class="border-t border-slate-200 pb-1 pt-4">
                        <div class="px-4">
                            <div class="text-base font-medium text-slate-800">{{ $page.props.auth.user.name }}</div>
                            <div class="text-sm font-medium text-slate-500">{{ $page.props.auth.user.email }}</div>
                        </div>

                        <div class="mt-3 flex gap-2 px-4">
                            <Link
                                :href="route('locale.switch', 'fr')"
                                class="rounded border px-2 py-1 text-xs"
                                :class="locale === 'fr' ? 'bg-slate-900 text-white' : 'bg-white text-slate-700'"
                            >
                                FR
                            </Link>
                            <Link
                                :href="route('locale.switch', 'en')"
                                class="rounded border px-2 py-1 text-xs"
                                :class="locale === 'en' ? 'bg-slate-900 text-white' : 'bg-white text-slate-700'"
                            >
                                EN
                            </Link>
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.edit')">
                                {{ t('nav.profile') }}
                            </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('logout')" method="post" as="button">
                                {{ t('nav.logout') }}
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            <header class="bg-white shadow" v-if="$slots.header">
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                <div
                    v-if="flashSuccess"
                    class="mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"
                >
                    {{ flashSuccess }}
                </div>

                <slot />
            </main>
        </div>
    </div>
</template>
