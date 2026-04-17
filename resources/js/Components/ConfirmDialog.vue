<script setup>
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        required: true,
    },
    message: {
        type: String,
        default: '',
    },
    confirmLabel: {
        type: String,
        default: 'Confirm',
    },
    cancelLabel: {
        type: String,
        default: 'Cancel',
    },
    danger: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['close', 'confirm']);
</script>

<template>
    <Modal :show="show" max-width="md" @close="emit('close')">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-slate-900">{{ title }}</h3>
            <p v-if="message" class="mt-2 text-sm leading-relaxed text-slate-500">{{ message }}</p>

            <div class="mt-6 flex justify-end gap-2">
                <button type="button" class="btn-secondary" @click="emit('close')">{{ cancelLabel }}</button>
                <button type="button" :class="danger ? 'btn-danger' : 'btn-primary'" @click="emit('confirm')">
                    {{ confirmLabel }}
                </button>
            </div>
        </div>
    </Modal>
</template>
