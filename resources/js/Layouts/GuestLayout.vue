<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
import { useI18n } from '@/composables/useI18n';

const { t } = useI18n();
</script>

<template>
    <div class="app-shell relative flex min-h-screen items-center justify-center px-4 py-10 sm:px-6">
        <div class="absolute inset-0 pointer-events-none">
            <div class="absolute left-[-4rem] top-[-4rem] h-44 w-44 rounded-full bg-blue-300/30 blur-3xl" />
            <div class="absolute bottom-[-5rem] right-[-4rem] h-56 w-56 rounded-full bg-emerald-300/25 blur-3xl" />
        </div>

        <div class="relative w-full max-w-5xl">
            <div class="grid items-stretch gap-5 lg:grid-cols-[minmax(0,0.95fr)_minmax(0,1.05fr)]">
                <aside class="surface-card reveal hidden p-7 lg:block">
                    <p class="text-xs font-semibold uppercase tracking-wide text-blue-700">{{ t('app_name') }}</p>
                    <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-900">{{ t('auth.pitch_title') }}</h2>
                    <p class="mt-3 text-sm leading-relaxed text-slate-600">{{ t('auth.pitch_text') }}</p>

                    <ul class="mt-6 space-y-3 text-sm text-slate-700">
                        <li class="surface-card-soft flex items-start gap-3 p-3">
                            <span class="mt-1 h-2 w-2 rounded-full bg-sky-500" />
                            {{ t('auth.pitch_item_1') }}
                        </li>
                        <li class="surface-card-soft flex items-start gap-3 p-3">
                            <span class="mt-1 h-2 w-2 rounded-full bg-emerald-500" />
                            {{ t('auth.pitch_item_2') }}
                        </li>
                        <li class="surface-card-soft flex items-start gap-3 p-3">
                            <span class="mt-1 h-2 w-2 rounded-full bg-amber-500" />
                            {{ t('auth.pitch_item_3') }}
                        </li>
                    </ul>

                    <div class="mt-6 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-500">
                        {{ t('auth.security_label') }}
                    </div>
                </aside>

                <div class="w-full max-w-md justify-self-center lg:max-w-none">
                    <div class="mb-5 flex justify-center lg:justify-start">
                        <Link href="/" class="inline-flex items-center gap-2 rounded-2xl px-3 py-2 text-slate-600 transition hover:bg-white/70 hover:text-slate-900">
                            <ApplicationLogo class="h-10 w-10 fill-current text-slate-700" />
                            <span class="text-sm font-semibold tracking-wide">{{ t('common.support_suite') }}</span>
                        </Link>
                    </div>

                    <div class="surface-card reveal reveal-delay-1 p-6 sm:p-8">
                        <slot />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
