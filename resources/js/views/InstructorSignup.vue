<template>
  <div class="min-h-screen flex items-center justify-center relative overflow-hidden bg-gray-900">
    <!-- Animated Background Shapes -->
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden z-0">
      <div class="absolute top-[-10%] left-[20%] w-[50%] h-[50%] bg-indigo-600 rounded-full mix-blend-multiply filter blur-[100px] opacity-30 animate-blob"></div>
      <div class="absolute top-[20%] right-[-10%] w-[50%] h-[50%] bg-purple-600 rounded-full mix-blend-multiply filter blur-[100px] opacity-30 animate-blob animation-delay-2000"></div>
      <div class="absolute bottom-[-20%] left-[10%] w-[60%] h-[60%] bg-blue-600 rounded-full mix-blend-multiply filter blur-[100px] opacity-30 animate-blob animation-delay-4000"></div>
    </div>

    <!-- Main Content -->
    <div class="relative z-10 w-full max-w-md px-6 py-12">
      <div class="bg-white/10 backdrop-blur-xl border border-white/20 rounded-2xl shadow-2xl p-8 transform transition-all hover:scale-[1.01]">
        <!-- Header -->
        <div class="text-center mb-8">
          <router-link to="/" class="inline-block mb-4 transition-transform hover:scale-105">
            <img :src="'/images/logo.png'" alt="UdemyClone" class="h-12 w-auto drop-shadow-lg mx-auto" />
          </router-link>
          <h2 class="text-3xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-white to-indigo-200">
            Become an Instructor
          </h2>
          <p class="text-indigo-200 mt-2 text-sm">
            Share your knowledge and earn money
          </p>
        </div>

        <!-- Form -->
        <form class="space-y-6" @submit.prevent="handleSignup">
          <div class="space-y-4">
            <!-- Name -->
            <div class="group relative">
              <label for="name" class="block text-xs font-medium text-indigo-200 mb-1 uppercase tracking-wider">Full Name</label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <svg class="h-5 w-5 text-gray-400 group-focus-within:text-purple-400 transition-colors" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                  </svg>
                </div>
                <input
                  v-model="form.name"
                  id="name"
                  name="name"
                  type="text"
                  required
                  class="block w-full pl-10 pr-3 py-3 bg-white/5 border border-white/10 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200 hover:bg-white/10"
                  placeholder="Enter your full name"
                />
              </div>
            </div>

            <!-- Email -->
            <div class="group relative">
              <label for="email" class="block text-xs font-medium text-indigo-200 mb-1 uppercase tracking-wider">Email Address</label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <svg class="h-5 w-5 text-gray-400 group-focus-within:text-purple-400 transition-colors" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                    <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                  </svg>
                </div>
                <input
                  v-model="form.email"
                  id="email"
                  name="email"
                  type="email"
                  required
                  class="block w-full pl-10 pr-3 py-3 bg-white/5 border border-white/10 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200 hover:bg-white/10"
                  placeholder="Enter your email"
                />
              </div>
            </div>

            <!-- Password -->
            <div class="group relative">
              <label for="password" class="block text-xs font-medium text-indigo-200 mb-1 uppercase tracking-wider">Password</label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <svg class="h-5 w-5 text-gray-400 group-focus-within:text-purple-400 transition-colors" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                  </svg>
                </div>
                <input
                  v-model="form.password"
                  id="password"
                  name="password"
                  type="password"
                  required
                  class="block w-full pl-10 pr-3 py-3 bg-white/5 border border-white/10 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200 hover:bg-white/10"
                  placeholder="Create a password"
                />
              </div>
            </div>

             <!-- Confirm Password -->
            <div class="group relative">
              <label for="password_confirmation" class="block text-xs font-medium text-indigo-200 mb-1 uppercase tracking-wider">Confirm Password</label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <svg class="h-5 w-5 text-gray-400 group-focus-within:text-purple-400 transition-colors" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                  </svg>
                </div>
                <input
                  v-model="form.password_confirmation"
                  id="password_confirmation"
                  name="password_confirmation"
                  type="password"
                  required
                  class="block w-full pl-10 pr-3 py-3 bg-white/5 border border-white/10 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200 hover:bg-white/10"
                  placeholder="Confirm your password"
                />
              </div>
            </div>
          </div>

          <div v-if="error" class="text-red-400 text-sm text-center bg-red-500/10 p-3 rounded-lg border border-red-500/20">
            {{ error }}
          </div>

          <button
            type="submit"
            :disabled="loading"
            class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-bold rounded-lg text-white bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transform transition-all duration-200 hover:scale-[1.02] active:scale-[0.98] shadow-lg hover:shadow-purple-500/30"
          >
            <span v-if="loading" class="absolute left-0 inset-y-0 flex items-center pl-3">
              <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
            </span>
            {{ loading ? 'Registering...' : 'Sign Up as Instructor' }}
          </button>
        </form>

        <p class="mt-8 text-center text-sm text-gray-400">
          Already an instructor?
          <router-link to="/login" class="font-semibold text-indigo-400 hover:text-indigo-300 transition-colors">
            Login
          </router-link>
        </p>
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
    password_confirmation: '',
    role: 'instructor' // Explicitly set role
});

const error = ref('');
const loading = ref(false);

const handleSignup = async () => {
    loading.value = true;
    error.value = '';
    
    try {
        await authStore.register(form.value);
        // After successful registration, redirect to instructor dashboard
        router.push('/instructor');
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
