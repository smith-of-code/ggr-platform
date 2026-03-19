<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const confirmingUserDeletion = ref(false);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
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
    <section class="space-y-6">
        <header>
            <h2 class="text-lg font-medium text-gray-900">
                Delete Account
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                Once your account is deleted, all of its resources and data will
                be permanently deleted. Before deleting your account, please
                download any data or information that you wish to retain.
            </p>
        </header>

        <RButton variant="danger" @click="confirmUserDeletion">Delete Account</RButton>

        <RModal
            v-model="confirmingUserDeletion"
            title="Are you sure you want to delete your account?"
            subtitle="Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account."
            size="md"
        >
            <RInput
                v-model="form.password"
                type="password"
                label="Password"
                placeholder="Password"
                :error="form.errors.password"
                id="password"
                @keyup.enter="deleteUser"
            />

            <template #footer>
                <RButton variant="secondary" @click="closeModal">Cancel</RButton>
                <RButton
                    variant="danger"
                    :loading="form.processing"
                    :disabled="form.processing"
                    @click="deleteUser"
                >
                    Delete Account
                </RButton>
            </template>
        </RModal>
    </section>
</template>
