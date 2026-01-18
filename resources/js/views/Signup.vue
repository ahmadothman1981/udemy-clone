<template>
  <div class="min-h-screen flex items-center justify-center relative overflow-hidden bg-gray-900">
    <!-- Language Switcher -->
    <div class="absolute top-4 right-4 z-50">
      <button 
        @click="toggleLanguage" 
        class="flex items-center space-x-2 px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 border border-white/10 transition-all text-white text-sm"
      >
        <span class="uppercase">{{ currentLang }}</span>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" />
        </svg>
      </button>
    </div>

    <!-- Animated Background Shapes -->
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden z-0">
      <div class="absolute top-[-10%] right-[-10%] w-[50%] h-[50%] bg-purple-600 rounded-full mix-blend-multiply filter blur-[100px] opacity-30 animate-blob"></div>
      <div class="absolute top-[-10%] left-[-10%] w-[50%] h-[50%] bg-blue-600 rounded-full mix-blend-multiply filter blur-[100px] opacity-30 animate-blob animation-delay-2000"></div>
      <div class="absolute bottom-[-20%] right-[20%] w-[60%] h-[60%] bg-pink-600 rounded-full mix-blend-multiply filter blur-[100px] opacity-30 animate-blob animation-delay-4000"></div>
    </div>

    <!-- Main Content -->
    <div class="relative z-10 w-full max-w-md px-6 py-12">
      <div class="bg-white/10 backdrop-blur-xl border border-white/20 rounded-2xl shadow-2xl p-8 transform transition-all hover:scale-[1.01]">
        <!-- Header -->
        <div class="text-center mb-8">
          <router-link to="/" class="inline-block mb-4 transition-transform hover:scale-105">
            <img :src="'/images/logo.png'" alt="NetLearn" class="h-12 w-auto drop-shadow-lg mx-auto" />
          </router-link>
          <h2 class="text-3xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-white to-purple-200">
            {{ $t('auth.create_account') }}
          </h2>
          <p class="text-purple-200 mt-2 text-sm">
            {{ $t('auth.join_thousands') }}
          </p>
        </div>

        <!-- Form -->
        <form class="space-y-6" @submit.prevent="handleSignup">
          <div class="space-y-4">
            <!-- Name -->
            <div class="group relative">
              <label for="name" class="block text-xs font-medium text-purple-200 mb-1 uppercase tracking-wider">{{ $t('auth.full_name_label') }}</label>
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
                  :placeholder="$t('auth.placeholders.name')"
                />
              </div>
            </div>

            <!-- Email -->
            <div class="group relative">
              <label for="email" class="block text-xs font-medium text-purple-200 mb-1 uppercase tracking-wider">{{ $t('auth.email_label') }}</label>
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
                  :placeholder="$t('auth.placeholders.email')"
                />
              </div>
            </div>

            <!-- Password -->
            <div class="group relative">
              <label for="password" class="block text-xs font-medium text-purple-200 mb-1 uppercase tracking-wider">{{ $t('auth.password_label') }}</label>
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
                  :placeholder="$t('auth.placeholders.create_password')"
                />
              </div>
            </div>

             <!-- Confirm Password -->
            <div class="group relative">
              <label for="password_confirmation" class="block text-xs font-medium text-purple-200 mb-1 uppercase tracking-wider">{{ $t('auth.confirm_password_label') }}</label>
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
                  :placeholder="$t('auth.placeholders.confirm_password')"
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
            {{ loading ? $t('auth.creating_account') : $t('auth.sign_up') }}
          </button>
        </form>

        <!-- Social Login -->
        <div class="mt-8">
          <div class="relative">
            <div class="absolute inset-0 flex items-center">
              <div class="w-full border-t border-white/10"></div>
            </div>
            <div class="relative flex justify-center text-sm">
              <span class="px-2 bg-transparent text-gray-400">{{ $t('auth.or_sign_up_with') }}</span>
            </div>
          </div>

          <div class="mt-6 flex justify-center gap-4">
            <!-- Google -->
            <a href="/api/auth/google/redirect" class="social-btn group flex items-center justify-center w-12 h-12 rounded-lg bg-white/5 border border-white/10 hover:bg-white/10 transition-all duration-200">
              <span class="sr-only">Sign up with Google</span>
              <svg class="h-6 w-6 text-red-500 group-hover:text-red-400 transition-colors" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12.48 10.92v3.28h7.84c-.24 1.84-.853 3.187-1.787 4.133-1.147 1.147-2.933 2.4-6.053 2.4-4.827 0-8.6-3.893-8.6-8.72s3.773-8.72 8.6-8.72c2.6 0 4.507 1.027 5.907 2.347l2.307-2.307C18.747 1.44 16.133 0 12.48 0 5.867 0 .533 5.347.533 12s5.334 12 11.947 12c3.48 0 6.147-1.133 8.213-3.293 2.12-2.12 2.76-5.267 2.76-7.787 0-.787-.067-1.573-.187-2.907h-10.773z" />
              </svg>
            </a>
            <!-- Facebook -->
            <a href="/api/auth/facebook/redirect" class="social-btn group flex items-center justify-center w-12 h-12 rounded-lg bg-white/5 border border-white/10 hover:bg-white/10 transition-all duration-200">
              <span class="sr-only">Sign up with Facebook</span>
              <svg class="h-6 w-6 text-blue-500 group-hover:text-blue-400 transition-colors" fill="currentColor" viewBox="0 0 24 24">
                 <path fill-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clip-rule="evenodd" />
              </svg>
            </a>
            <!-- GitHub -->
            <a href="/api/auth/github/redirect" class="social-btn group flex items-center justify-center w-12 h-12 rounded-lg bg-white/5 border border-white/10 hover:bg-white/10 transition-all duration-200">
               <span class="sr-only">Sign up with GitHub</span>
               <svg class="h-6 w-6 text-white group-hover:text-gray-200 transition-colors" fill="currentColor" viewBox="0 0 24 24">
                  <path fill-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z" clip-rule="evenodd" />
               </svg>
            </a>
          </div>
        </div>

        <!-- Login Link -->
        <p class="mt-8 text-center text-sm text-gray-400">
          {{ $t('auth.already_have_account') }}
          <router-link to="/login" class="font-semibold text-purple-400 hover:text-purple-300 transition-colors">
            {{ $t('auth.login') }}
          </router-link>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import { useI18n } from 'vue-i18n';

const router = useRouter();
const authStore = useAuthStore();
const { t, locale } = useI18n();

const currentLang = computed(() => locale.value);

const toggleLanguage = () => {
    const newLang = locale.value === 'en' ? 'ar' : 'en';
    locale.value = newLang;
    document.dir = newLang === 'ar' ? 'rtl' : 'ltr';
    localStorage.setItem('locale', newLang);
};

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
             error.value = t('common.error');
        }
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    document.dir = locale.value === 'ar' ? 'rtl' : 'ltr';
});
</script>
