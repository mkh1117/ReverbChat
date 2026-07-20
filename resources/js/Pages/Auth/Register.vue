<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    username: '',
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
        <Head title="ایجاد حساب کاربری" />

        <div class="w-full max-w-md mx-auto" dir="rtl">
            <!-- هدر فرم ثبت نام -->
            <div class="text-center mb-8">
                <h2 class="text-2xl font-black text-gray-800 dark:text-white tracking-tight">ساخت حساب کاربری</h2>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-2">جهت ثبت‌نام، مشخصات خود را وارد کنید</p>
            </div>

            <form @submit.prevent="submit" class="space-y-5">
                <!-- فیلد نام -->
                <div class="space-y-1.5">
                    <InputLabel for="name" value="نام و نام خانوادگی" class="text-xs font-semibold text-gray-600 dark:text-gray-400" />

                    <div class="relative flex items-center">
                        <span class="absolute right-4 text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </span>
                        <TextInput
                            id="name"
                            type="text"
                            class="w-full bg-gray-50 dark:bg-gray-900/50 text-sm border-gray-200/80 dark:border-gray-800 rounded-2xl pr-11 pl-4 py-3 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-right placeholder:text-gray-400"
                            v-model="form.name"
                            required
                            autofocus
                            placeholder="نام خود را وارد کنید"
                            autocomplete="name"
                        />
                    </div>
                    <InputError class="mt-1 text-xs text-right" :message="form.errors.name" />
                </div>

                <!-- فیلد نام کاربری -->
                <div class="space-y-1.5">
                    <InputLabel for="username" value="نام کاربری" class="text-xs font-semibold text-gray-600 dark:text-gray-400" />

                    <div class="relative flex items-center">
                        <span class="absolute right-4 text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                            </svg>
                        </span>
                        <TextInput
                            id="username"
                            type="text"
                            class="w-full bg-gray-50 dark:bg-gray-900/50 text-sm border-gray-200/80 dark:border-gray-800 rounded-2xl pr-11 pl-4 py-3 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-right placeholder:text-gray-400"
                            v-model="form.username"
                            required
                            placeholder="username"
                            autocomplete="username"
                        />
                    </div>
                    <InputError class="mt-1 text-xs text-right" :message="form.errors.username" />
                </div>

                <!-- فیلد رمز عبور -->
                <div class="space-y-1.5">
                    <InputLabel for="password" value="رمز عبور" class="text-xs font-semibold text-gray-600 dark:text-gray-400" />

                    <div class="relative flex items-center">
                        <span class="absolute right-4 text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </span>
                        <TextInput
                            id="password"
                            type="password"
                            class="w-full bg-gray-50 dark:bg-gray-900/50 text-sm border-gray-200/80 dark:border-gray-800 rounded-2xl pr-11 pl-4 py-3 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-right placeholder:text-gray-400"
                            v-model="form.password"
                            required
                            placeholder="••••••••"
                            autocomplete="new-password"
                        />
                    </div>
                    <InputError class="mt-1 text-xs text-right" :message="form.errors.password" />
                </div>

                <!-- فیلد تکرار رمز عبور -->
                <div class="space-y-1.5">
                    <InputLabel for="password_confirmation" value="تکرار رمز عبور" class="text-xs font-semibold text-gray-600 dark:text-gray-400" />

                    <div class="relative flex items-center">
                        <span class="absolute right-4 text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </span>
                        <TextInput
                            id="password_confirmation"
                            type="password"
                            class="w-full bg-gray-50 dark:bg-gray-900/50 text-sm border-gray-200/80 dark:border-gray-800 rounded-2xl pr-11 pl-4 py-3 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-right placeholder:text-gray-400"
                            v-model="form.password_confirmation"
                            required
                            placeholder="••••••••"
                            autocomplete="new-password"
                        />
                    </div>
                    <InputError class="mt-1 text-xs text-right" :message="form.errors.password_confirmation" />
                </div>

                <!-- دکمه ثبت نام اصلی -->
                <div class="pt-2">
                    <PrimaryButton
                        class="w-full bg-blue-600 hover:bg-blue-700 active:scale-[0.98] text-white font-bold py-3.5 rounded-2xl flex items-center justify-center gap-2 transition-all shadow-lg shadow-blue-500/10 border-none"
                        :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                        :disabled="form.processing"
                    >
                        <span>ایجاد حساب</span>
                        <svg v-if="!form.processing" class="w-4 h-4 transform rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14" />
                        </svg>
                    </PrimaryButton>
                </div>
            </form>

            <!-- فوتر فرم برای هدایت به لاگین -->
            <div class="mt-8 text-center border-t border-gray-100 dark:border-gray-800/60 pt-4">
                <span class="text-xs text-gray-400">قبلاً ثبت نام کرده‌اید؟</span>
                <Link
                    :href="route('login')"
                    class="text-xs font-bold text-blue-600 hover:text-blue-700 mr-1.5 hover:underline transition-all"
                >
                    وارد شوید
                </Link>
            </div>
        </div>
    </GuestLayout>
</template>
