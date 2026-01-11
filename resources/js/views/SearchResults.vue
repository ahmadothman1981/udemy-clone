<template>
  <div class="min-h-screen bg-gray-50">
    <Navbar />
    
    <!-- Search Header -->
    <section class="bg-gradient-to-r from-purple-600 to-indigo-600 py-12">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-white mb-2">
          {{ $t('search.results_for') }} "{{ searchQuery }}"
        </h1>
        <p class="text-purple-100">
          {{ totalResults }} {{ $t('search.courses_found') }}
        </p>
      </div>
    </section>
    
    <!-- Main Content -->
    <section class="py-10">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col lg:flex-row gap-8">
          
          <!-- Sidebar Filters -->
          <aside class="w-full lg:w-64 flex-shrink-0">
            <div class="bg-white rounded-xl shadow-sm p-6 sticky top-24">
              <div class="flex justify-between items-center mb-6">
                <h3 class="font-bold text-gray-900">{{ $t('search.filters') }}</h3>
                <button 
                  @click="clearFilters" 
                  class="text-sm text-purple-600 hover:text-purple-800 font-medium"
                >
                  {{ $t('search.clear_all') }}
                </button>
              </div>
              
              <!-- Category Filter -->
              <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  {{ $t('search.category') }}
                </label>
                <select 
                  v-model="selectedCategory" 
                  @change="applyFilters"
                  class="filter-select w-full"
                >
                  <option value="">{{ $t('home.filters.all_categories') }}</option>
                  <option v-for="cat in courseStore.categories" :key="cat.id" :value="cat.slug">
                    {{ cat.name }}
                  </option>
                </select>
              </div>
              
              <!-- Level Filter -->
              <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  {{ $t('search.level') }}
                </label>
                <select 
                  v-model="selectedLevel" 
                  @change="applyFilters"
                  class="filter-select w-full"
                >
                  <option value="">All Levels</option>
                  <option v-for="level in courseStore.levels" :key="level.id" :value="level.slug || level.id">
                    {{ level.name }}
                  </option>
                </select>
              </div>
              
              <!-- Price Range -->
              <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  {{ $t('search.price_range') }}
                </label>
                <div class="flex items-center gap-2">
                  <input 
                    v-model="priceMin" 
                    type="number" 
                    placeholder="Min $" 
                    min="0"
                    class="filter-input flex-1"
                    @change="applyFilters"
                  >
                  <span class="text-gray-400">-</span>
                  <input 
                    v-model="priceMax" 
                    type="number" 
                    placeholder="Max $" 
                    min="0"
                    class="filter-input flex-1"
                    @change="applyFilters"
                  >
                </div>
              </div>
              
              <!-- Sort By -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  {{ $t('search.sort_by') }}
                </label>
                <select 
                  v-model="sortBy" 
                  @change="applyFilters"
                  class="filter-select w-full"
                >
                  <option value="">{{ $t('home.filters.most_popular') }}</option>
                  <option value="newest">{{ $t('home.filters.newest') }}</option>
                  <option value="rating">{{ $t('home.filters.highest_rated') }}</option>
                  <option value="price_low">{{ $t('home.filters.price_low') }}</option>
                  <option value="price_high">{{ $t('home.filters.price_high') }}</option>
                </select>
              </div>
            </div>
          </aside>
          
          <!-- Results Grid -->
          <main class="flex-1">
            <!-- Loading State -->
            <div v-if="loading" class="flex justify-center items-center py-20">
              <div class="loading-spinner"></div>
            </div>
            
            <!-- Empty State -->
            <div v-else-if="courses.length === 0" class="text-center py-20 bg-white rounded-xl shadow-sm">
              <div class="w-24 h-24 mx-auto mb-6 bg-purple-100 rounded-full flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
              </div>
              <h3 class="text-xl font-bold text-gray-900 mb-2">{{ $t('search.no_results') }}</h3>
              <p class="text-gray-500 mb-6">{{ $t('search.try_different') }}</p>
              <router-link to="/" class="btn-primary inline-block">
                {{ $t('search.browse_all') }}
              </router-link>
            </div>
            
            <!-- Course Grid -->
            <div v-else class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
              <CourseCard 
                v-for="(course, index) in courses" 
                :key="course.id" 
                :course="course"
                :style="{ animationDelay: `${index * 0.05}s` }"
                class="animate-fadeIn"
              />
            </div>
            
            <!-- Pagination -->
            <div v-if="totalPages > 1" class="mt-10 flex justify-center gap-2">
              <button 
                @click="goToPage(currentPage - 1)" 
                :disabled="currentPage === 1"
                class="pagination-btn"
                :class="{ 'opacity-50 cursor-not-allowed': currentPage === 1 }"
              >
                ‹
              </button>
              <button 
                v-for="page in visiblePages" 
                :key="page"
                @click="goToPage(page)"
                class="pagination-btn"
                :class="{ 'bg-purple-600 text-white': page === currentPage }"
              >
                {{ page }}
              </button>
              <button 
                @click="goToPage(currentPage + 1)" 
                :disabled="currentPage === totalPages"
                class="pagination-btn"
                :class="{ 'opacity-50 cursor-not-allowed': currentPage === totalPages }"
              >
                ›
              </button>
            </div>
          </main>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useCourseStore } from '../stores/course';
import Navbar from '../components/Navbar.vue';
import CourseCard from '../components/CourseCard.vue';
import { useI18n } from 'vue-i18n';
import axios from 'axios';

const { t } = useI18n();
const route = useRoute();
const router = useRouter();
const courseStore = useCourseStore();

// State
const loading = ref(false);
const courses = ref([]);
const totalResults = ref(0);
const currentPage = ref(1);
const totalPages = ref(1);
const perPage = 12;

// Filters
const selectedCategory = ref('');
const selectedLevel = ref('');
const priceMin = ref('');
const priceMax = ref('');
const sortBy = ref('');

// Computed
const searchQuery = computed(() => route.query.q || '');

const visiblePages = computed(() => {
  const pages = [];
  const start = Math.max(1, currentPage.value - 2);
  const end = Math.min(totalPages.value, currentPage.value + 2);
  for (let i = start; i <= end; i++) {
    pages.push(i);
  }
  return pages;
});

// Methods
const fetchSearchResults = async () => {
  loading.value = true;
  try {
    const params = {
      search: searchQuery.value,
      page: currentPage.value,
      per_page: perPage
    };
    
    if (selectedCategory.value) params.category = selectedCategory.value;
    if (selectedLevel.value) params.level = selectedLevel.value;
    if (priceMin.value) params.price_min = priceMin.value;
    if (priceMax.value) params.price_max = priceMax.value;
    if (sortBy.value) params.sort = sortBy.value;
    
    const response = await axios.get('/api/courses', { params });
    courses.value = response.data.data || response.data;
    totalResults.value = response.data.meta?.total || courses.value.length;
    totalPages.value = response.data.meta?.last_page || 1;
  } catch (error) {
    console.error('Search error:', error);
    courses.value = [];
    totalResults.value = 0;
  } finally {
    loading.value = false;
  }
};

const applyFilters = () => {
  currentPage.value = 1;
  updateUrl();
  fetchSearchResults();
};

const clearFilters = () => {
  selectedCategory.value = '';
  selectedLevel.value = '';
  priceMin.value = '';
  priceMax.value = '';
  sortBy.value = '';
  currentPage.value = 1;
  updateUrl();
  fetchSearchResults();
};

const goToPage = (page) => {
  if (page < 1 || page > totalPages.value) return;
  currentPage.value = page;
  updateUrl();
  fetchSearchResults();
  window.scrollTo({ top: 0, behavior: 'smooth' });
};

const updateUrl = () => {
  const query = { q: searchQuery.value };
  if (currentPage.value > 1) query.page = currentPage.value;
  if (selectedCategory.value) query.category = selectedCategory.value;
  if (selectedLevel.value) query.level = selectedLevel.value;
  if (priceMin.value) query.price_min = priceMin.value;
  if (priceMax.value) query.price_max = priceMax.value;
  if (sortBy.value) query.sort = sortBy.value;
  
  router.replace({ path: '/search', query });
};

const initFromUrl = () => {
  currentPage.value = parseInt(route.query.page) || 1;
  selectedCategory.value = route.query.category || '';
  selectedLevel.value = route.query.level || '';
  priceMin.value = route.query.price_min || '';
  priceMax.value = route.query.price_max || '';
  sortBy.value = route.query.sort || '';
};

onMounted(() => {
  courseStore.fetchCategories();
  courseStore.fetchLevels();
  initFromUrl();
  fetchSearchResults();
});

watch(() => route.query.q, (newQuery) => {
  if (newQuery !== undefined) {
    currentPage.value = 1;
    initFromUrl();
    fetchSearchResults();
  }
});
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

.pagination-btn {
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 0.5rem;
  font-weight: 500;
  color: #374151;
  transition: all 0.2s ease;
}

.pagination-btn:hover:not(:disabled) {
  border-color: #a855f7;
  color: #a855f7;
}

.animate-fadeIn {
  animation: fadeIn 0.5s ease forwards;
  opacity: 0;
}

@keyframes fadeIn {
  to { opacity: 1; }
}
</style>
