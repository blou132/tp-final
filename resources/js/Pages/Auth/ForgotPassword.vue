<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { useI18n } from '@/composables/useI18n';

defineProps({
    status: {
        type: String,
    },
});

const { t } = useI18n();

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout>
        <Head :title="t('auth.forgot_page_title')" />

        <header class="mb-6">
            <h1 class="text-xl font-bold text-slate-900">{{ t('auth.forgot_title') }}</h1>
            <p class="mt-1 text-sm text-slate-500">{{ t('auth.forgot_subtitle') }}</p>
        </header>

        <div v-if="status" class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <InputLabel for="email" :value="t('common.email')" />
                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="pt-2">
                <PrimaryButton type="submit" class="w-full justify-center" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    {{ t('auth.forgot_button') }}
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
