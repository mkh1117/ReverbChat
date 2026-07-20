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
        <Head title="ورود به سیستم" />

        <div class="w-full max-w-md mx-auto" dir="rtl">
            <!-- هدر فرم لاگین -->
            <div class="text-center mb-8">
                <h2 class="text-2xl font-black text-gray-800 dark:text-white tracking-tight">خوش آمدید</h2>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-2">جهت دسترسی به حساب خود اطلاعات زیر را وارد کنید</p>
            </div>

            <div v-if="status" class="mb-4 p-3 bg-green-50 dark:bg-green-900/20 text-sm font-medium text-green-600 rounded-xl text-right">
                {{ status }}
            </div>

            <form @submit.prevent="submit" class="space-y-5">
                <!-- فیلد نام کاربری -->
                <div class="space-y-1.5">
                    <InputLabel for="username" value="نام کاربری" class="text-xs font-semibold text-gray-600 dark:text-gray-400" />

                    <div class="relative flex items-center">
                        <span class="absolute right-4 text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </span>
                        <TextInput
                            id="username"
                            type="text"
                            class="w-full bg-gray-50 dark:bg-gray-900/50 text-sm border-gray-200/80 dark:border-gray-800 rounded-2xl pr-11 pl-4 py-3 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-right placeholder:text-gray-400"
                            v-model="form.username"
                            required
                            autofocus
                            placeholder="نام کاربری خود را وارد کنید"
                            autocomplete="username"
                        />
                    </div>
                    <InputError class="mt-1 text-xs text-right" :message="form.errors.username" />
                </div>

                <!-- فیلد رمز عبور -->
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <InputLabel for="password" value="رمز عبور" class="text-xs font-semibold text-gray-600 dark:text-gray-400" />

                        <Link
                            v-if="canResetPassword"
                            :href="route('password.request')"
                            class="text-xs text-blue-600 hover:text-blue-700 hover:underline transition-all"
                        >
                            فراموشی رمز عبور؟
                        </Link>
                    </div>

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
                            autocomplete="current-password"
                        />
                    </div>
                    <InputError class="mt-1 text-xs text-right" :message="form.errors.password" />
                </div>

                <!-- مرا به خاطر بسپار -->
                <div class="pt-1 flex items-center justify-start">
                    <label class="flex items-center gap-2 cursor-pointer select-none group">
                        <Checkbox name="remember" v-model:checked="form.remember" class="rounded-md border-gray-300 text-blue-600 focus:ring-blue-500/30" />
                        <span class="text-xs text-gray-500 dark:text-gray-400 group-hover:text-gray-700 transition-all">مرا به خاطر بسپار</span>
                    </label>
                </div>

                <!-- دکمه ورود اصلی -->
                <div class="pt-2">
                    <PrimaryButton
                        class="w-full bg-blue-600 hover:bg-blue-700 active:scale-[0.98] text-white font-bold py-3.5 rounded-2xl flex items-center justify-center gap-2 transition-all shadow-lg shadow-blue-500/10 border-none"
                        :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                        :disabled="form.processing"
                    >
                        <span>ورود به حساب</span>
                        <svg v-if="!form.processing" class="w-4 h-4 transform rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14" />
                        </svg>
                    </PrimaryButton>
                </div>
            </form>

            <!-- فوتر فرم برای ثبت نام -->
            <div class="mt-8 text-center border-t border-gray-100 dark:border-gray-800/60 pt-4">
                <span class="text-xs text-gray-400">حساب کاربری ندارید؟</span>
                <Link
                    :href="route('register')"
                    class="text-xs font-bold text-blue-600 hover:text-blue-700 mr-1.5 hover:underline transition-all"
                >
                    ثبت نام کنید
                </Link>
            </div>
        </div>
    </GuestLayout>
</template>
