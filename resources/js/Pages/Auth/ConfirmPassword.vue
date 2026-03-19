<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    password: '',
});

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Confirm Password" />

        <div class="mb-4 text-sm text-gray-600">
            This is a secure area of the application. Please confirm your
            password before continuing.
        </div>

        <form @submit.prevent="submit">
            <RInput v-model="form.password" type="password" label="Password" :error="form.errors.password" required id="password" />

            <div class="mt-4 flex justify-end">
                <RButton
                    variant="primary"
                    :loading="form.processing"
                    :disabled="form.processing"
                >
                    Confirm
                </RButton>
            </div>
        </form>
    </GuestLayout>
</template>
