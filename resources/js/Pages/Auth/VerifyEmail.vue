<script setup>
import { computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { useI18n } from '@/composables/useI18n';

const props = defineProps({
    status: {
        type: String,
    },
});

const { t } = useI18n();

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(() => props.status === 'verification-link-sent');
</script>

<template>
    <GuestLayout>
        <Head :title="t('auth.verify_page_title')" />

        <header class="mb-6">
            <h1 class="text-xl font-bold text-slate-900">{{ t('auth.verify_title') }}</h1>
            <p class="mt-1 text-sm text-slate-500">{{ t('auth.verify_subtitle') }}</p>
        </header>

        <div v-if="verificationLinkSent" class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700">
            {{ t('auth.verify_sent') }}
        </div>

        <form @submit.prevent="submit" class="space-y-3">
            <PrimaryButton class="w-full justify-center" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                {{ t('auth.verify_resend') }}
            </PrimaryButton>

            <Link
                :href="route('logout')"
                method="post"
                as="button"
                class="btn-ghost w-full justify-center"
            >
                {{ t('auth.verify_logout') }}
            </Link>
        </form>
    </GuestLayout>
</template>
