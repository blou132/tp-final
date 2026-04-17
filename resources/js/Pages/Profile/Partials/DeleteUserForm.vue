<script setup>
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';
import { useI18n } from '@/composables/useI18n';

const { t } = useI18n();

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;
    nextTick(() => passwordInput.value.focus());
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;
    form.clearErrors();
    form.reset();
};
</script>

<template>
    <section class="space-y-4">
        <header>
            <h2 class="text-lg font-semibold text-slate-900">{{ t('profile.delete_title') }}</h2>
            <p class="mt-1 text-sm text-slate-500">
                {{ t('profile.delete_subtitle') }}
            </p>
        </header>

        <DangerButton @click="confirmUserDeletion">{{ t('profile.delete_button') }}</DangerButton>

        <Modal :show="confirmingUserDeletion" @close="closeModal">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-slate-900">{{ t('profile.delete_confirm_title') }}</h3>
                <p class="mt-2 text-sm text-slate-500">
                    {{ t('profile.delete_confirm_text') }}
                </p>

                <div class="mt-6">
                    <InputLabel for="password" :value="t('common.password')" class="sr-only" />
                    <TextInput
                        id="password"
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        class="mt-1 block w-full"
                        :placeholder="t('profile.delete_confirm_placeholder')"
                        @keyup.enter="deleteUser"
                    />
                    <InputError :message="form.errors.password" class="mt-2" />
                </div>

                <div class="mt-6 flex justify-end gap-2">
                    <SecondaryButton @click="closeModal">{{ t('common.cancel') }}</SecondaryButton>
                    <DangerButton :disabled="form.processing" @click="deleteUser">{{ t('profile.delete_button') }}</DangerButton>
                </div>
            </div>
        </Modal>
    </section>
</template>
