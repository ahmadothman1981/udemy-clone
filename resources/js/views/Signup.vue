<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div>
                <div class="flex justify-center mb-2">
                    <router-link to="/">
                        <img :src="'/images/logo.png'" alt="UdemyClone" class="h-12 w-auto">
                    </router-link>
                </div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    Sign up and start learning
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    Or
                    <router-link to="/login" class="font-medium text-purple-600 hover:text-purple-500">
                        login to your account
                    </router-link>
                </p>
            </div>
            <form class="mt-8 space-y-6" @submit.prevent="handleSignup">
                <div class="rounded-md shadow-sm -space-y-px">
                     <div>
                        <label for="name" class="sr-only">Full Name</label>
                        <input id="name" name="name" type="text" required v-model="form.name" class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-purple-500 focus:border-purple-500 focus:z-10 sm:text-sm" placeholder="Full Name">
                    </div>
                    <div>
                        <label for="email-address" class="sr-only">Email address</label>
                        <input id="email-address" name="email" type="email" autocomplete="email" required v-model="form.email" class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-purple-500 focus:border-purple-500 focus:z-10 sm:text-sm" placeholder="Email address">
                    </div>
                    <div>
                        <label for="password" class="sr-only">Password</label>
                        <input id="password" name="password" type="password" autocomplete="new-password" required v-model="form.password" class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-purple-500 focus:border-purple-500 focus:z-10 sm:text-sm" placeholder="Password">
                    </div>
                     <div>
                        <label for="password_confirmation" class="sr-only">Confirm Password</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required v-model="form.password_confirmation" class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-purple-500 focus:border-purple-500 focus:z-10 sm:text-sm" placeholder="Confirm Password">
                    </div>
                </div>

                <div v-if="error" class="text-red-500 text-sm text-center">
                    {{ error }}
                </div>

                <div>
                    <button type="submit" :disabled="loading" class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                        <span v-if="loading">Signing up...</span>
                        <span v-else>Sign Up</span>
                    </button>
                </div>
            </form>

            <div class="mt-6">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-white text-gray-500">
                            Or sign up with
                        </span>
                    </div>
                </div>
                <div class="mt-6 grid grid-cols-3 gap-3">
                    <div>
                        <a href="/api/auth/google/redirect" class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                            Google
                        </a>
                    </div>
                    <div>
                        <a href="/api/auth/facebook/redirect" class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                            Facebook
                        </a>
                    </div>
                    <div>
                         <a href="/api/auth/github/redirect" class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                            GitHub
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const router = useRouter();
const authStore = useAuthStore();

const form = ref({
    name: '',
    email: '',
    password: '',
    password_confirmation: ''
});

const error = ref('');
const loading = ref(false);

// Determine redirect path based on user role
const getRedirectPath = (user) => {
    if (!user) return '/dashboard';
    const role = user.role?.toLowerCase();
    if (role === 'instructor') return '/instructor';
    if (role === 'admin') return '/admin';
    return '/dashboard'; // Default for students (new registrations)
};

const handleSignup = async () => {
    loading.value = true;
    error.value = '';
    
    try {
        await authStore.register(form.value);
        const redirectPath = getRedirectPath(authStore.user);
        router.push(redirectPath);
    } catch (e) {
        if (e.response && e.response.status === 422) {
             const errors = e.response.data.errors;
             error.value = Object.values(errors).flat().join(', ');
        } else {
             error.value = 'Registration failed. Please try again.';
        }
    } finally {
        loading.value = false;
    }
};
</script>
