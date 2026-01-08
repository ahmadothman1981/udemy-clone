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
            <h2 class="text-3xl font-bold text-gray-900 mb-2">All Courses</h2>
            <p class="text-gray-600">Explore our extensive library of courses</p>
          </div>
          
          <!-- Filters -->
          <div class="flex flex-wrap gap-3">
            <select 
              v-model="selectedCategory" 
              @change="applyFilters"
              class="filter-select"
            >
              <option value="">All Categories</option>
              <option v-for="cat in courseStore.categories" :key="cat.id" :value="cat.slug">
                {{ cat.name }}
              </option>
            </select>
            
            <select 
              v-model="sortBy" 
              @change="applyFilters"
              class="filter-select"
            >
              <option value="">Sort By</option>
              <option value="popular">Most Popular</option>
              <option value="newest">Newest</option>
              <option value="rating">Highest Rated</option>
              <option value="price_low">Price: Low to High</option>
              <option value="price_high">Price: High to Low</option>
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
          <h3 class="text-xl font-bold text-gray-900 mb-2">No courses found</h3>
          <p class="text-gray-500 mb-6">Try adjusting your search or filter criteria</p>
          <button @click="clearFilters" class="btn-primary">
            Clear filters
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
        <h2 class="text-3xl font-bold mb-4">Stay Updated</h2>
        <p class="text-purple-100 mb-8 max-w-2xl mx-auto">
          Subscribe to our newsletter and be the first to know about new courses, exclusive offers, and learning tips.
        </p>
        <form @submit.prevent class="flex flex-col sm:flex-row gap-4 max-w-lg mx-auto">
          <input 
            type="email" 
            placeholder="Enter your email"
            class="flex-1 px-6 py-3 rounded-full text-gray-900 focus:outline-none focus:ring-4 focus:ring-purple-300"
          >
          <button type="submit" class="px-8 py-3 bg-white text-purple-600 font-bold rounded-full hover:bg-gray-100 transition-colors">
            Subscribe
          </button>
        </form>
      </div>
    </section>
    
    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-400 py-12">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 mb-8">
          <div>
            <h4 class="text-white font-semibold mb-4">Company</h4>
            <ul class="space-y-2 text-sm">
              <li><a href="#" class="hover:text-white transition-colors">About us</a></li>
              <li><a href="#" class="hover:text-white transition-colors">Careers</a></li>
              <li><a href="#" class="hover:text-white transition-colors">Blog</a></li>
            </ul>
          </div>
          <div>
            <h4 class="text-white font-semibold mb-4">Community</h4>
            <ul class="space-y-2 text-sm">
              <li><a href="#" class="hover:text-white transition-colors">Become an instructor</a></li>
              <li><a href="#" class="hover:text-white transition-colors">Affiliate program</a></li>
              <li><a href="#" class="hover:text-white transition-colors">Partners</a></li>
            </ul>
          </div>
          <div>
            <h4 class="text-white font-semibold mb-4">Support</h4>
            <ul class="space-y-2 text-sm">
              <li><a href="#" class="hover:text-white transition-colors">Help center</a></li>
              <li><a href="#" class="hover:text-white transition-colors">Contact us</a></li>
              <li><a href="#" class="hover:text-white transition-colors">FAQ</a></li>
            </ul>
          </div>
          <div>
            <h4 class="text-white font-semibold mb-4">Legal</h4>
            <ul class="space-y-2 text-sm">
              <li><a href="#" class="hover:text-white transition-colors">Terms of use</a></li>
              <li><a href="#" class="hover:text-white transition-colors">Privacy policy</a></li>
              <li><a href="#" class="hover:text-white transition-colors">Cookie policy</a></li>
            </ul>
          </div>
        </div>
        <div class="border-t border-gray-800 pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
          <div class="text-2xl font-bold text-white">UdemyClone</div>
          <p class="text-sm">© 2026 UdemyClone. All rights reserved.</p>
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

const courseStore = useCourseStore();
const route = useRoute();
const router = useRouter();

const selectedCategory = ref('');
const sortBy = ref('');

const loadCourses = () => {
  courseStore.fetchCourses(route.query);
};

const applyFilters = () => {
  const query = { ...route.query };
  if (selectedCategory.value) {
    query.category = selectedCategory.value;
  } else {
    delete query.category;
  }
  if (sortBy.value) {
    query.sort = sortBy.value;
  } else {
    delete query.sort;
  }
  router.push({ path: '/', query });
};

const clearFilters = () => {
  selectedCategory.value = '';
  sortBy.value = '';
  router.push({ path: '/' });
};

onMounted(() => {
  loadCourses();
  if (route.query.category) {
    selectedCategory.value = route.query.category;
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
