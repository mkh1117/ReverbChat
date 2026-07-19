<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    username: '',
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
        <Head title="ورود به حساب کاربری" />

        <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit" dir="rtl">
            <!-- نام کاربری -->
            <div>
                <InputLabel for="username" value="نام کاربری" />

                <TextInput
                    id="username"
                    type="text"
                    class="mt-1 block w-full text-right"
                    v-model="form.username"
                    required
                    autofocus
                    autocomplete="username"
                />

                <InputError class="mt-2 text-right" :message="form.errors.username" />
            </div>

            <!-- رمز عبور -->
            <div class="mt-4">
                <InputLabel for="password" value="رمز عبور" />

                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full text-right"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                />

                <InputError class="mt-2 text-right" :message="form.errors.password" />
            </div>

            <!-- مرا به خاطر بسپار -->
            <div class="mt-4 block text-right">
                <label class="flex items-center justify-start gap-2 cursor-pointer select-none">
                    <Checkbox name="remember" v-model:checked="form.remember" />
                    <span class="text-sm text-gray-600 dark:text-gray-400">مرا به خاطر بسپار</span>
                </label>
            </div>

            <!-- بخش عملیات و دکمه‌ها -->
            <div class="mt-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-gray-400 dark:hover:text-gray-100"
                    >
                        رمز عبور خود را فراموش کرده‌اید؟
                    </Link>
                </div>

                <div class="flex items-center justify-end gap-3">
                    <!-- لینک ثبت نام با ظاهر هماهنگ -->
                    <Link
                        :href="route('register')"
                        class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none dark:text-gray-400 dark:hover:text-gray-100"
                    >
                        ثبت نام
                    </Link>

                    <PrimaryButton
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                    >
                        ورود
                    </PrimaryButton>
                </div>
            </div>
        </form>
    </GuestLayout>
</template>
