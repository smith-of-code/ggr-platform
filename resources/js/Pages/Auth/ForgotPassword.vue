<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout>
        <Head title="Forgot Password" />

        <div class="mb-4 text-sm text-gray-600">
            Forgot your password? No problem. Just let us know your email
            address and we will email you a password reset link that will allow
            you to choose a new one.
        </div>

        <div
            v-if="status"
            class="mb-4 text-sm font-medium text-green-600"
        >
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <RInput v-model="form.email" type="email" label="Email" :error="form.errors.email" required id="email" />

            <div class="mt-4 flex items-center justify-end">
                <RButton
                    variant="primary"
                    :loading="form.processing"
                    :disabled="form.processing"
                >
                    Email Password Reset Link
                </RButton>
            </div>
        </form>
    </GuestLayout>
</template>
