<template>
  <div class="min-h-screen bg-gray-50">
    <Navbar />
    
    <!-- Hero Section -->
    <HeroSection />
    
    <!-- Featured Categories -->
    <FeaturedCategories />
    
    <!-- Trending Courses -->
    <TrendingCourses v-if="courseStore.courses.length > 0" />
    
    <!-- Popular Courses Section -->
    <section class="py-16 bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-4">
          <div>
            <h2 class="text-3xl font-bold text-gray-900 mb-2">{{ $t('home.all_courses') }}</h2>
            <p class="text-gray-600">{{ $t('home.explore_library') }}</p>
          </div>
          
          <!-- Filters -->
          <div class="flex flex-wrap gap-3 items-center">
            <select 
              v-model="selectedCategory" 
              @change="applyFilters"
              class="filter-select"
            >
              <option value="">{{ $t('home.filters.all_categories') }}</option>
              <option v-for="cat in courseStore.categories" :key="cat.id" :value="cat.slug">
                {{ cat.name }}
              </option>
            </select>
            
            <select 
              v-model="selectedLevel" 
              @change="applyFilters"
              class="filter-select"
            >
              <option value="">All Levels</option>
              <option v-for="level in courseStore.levels" :key="level.id" :value="level.slug || level.id">
                {{ level.name }}
              </option>
            </select>
            
            <div class="flex items-center gap-2">
              <input 
                v-model="priceMin" 
                type="number" 
                placeholder="Min $" 
                min="0"
                class="filter-input w-20"
                @change="applyFilters"
              >
              <span class="text-gray-400">-</span>
              <input 
                v-model="priceMax" 
                type="number" 
                placeholder="Max $" 
                min="0"
                class="filter-input w-20"
                @change="applyFilters"
              >
            </div>
            
            <select 
              v-model="sortBy" 
              @change="applyFilters"
              class="filter-select"
            >
              <option value="">{{ $t('home.filters.sort_by') }}</option>
              <option value="popular">{{ $t('home.filters.most_popular') }}</option>
              <option value="newest">{{ $t('home.filters.newest') }}</option>
              <option value="rating">{{ $t('home.filters.highest_rated') }}</option>
              <option value="price_low">{{ $t('home.filters.price_low') }}</option>
              <option value="price_high">{{ $t('home.filters.price_high') }}</option>
            </select>
          </div>
        </div>
        
        <!-- Loading State -->
        <div v-if="courseStore.loading" class="flex justify-center items-center py-20">
          <div class="loading-spinner"></div>
        </div>
        
        <!-- Empty State -->
        <div v-else-if="courseStore.courses.length === 0" class="text-center py-20">
          <div class="w-24 h-24 mx-auto mb-6 bg-purple-100 rounded-full flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
          </div>
          <h3 class="text-xl font-bold text-gray-900 mb-2">{{ $t('home.no_courses') }}</h3>
          <p class="text-gray-500 mb-6">{{ $t('home.adjust_filters') }}</p>
          <button @click="clearFilters" class="btn-primary">
            {{ $t('home.clear_filters') }}
          </button>
        </div>
        
        <!-- Course Grid -->
        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          <CourseCard 
            v-for="(course, index) in courseStore.courses" 
            :key="course.id" 
            :course="course"
            :style="{ animationDelay: `${index * 0.05}s` }"
            class="animate-fadeIn"
          />
        </div>
      </div>
    </section>
    
    <!-- Newsletter Section -->
    <section class="py-20 bg-gradient-to-r from-purple-600 to-indigo-600 text-white">
      <div class="max-w-4xl mx-auto px-4 text-center">
        <h2 class="text-3xl font-bold mb-4">{{ $t('common.newsletter.title') }}</h2>
        <p class="text-purple-100 mb-8 max-w-2xl mx-auto">
          {{ $t('common.newsletter.subtitle') }}
        </p>
        <form @submit.prevent class="flex flex-col sm:flex-row gap-4 max-w-lg mx-auto">
          <input 
            type="email" 
            :placeholder="$t('common.newsletter.placeholder')"
            class="flex-1 px-6 py-3 rounded-full text-gray-900 focus:outline-none focus:ring-4 focus:ring-purple-300"
          >
          <button type="submit" class="px-8 py-3 bg-white text-purple-600 font-bold rounded-full hover:bg-gray-100 transition-colors">
            {{ $t('common.newsletter.subscribe') }}
          </button>
        </form>
      </div>
    </section>
    
    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-400 py-12">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 mb-8">
          <div>
            <h4 class="text-white font-semibold mb-4">{{ $t('common.footer.company') }}</h4>
            <ul class="space-y-2 text-sm">
              <li><a href="#" class="hover:text-white transition-colors">{{ $t('common.footer.about') }}</a></li>
              <li><a href="#" class="hover:text-white transition-colors">{{ $t('common.footer.careers') }}</a></li>
              <li><a href="#" class="hover:text-white transition-colors">{{ $t('common.footer.blog') }}</a></li>
            </ul>
          </div>
          <div>
            <h4 class="text-white font-semibold mb-4">{{ $t('common.footer.community') }}</h4>
            <ul class="space-y-2 text-sm">
              <li><a href="#" class="hover:text-white transition-colors">{{ $t('common.footer.become_instructor') }}</a></li>
              <li><a href="#" class="hover:text-white transition-colors">{{ $t('common.footer.affiliate') }}</a></li>
              <li><a href="#" class="hover:text-white transition-colors">{{ $t('common.footer.partners') }}</a></li>
            </ul>
          </div>
          <div>
            <h4 class="text-white font-semibold mb-4">{{ $t('common.footer.support') }}</h4>
            <ul class="space-y-2 text-sm">
              <li><a href="#" class="hover:text-white transition-colors">{{ $t('common.footer.help') }}</a></li>
              <li><a href="#" class="hover:text-white transition-colors">{{ $t('common.footer.contact') }}</a></li>
              <li><a href="#" class="hover:text-white transition-colors">{{ $t('common.footer.faq') }}</a></li>
            </ul>
          </div>
          <div>
            <h4 class="text-white font-semibold mb-4">{{ $t('common.footer.legal') }}</h4>
            <ul class="space-y-2 text-sm">
              <li><a href="#" class="hover:text-white transition-colors">{{ $t('common.footer.terms') }}</a></li>
              <li><a href="#" class="hover:text-white transition-colors">{{ $t('common.footer.privacy') }}</a></li>
              <li><a href="#" class="hover:text-white transition-colors">{{ $t('common.footer.cookie') }}</a></li>
            </ul>
          </div>
        </div>
        <div class="border-t border-gray-800 pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
          <div class="text-2xl font-bold text-white">UdemyClone</div>
          <p class="text-sm">© 2026 UdemyClone. {{ $t('common.footer.rights') }}</p>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useCourseStore } from '../stores/course';
import Navbar from '../components/Navbar.vue';
import CourseCard from '../components/CourseCard.vue';
import HeroSection from '../components/HeroSection.vue';
import FeaturedCategories from '../components/FeaturedCategories.vue';
import TrendingCourses from '../components/TrendingCourses.vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const courseStore = useCourseStore();
const route = useRoute();
const router = useRouter();

const selectedCategory = ref('');
const selectedLevel = ref('');
const priceMin = ref('');
const priceMax = ref('');
const sortBy = ref('');

const loadCourses = () => {
  courseStore.fetchCourses(route.query);
};

const applyFilters = () => {
  const query = { ...route.query };
  
  // Category filter
  if (selectedCategory.value) {
    query.category = selectedCategory.value;
  } else {
    delete query.category;
  }
  
  // Level filter
  if (selectedLevel.value) {
    query.level = selectedLevel.value;
  } else {
    delete query.level;
  }
  
  // Price range filters
  if (priceMin.value) {
    query.price_min = priceMin.value;
  } else {
    delete query.price_min;
  }
  
  if (priceMax.value) {
    query.price_max = priceMax.value;
  } else {
    delete query.price_max;
  }
  
  // Sort
  if (sortBy.value) {
    query.sort = sortBy.value;
  } else {
    delete query.sort;
  }
  
  router.push({ path: '/', query });
};

const clearFilters = () => {
  selectedCategory.value = '';
  selectedLevel.value = '';
  priceMin.value = '';
  priceMax.value = '';
  sortBy.value = '';
  router.push({ path: '/' });
};

onMounted(() => {
  loadCourses();
  courseStore.fetchLevels();
  
  // Initialize filters from URL query params
  if (route.query.category) {
    selectedCategory.value = route.query.category;
  }
  if (route.query.level) {
    selectedLevel.value = route.query.level;
  }
  if (route.query.price_min) {
    priceMin.value = route.query.price_min;
  }
  if (route.query.price_max) {
    priceMax.value = route.query.price_max;
  }
  if (route.query.sort) {
    sortBy.value = route.query.sort;
  }
});

watch(() => route.query, () => {
  loadCourses();
}, { deep: true });
</script>

<style scoped>
.filter-select {
  padding: 0.625rem 1rem;
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 0.5rem;
  color: #374151;
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.3s ease;
}

.filter-select:hover {
  border-color: #a855f7;
}

.filter-select:focus {
  outline: none;
  border-color: #a855f7;
  box-shadow: 0 0 0 3px rgba(168, 85, 247, 0.1);
}

.filter-input {
  padding: 0.625rem 0.75rem;
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 0.5rem;
  color: #374151;
  font-size: 0.875rem;
  transition: all 0.3s ease;
}

.filter-input:hover {
  border-color: #a855f7;
}

.filter-input:focus {
  outline: none;
  border-color: #a855f7;
  box-shadow: 0 0 0 3px rgba(168, 85, 247, 0.1);
}

.filter-input::placeholder {
  color: #9ca3af;
}

.loading-spinner {
  width: 48px;
  height: 48px;
  border: 4px solid #e5e7eb;
  border-top-color: #a855f7;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.btn-primary {
  padding: 0.75rem 1.5rem;
  background: linear-gradient(135deg, #a855f7, #ec4899);
  color: white;
  font-weight: 600;
  border-radius: 0.5rem;
  transition: all 0.3s ease;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 30px rgba(168, 85, 247, 0.3);
}

.animate-fadeIn {
  animation: fadeIn 0.5s ease forwards;
  opacity: 0;
}

@keyframes fadeIn {
  to { opacity: 1; }
}
</style>
