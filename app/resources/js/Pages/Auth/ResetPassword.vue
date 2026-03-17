<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    email: {
        type: String,
        required: true,
    },
    token: {
        type: String,
        required: true,
    },
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Reset Password" />

        <form @submit.prevent="submit" class="space-y-4">
            <RInput v-model="form.email" type="email" label="Email" :error="form.errors.email" required id="email" />

            <RInput v-model="form.password" type="password" label="Password" :error="form.errors.password" required id="password" />

            <RInput v-model="form.password_confirmation" type="password" label="Confirm Password" :error="form.errors.password_confirmation" required id="password_confirmation" />

            <div class="flex items-center justify-end">
                <RButton
                    variant="primary"
                    :loading="form.processing"
                    :disabled="form.processing"
                >
                    Reset Password
                </RButton>
            </div>
        </form>
    </GuestLayout>
</template>
