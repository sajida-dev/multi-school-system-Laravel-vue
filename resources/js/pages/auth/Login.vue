<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { Eye, EyeOff, ArrowRight } from 'lucide-vue-next';
import { ref } from 'vue';

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

const form = useForm({
    login: '',
    password: '',
    remember: false,
});

const showPassword = ref(false);

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>

    <Head title="Log in" />

    <div class="min-h-screen bg-purple-800 flex items-center justify-center py-3">
        <div
            class="w-full max-w-6xl bg-white rounded-3xl shadow-2xl flex flex-col md:flex-row overflow-hidden m-2 md:m-0">
            <!-- Left Panel -->
            <div class="w-full md:w-1/2 bg-purple-900 flex flex-col justify-between p-6 md:p-8 relative">
                <div class="flex items-center mb-4 md:mb-8">
                    <span class="text-white text-xl md:text-2xl font-bold tracking-wide">Multi School System</span>
                </div>
                <div class="flex-1 flex items-center justify-center">
                    <img src="/login.png" alt="School Logo" class="w-full h-full max-w-md object-contain">
                </div>
                <div
                    class="flex flex-col md:flex-row justify-between text-xs text-purple-200 mt-4 md:mt-6 space-y-2 md:space-y-0">
                    <span>Multi School System — Education Management</span>
                    <span>©2024 All Rights Reserved</span>
                </div>
            </div>

            <!-- Right Panel -->
            <div class="w-full md:w-1/2 flex flex-col justify-center items-center p-6 md:p-12">
                <div class="w-full max-w-xs sm:max-w-md">
                    <h2 class="text-2xl md:text-3xl font-bold text-gray-800 mb-2 text-center md:text-left">Welcome Back
                    </h2>
                    <p class="text-gray-500 mb-4 md:mb-6 text-center md:text-left">Please enter your email and password
                    </p>

                    <div v-if="status" class="mb-4 text-center text-sm font-medium text-green-600">
                        {{ status }}
                    </div>

                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <label class="block text-gray-700 mb-1" for="login">Email</label>
                            <input id="login" name="login" v-model="form.login" type="email"
                                placeholder="name@company.com"
                                class="w-full px-3 py-2 md:px-4 md:py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-400 @error('login') border-red-300 text-red-900 placeholder-red-300 @enderror"
                                required autofocus>
                            <InputError :message="form.errors.login" />
                        </div>

                        <div>
                            <label class="block text-gray-700 mb-1" for="password">Password</label>
                            <div class="relative">
                                <input id="password" name="password" v-model="form.password"
                                    :type="showPassword ? 'text' : 'password'" placeholder="Password"
                                    class="w-full px-3 py-2 md:px-4 md:py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-400"
                                    required>
                                <button type="button" @click="showPassword = !showPassword"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                    <Eye v-if="!showPassword" class="w-5 h-5" />
                                    <EyeOff v-else class="w-5 h-5" />
                                </button>
                            </div>
                            <InputError :message="form.errors.password" />
                        </div>

                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <input type="checkbox" name="remember" v-model="form.remember" id="remember"
                                    class="mr-2 rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                                <label for="remember" class="text-xs text-gray-600">Remember me</label>
                            </div>
                            <Link v-if="canResetPassword" :href="route('password.request')"
                                class="text-xs text-purple-600 hover:underline">
                            Forgot Password
                            </Link>
                        </div>

                        <button type="submit" :disabled="form.processing"
                            class="w-full bg-purple-700 hover:bg-purple-800 text-white font-semibold py-2 rounded-lg transition-colors flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span v-if="form.processing"
                                class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></span>
                            <span v-else>login...</span>
                            <ArrowRight v-if="!form.processing" class="w-4 h-4" />
                        </button>

                        <div class="text-xs text-gray-500 mt-3 text-center">
                            By login, you agree to our <a href="#" class="text-purple-600 hover:underline">Terms &
                                Conditions</a>
                        </div>
                    </form>

                    <div class="mt-4 md:mt-6 text-center text-sm text-gray-600">
                        Don't have an account yet?
                        <Link :href="route('register')" class="text-purple-700 font-semibold hover:underline">Create
                        Account</Link>
                    </div>

                    <div class="flex justify-between text-xs text-gray-400 mt-8">
                        <span>Privacy</span>
                        <span>Terms of Service.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
