<template>
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
  <div class="max-w-md w-full space-y-8">
    <div>
        <div class="flex justify-center mb-2">
            <router-link to="/">
                <img :src="'/images/logo.png'" alt="UdemyClone" class="h-12 w-auto">
            </router-link>
        </div>
      <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">Sign in to your account</h2>
    </div>
    <form class="mt-8 space-y-6" @submit.prevent="handleLogin">
      <input type="hidden" name="remember" value="true">
      <div class="rounded-md shadow-sm -space-y-px">
        <div>
          <label for="email-address" class="sr-only">Email address</label>
          <input v-model="email" id="email-address" name="email" type="email" autocomplete="email" required class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-purple-500 focus:border-purple-500 focus:z-10 sm:text-sm" placeholder="Email address">
        </div>
        <div>
          <label for="password" class="sr-only">Password</label>
          <input v-model="password" id="password" name="password" type="password" autocomplete="current-password" required class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-purple-500 focus:border-purple-500 focus:z-10 sm:text-sm" placeholder="Password">
        </div>
      </div>

      <div>
        <button type="submit" class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
          Sign in
        </button>
        <div class="text-sm mt-2 text-center">
            <router-link to="/forgot-password" class="font-medium text-purple-600 hover:text-purple-500">
                Forgot your password?
            </router-link>
        </div>
      </div>
      
      <div class="mt-6">
          <div class="relative">
              <div class="absolute inset-0 flex items-center">
                  <div class="w-full border-t border-gray-300"></div>
              </div>
              <div class="relative flex justify-center text-sm">
                  <span class="px-2 bg-white text-gray-500">
                      Or continue with
                  </span>
              </div>
          </div>
          <div class="mt-6 grid grid-cols-3 gap-3">
              <div>
                  <a href="/api/auth/google/redirect" class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                      <span class="sr-only">Sign in with Google</span>
                      Google
                  </a>
              </div>
              <div>
                  <a href="/api/auth/facebook/redirect" class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                      <span class="sr-only">Sign in with Facebook</span>
                      Facebook
                  </a>
              </div>
              <div>
                  <a href="/api/auth/github/redirect" class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                      <span class="sr-only">Sign in with GitHub</span>
                      GitHub
                  </a>
              </div>
          </div>
      </div>
    </form>
    <!-- Mock info -->
    <div class="text-sm text-center text-gray-500">
        Dev Note: Use admin@udemyclone.com / password
    </div>
  </div>
</div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const email = ref('');
const password = ref('');
const auth = useAuthStore();
const router = useRouter();

const handleLogin = async () => {
    try {
        await auth.login(email.value, password.value);
        router.push('/');
    } catch (e) {
        alert('Login failed');
    }
};

onMounted(async () => {
    const route = useRoute();
    if (route.query.token) {
        auth.token = route.query.token;
        localStorage.setItem('token', auth.token);
        await auth.fetchUser();
        router.replace('/'); // Remove token from URL
    }
});
</script>
