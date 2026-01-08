<template>
  <nav class="bg-white shadow-sm border-b border-gray-100 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex justify-between items-center bg-white">
      <!-- Left Side -->
      <div class="flex items-center gap-4">
          <!-- Logo -->
          <router-link to="/" class="text-2xl font-bold text-gray-800 flex-shrink-0">UdemyClone</router-link>

          <!-- Categories -->
          <div class="relative group hidden md:block">
              <button class="text-sm font-medium text-gray-700 hover:text-purple-600 px-2 py-4">Categories</button>
              <!-- Dropdown code unchanged, just ensuring parent visibility -->
              <div class="absolute top-14 left-0 w-64 bg-white border border-gray-200 shadow-xl hidden group-hover:block z-50 rounded-sm">
                   <div v-if="courseStore.categories.length === 0" class="p-4 text-gray-500 text-sm">Loading...</div>
                   <ul v-else class="py-2">
                       <li v-for="cat in courseStore.categories" :key="cat.id" class="px-4 py-2 hover:bg-gray-100 relative group/sub">
                           <router-link :to="`/?category=${cat.slug}`" class="block text-sm text-gray-700 flex justify-between items-center">
                               {{ cat.name }}
                               <span v-if="cat.children && cat.children.length" class="text-xs">›</span>
                           </router-link>
                           <div v-if="cat.children && cat.children.length" class="absolute left-full top-0 w-64 bg-white border border-gray-200 shadow-xl hidden group-hover/sub:block">
                               <ul class="py-2">
                                   <li v-for="sub in cat.children" :key="sub.id" class="px-4 py-2 hover:bg-gray-100">
                                       <router-link :to="`/?category=${sub.slug}`" class="block text-sm text-gray-700">{{ sub.name }}</router-link>
                                   </li>
                               </ul>
                           </div>
                       </li>
                   </ul>
              </div>
          </div>
      </div>

      <!-- Center Search -->
      <div class="hidden md:flex flex-1 mx-4 max-w-2xl relative">
        <div class="relative w-full">
            <button class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </button>
            <input 
              type="text" 
              v-model="searchQuery"
              @keyup.enter="handleSearch"
              :placeholder="$t('catalog.search_placeholder')"
              class="w-full bg-gray-50 border border-gray-300 rounded-full py-2 pl-10 pr-4 focus:ring-2 focus:ring-purple-500 focus:outline-none"
            >
        </div>
      </div>

      <!-- Right Side Links -->
      <div class="flex items-center space-x-4">
         <!-- Wishlist -->
         <router-link to="/wishlist" class="text-gray-600 hover:text-purple-600 relative group hidden sm:block">
             <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
             </svg>
             <span v-if="wishlistStore.count > 0" class="absolute -top-1 -right-1 bg-purple-600 text-white text-xs font-bold rounded-full w-4 h-4 flex items-center justify-center">
                 {{ wishlistStore.count }}
             </span>
         </router-link>

         <!-- Cart -->
         <router-link to="/cart" class="text-gray-600 hover:text-purple-600 relative group hidden sm:block">
             <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
             </svg>
             <span v-if="cartStore.count > 0" class="absolute -top-1 -right-1 bg-purple-600 text-white text-xs font-bold rounded-full w-4 h-4 flex items-center justify-center">
                 {{ cartStore.count }}
             </span>
         </router-link>

        <router-link to="/instructor" class="text-gray-600 hover:text-gray-900 hidden lg:block text-sm">
           {{ $t('nav.instructor') }}
        </router-link>
        
        <router-link to="/my-courses" class="text-gray-600 hover:text-gray-900 text-sm">
           {{ $t('nav.my_learning') }}
        </router-link>

        <div v-if="!auth.isAuthenticated" class="flex items-center space-x-2">
            <router-link to="/login" class="px-4 py-2 text-sm font-bold text-gray-700 hover:bg-gray-50 border border-gray-300 rounded-sm">
                {{ $t('nav.login') }}
            </router-link>
            <router-link to="/signup" class="px-4 py-2 text-sm font-bold text-white bg-gray-900 hover:bg-gray-800 rounded-sm">
                {{ $t('nav.signup') }}
            </router-link>
        </div>
        <div v-else class="flex items-center space-x-4 relative" ref="userMenuRef">
           <button 
                @click.stop="toggleUserMenu"
                class="w-8 h-8 rounded-full bg-purple-600 text-white flex items-center justify-center font-bold cursor-pointer hover:bg-purple-700 transition-colors focus:outline-none" 
                :title="auth.user?.name"
            >
               {{ auth.user?.name?.[0] || 'U' }}
           </button>

           <!-- Dropdown Menu -->
           <div v-if="isUserMenuOpen" class="absolute top-12 right-0 w-72 bg-white border border-gray-200 shadow-xl rounded-sm z-50 overflow-hidden group">
               <!-- User Info Header -->
               <div class="p-4 border-b border-gray-100 flex items-center gap-3 hover:bg-gray-50 transition-colors">
                    <div class="w-16 h-16 rounded-full bg-purple-600 text-white flex items-center justify-center text-xl font-bold">
                         {{ auth.user?.name?.[0] || 'U' }}
                    </div>
                    <div class="overflow-hidden">
                        <div class="font-bold text-gray-900 truncate">{{ auth.user?.name }}</div>
                        <div class="text-xs text-gray-500 truncate">{{ auth.user?.email }}</div>
                    </div>
               </div>

               <!-- Menu Links -->
               <div class="py-2">
                   <router-link to="/my-courses" class="block px-4 py-3 text-sm text-gray-700 hover:text-purple-600 hover:bg-gray-50" @click="isUserMenuOpen = false">
                       {{ $t('nav.my_learning') || 'My learning' }}
                   </router-link>
                   <router-link to="/cart" class="block px-4 py-3 text-sm text-gray-700 hover:text-purple-600 hover:bg-gray-50 md:hidden" @click="isUserMenuOpen = false">
                       My cart
                   </router-link>
                    <router-link to="/wishlist" class="block px-4 py-3 text-sm text-gray-700 hover:text-purple-600 hover:bg-gray-50 md:hidden" @click="isUserMenuOpen = false">
                       My wishlist
                   </router-link>
                   <router-link to="/instructor" class="block px-4 py-3 text-sm text-gray-700 hover:text-purple-600 hover:bg-gray-50" @click="isUserMenuOpen = false">
                       {{ $t('nav.instructor') || 'Instructor Dashboard' }}
                   </router-link>
               </div>
               
                <div class="border-t border-gray-100 py-2">
                    <router-link to="/notifications" class="block px-4 py-3 text-sm text-gray-700 hover:text-purple-600 hover:bg-gray-50" @click="isUserMenuOpen = false">
                        Notifications
                    </router-link>
                    <router-link to="/messages" class="block px-4 py-3 text-sm text-gray-700 hover:text-purple-600 hover:bg-gray-50" @click="isUserMenuOpen = false">
                        Messages
                    </router-link>
                </div>

                <div class="border-t border-gray-100 py-2">
                    <router-link to="/account-settings" class="block px-4 py-3 text-sm text-gray-700 hover:text-purple-600 hover:bg-gray-50" @click="isUserMenuOpen = false">
                        Account settings
                    </router-link>
                    <router-link to="/payment-methods" class="block px-4 py-3 text-sm text-gray-700 hover:text-purple-600 hover:bg-gray-50" @click="isUserMenuOpen = false">
                       Payment methods
                    </router-link>
                </div>

               <!-- Logout -->
               <div class="border-t border-gray-100 py-2">
                   <button @click="handleLogout" class="w-full text-left px-4 py-3 text-sm text-gray-700 hover:text-purple-600 hover:bg-gray-50">
                       Log out
                   </button>
               </div>
           </div>
        </div>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useAuthStore } from '../stores/auth';
import { useCourseStore } from '../stores/course';
import { useCartStore } from '../stores/cart';
import { useWishlistStore } from '../stores/wishlist';
import { useI18n } from 'vue-i18n';
import { useRouter, useRoute } from 'vue-router';

const auth = useAuthStore();
const courseStore = useCourseStore();
const cartStore = useCartStore();
const wishlistStore = useWishlistStore();

const { locale } = useI18n();
const router = useRouter();
const route = useRoute();

const searchQuery = ref('');

const isUserMenuOpen = ref(false);
const userMenuRef = ref(null);

const toggleUserMenu = () => {
    isUserMenuOpen.value = !isUserMenuOpen.value;
};

const closeUserMenu = (e) => {
    if (userMenuRef.value && !userMenuRef.value.contains(e.target)) {
        isUserMenuOpen.value = false;
    }
};

onMounted(() => {
    if (route.query.search) {
        searchQuery.value = route.query.search;
    }
    courseStore.fetchCategories();
    document.addEventListener('click', closeUserMenu);
});


onUnmounted(() => {
    document.removeEventListener('click', closeUserMenu);
});

const handleSearch = () => {
    router.push({ 
        path: '/', 
        query: { 
            ...route.query, 
            search: searchQuery.value 
        } 
    });
};

const toggleLang = () => {
  locale.value = locale.value === 'en' ? 'ar' : 'en';
};

const handleLogout = () => {
    isUserMenuOpen.value = false;
    auth.logout();
    router.push('/login');
};
</script>
