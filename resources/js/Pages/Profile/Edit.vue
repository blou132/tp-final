<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});
</script>

<template>
    <Head title="Profile" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="page-title">Profile</h2>
                <p class="page-subtitle">Manage your account settings and security.</p>
            </div>
        </template>

        <div class="space-y-4">
            <div class="surface-card p-5 sm:p-6">
                <UpdateProfileInformationForm :must-verify-email="mustVerifyEmail" :status="status" class="max-w-2xl" />
            </div>

            <div class="surface-card p-5 sm:p-6">
                <UpdatePasswordForm class="max-w-2xl" />
            </div>

            <div class="surface-card p-5 sm:p-6">
                <DeleteUserForm class="max-w-2xl" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
