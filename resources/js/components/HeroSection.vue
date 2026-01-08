<template>
  <section class="hero-section relative overflow-hidden bg-gradient-to-br from-purple-900 via-purple-800 to-indigo-900 text-white">
    <!-- Animated background shapes -->
    <div class="absolute inset-0 overflow-hidden">
      <div class="hero-shape hero-shape-1"></div>
      <div class="hero-shape hero-shape-2"></div>
      <div class="hero-shape hero-shape-3"></div>
    </div>
    
    <!-- Glassmorphism overlay -->
    <div class="absolute inset-0 bg-black/10 backdrop-blur-[1px]"></div>
    
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24">
      <div class="grid lg:grid-cols-2 gap-12 items-center">
        <!-- Left content -->
        <div class="text-center lg:text-left">
          <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold leading-tight mb-6">
            <span class="block">{{ $t('home.hero.learn_without') }}</span>
            <span class="block bg-gradient-to-r from-yellow-300 via-pink-300 to-purple-300 bg-clip-text text-transparent">
              {{ $t('home.hero.limits') }}
            </span>
          </h1>
          <p class="text-lg sm:text-xl text-purple-100 mb-8 max-w-xl mx-auto lg:mx-0">
            {{ $t('home.hero.subtitle') }}
          </p>
          
          <!-- Search bar -->
          <div class="relative max-w-xl mx-auto lg:mx-0 mb-8">
            <div class="hero-search-container">
              <input 
                type="text" 
                v-model="searchQuery"
                @keyup.enter="handleSearch"
                :placeholder="$t('home.search_placeholder')"
                class="hero-search-input"
              >
              <button @click="handleSearch" class="hero-search-btn">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 rtl:mirror" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
              </button>
            </div>
          </div>
          
          <!-- Stats -->
          <div class="flex flex-wrap justify-center lg:justify-start gap-8 text-center">
            <div class="stat-item">
              <div class="text-3xl font-bold text-white">15M+</div>
              <div class="text-sm text-purple-200">{{ $t('home.stats.students') }}</div>
            </div>
            <div class="stat-item">
              <div class="text-3xl font-bold text-white">2,500+</div>
              <div class="text-sm text-purple-200">{{ $t('home.stats.courses') }}</div>
            </div>
            <div class="stat-item">
              <div class="text-3xl font-bold text-white">4.8</div>
              <div class="text-sm text-purple-200">{{ $t('home.stats.avg_rating') }}</div>
            </div>
          </div>
        </div>
        
        <!-- Right content - floating cards -->
        <div class="hidden lg:block relative h-96">
          <div class="floating-card floating-card-1">
            <div class="w-16 h-16 rounded-xl bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center mb-3">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
              </svg>
            </div>
            <div class="font-semibold text-gray-800">{{ $t('home.categories.development') }}</div>
            <div class="text-sm text-gray-500">1,200+ {{ $t('home.hero.courses_count_suffix') }}</div>
          </div>
          
          <div class="floating-card floating-card-2">
            <div class="w-16 h-16 rounded-xl bg-gradient-to-br from-green-400 to-emerald-600 flex items-center justify-center mb-3">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
              </svg>
            </div>
            <div class="font-semibold text-gray-800">{{ $t('home.categories.data_science') }}</div>
            <div class="text-sm text-gray-500">800+ {{ $t('home.hero.courses_count_suffix') }}</div>
          </div>
          
          <div class="floating-card floating-card-3">
            <div class="w-16 h-16 rounded-xl bg-gradient-to-br from-orange-400 to-red-500 flex items-center justify-center mb-3">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
              </svg>
            </div>
            <div class="font-semibold text-gray-800">{{ $t('home.categories.design') }}</div>
            <div class="text-sm text-gray-500">650+ {{ $t('home.hero.courses_count_suffix') }}</div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Wave separator -->
    <div class="absolute bottom-0 left-0 right-0">
      <svg viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full">
        <path d="M0 120L60 105C120 90 240 60 360 45C480 30 600 30 720 37.5C840 45 960 60 1080 67.5C1200 75 1320 75 1380 75L1440 75V120H1380C1320 120 1200 120 1080 120C960 120 840 120 720 120C600 120 480 120 360 120C240 120 120 120 60 120H0Z" fill="#f9fafb"/>
      </svg>
    </div>
  </section>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const router = useRouter();
const searchQuery = ref('');

const handleSearch = () => {
  if (searchQuery.value.trim()) {
    router.push({ path: '/', query: { search: searchQuery.value } });
  }
};
</script>

<style scoped>
.hero-section {
  min-height: 600px;
}

.hero-shape {
  position: absolute;
  border-radius: 50%;
  filter: blur(60px);
  opacity: 0.4;
}

.hero-shape-1 {
  width: 400px;
  height: 400px;
  background: linear-gradient(135deg, #a855f7, #ec4899);
  top: -100px;
  right: -100px;
  animation: float 8s ease-in-out infinite;
}

.hero-shape-2 {
  width: 300px;
  height: 300px;
  background: linear-gradient(135deg, #3b82f6, #8b5cf6);
  bottom: 100px;
  left: -50px;
  animation: float 10s ease-in-out infinite reverse;
}

.hero-shape-3 {
  width: 200px;
  height: 200px;
  background: linear-gradient(135deg, #f59e0b, #ef4444);
  top: 50%;
  left: 50%;
  animation: float 12s ease-in-out infinite;
}

@keyframes float {
  0%, 100% { transform: translateY(0) rotate(0deg); }
  50% { transform: translateY(-20px) rotate(5deg); }
}

.hero-search-container {
  display: flex;
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 9999px;
  overflow: hidden;
  transition: all 0.3s ease;
}

.hero-search-container:focus-within {
  background: rgba(255, 255, 255, 0.15);
  border-color: rgba(255, 255, 255, 0.4);
  box-shadow: 0 0 30px rgba(168, 85, 247, 0.3);
}

.hero-search-input {
  flex: 1;
  padding: 1rem 1.5rem;
  background: transparent;
  border: none;
  color: white;
  font-size: 1rem;
  outline: none;
}

.hero-search-input::placeholder {
  color: rgba(255, 255, 255, 0.6);
}

.hero-search-btn {
  padding: 1rem 1.5rem;
  background: linear-gradient(135deg, #a855f7, #ec4899);
  color: white;
  transition: all 0.3s ease;
}

.hero-search-btn:hover {
  background: linear-gradient(135deg, #9333ea, #db2777);
}

.stat-item {
  padding: 0.5rem 1rem;
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  border-radius: 0.75rem;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.floating-card {
  position: absolute;
  padding: 1.25rem;
  background: white;
  border-radius: 1rem;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
  transition: transform 0.3s ease;
}

.floating-card:hover {
  transform: scale(1.05);
}

.floating-card-1 {
  top: 0;
  right: 50px;
  animation: floatCard 6s ease-in-out infinite;
}

.floating-card-2 {
  top: 50%;
  right: 0;
  animation: floatCard 8s ease-in-out infinite 1s;
}

.floating-card-3 {
  bottom: 50px;
  right: 100px;
  animation: floatCard 7s ease-in-out infinite 2s;
}

@keyframes floatCard {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-10px); }
}
</style>
