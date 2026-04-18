<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { useI18n } from '@/composables/useI18n';

const { t } = useI18n();

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head :title="t('auth.register_page_title')" />

        <header class="mb-6">
            <h1 class="text-xl font-bold text-slate-900">{{ t('auth.register_title') }}</h1>
            <p class="mt-1 text-sm text-slate-500">{{ t('auth.register_subtitle') }}</p>
        </header>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <InputLabel for="name" :value="t('common.name')" />
                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="email" :value="t('common.email')" />
                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
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
                    autocomplete="new-password"
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div>
                <InputLabel for="password_confirmation" :value="t('common.password_confirmation')" />
                <TextInput
                    id="password_confirmation"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                />
                <InputError class="mt-2" :message="form.errors.password_confirmation" />
            </div>

            <div class="pt-2">
                <PrimaryButton type="submit" class="w-full justify-center" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    {{ t('auth.register_button') }}
                </PrimaryButton>
            </div>

            <p class="text-center text-sm text-slate-500">
                {{ t('auth.already_registered') }}
                <Link :href="route('login')" class="font-semibold text-slate-800 underline-offset-4 hover:underline">
                    {{ t('auth.login_link') }}
                </Link>
            </p>
        </form>
    </GuestLayout>
</template>
