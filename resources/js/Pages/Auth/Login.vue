<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { useI18n } from '@/composables/useI18n';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const { t } = useI18n();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head :title="t('auth.login_page_title')" />

        <header class="mb-6">
            <h1 class="text-xl font-bold text-slate-900">{{ t('auth.login_title') }}</h1>
            <p class="mt-1 text-sm text-slate-500">{{ t('auth.login_subtitle') }}</p>
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

            <div>
                <InputLabel for="password" :value="t('common.password')" />
                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <label class="flex flex-col items-start gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-600 sm:flex-row sm:items-center sm:justify-between">
                <span class="flex items-center gap-2">
                    <Checkbox name="remember" v-model:checked="form.remember" />
                    {{ t('auth.remember_me') }}
                </span>

                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="text-xs font-semibold text-slate-500 underline-offset-4 hover:text-slate-900 hover:underline"
                >
                    {{ t('auth.forgot_password') }}
                </Link>
            </label>

            <div class="pt-2">
                <PrimaryButton type="submit" class="w-full justify-center" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    {{ t('auth.login_button') }}
                </PrimaryButton>
            </div>

            <p class="text-center text-sm text-slate-500">
                {{ t('auth.no_account') }}
                <Link :href="route('register')" class="font-semibold text-slate-800 underline-offset-4 hover:underline">
                    {{ t('auth.register_link') }}
                </Link>
            </p>
        </form>
    </GuestLayout>
</template>
