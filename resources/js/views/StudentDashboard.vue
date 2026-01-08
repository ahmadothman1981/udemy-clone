<template>
  <div class="min-h-screen bg-gray-50">
    <Navbar />
    
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-500 text-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
          <div>
            <h1 class="text-3xl font-bold mb-2">{{ $t('common.welcome') }}, {{ userName }}! 👋</h1>
            <p class="text-purple-100">Continue your learning journey</p>
          </div>
          <router-link to="/" class="browse-btn">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 rtl:mirror" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            {{ $t('student_dashboard.browse_courses') }}
          </router-link>
        </div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Stats Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div v-for="stat in statsCards" :key="stat.title" class="stat-card">
          <div class="stat-icon" :class="stat.bgClass">
            <component :is="stat.icon" class="w-6 h-6 text-white" />
          </div>
          <div class="flex-1">
            <p class="text-sm text-gray-500 mb-1">{{ stat.title }}</p>
            <p class="text-2xl font-bold text-gray-900">{{ stat.value }}</p>
          </div>
        </div>
      </div>

      <div class="grid lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Continue Learning Section -->
          <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-100 flex justify-between items-center">
              <h2 class="text-xl font-bold text-gray-900">{{ $t('student_dashboard.continue_learning') }}</h2>
              <router-link to="/my-courses" class="text-purple-600 hover:text-purple-700 text-sm font-semibold flex items-center gap-1">
                {{ $t('common.view_all') }} <span class="rtl:rotate-180">→</span>
              </router-link>
            </div>

            <!-- Loading State -->
            <div v-if="loading" class="p-8 text-center">
              <div class="loading-spinner mx-auto"></div>
            </div>

            <!-- Empty State -->
            <div v-else-if="enrolledCourses.length === 0" class="p-12 text-center">
              <div class="w-20 h-20 mx-auto mb-4 bg-purple-100 rounded-full flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
              </div>
              <h3 class="text-lg font-bold text-gray-900 mb-2">Start Your Learning Journey</h3>
              <p class="text-gray-500 mb-6">{{ $t('student_dashboard.no_courses') }}</p>
              <router-link to="/" class="btn-primary">
                {{ $t('student_dashboard.browse_courses') }}
              </router-link>
            </div>

            <!-- Course List -->
            <div v-else class="divide-y divide-gray-100">
              <div 
                v-for="course in enrolledCourses.slice(0, 3)" 
                :key="course.id" 
                class="p-4 hover:bg-gray-50 transition-colors cursor-pointer"
                @click="continueCourse(course)"
              >
                <div class="flex gap-4">
                  <div class="w-32 h-20 rounded-lg overflow-hidden bg-gray-200 flex-shrink-0">
                    <img 
                      :src="course.thumbnail || `https://placehold.co/160x100/a855f7/ffffff?text=${encodeURIComponent(course.title?.substring(0, 10) || 'Course')}`" 
                      :alt="course.title"
                      class="w-full h-full object-cover"
                    >
                  </div>
                  <div class="flex-1 min-w-0">
                    <h3 class="font-semibold text-gray-900 truncate mb-1">{{ course.title }}</h3>
                    <p class="text-sm text-gray-500 mb-2">{{ course.instructor?.name }}</p>
                    
                    <!-- Progress Bar -->
                    <div class="flex items-center gap-3">
                      <div class="flex-1 h-2 bg-gray-200 rounded-full overflow-hidden">
                        <div 
                          class="h-full bg-gradient-to-r from-purple-500 to-pink-500 rounded-full transition-all duration-500"
                          :style="{ width: `${course.progress || 0}%` }"
                        ></div>
                      </div>
                      <span class="text-sm font-semibold text-gray-600">{{ course.progress || 0 }}%</span>
                    </div>
                  </div>
                  <button class="play-btn flex-shrink-0 rtl:rotate-180">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M8 5v14l11-7z"/>
                    </svg>
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Recently Viewed -->
          <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-100">
              <h2 class="text-xl font-bold text-gray-900">Recommended For You</h2>
            </div>
            <div class="p-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div 
                v-for="course in recommendedCourses" 
                :key="course.id" 
                class="recommended-card"
                @click="$router.push(`/course/${course.slug || course.id}`)"
              >
                <div class="aspect-video rounded-lg overflow-hidden bg-gray-200 mb-3">
                  <img 
                    :src="course.thumbnail || `https://placehold.co/300x170/6366f1/ffffff?text=${encodeURIComponent(course.title?.substring(0, 15) || 'Course')}`" 
                    :alt="course.title"
                    class="w-full h-full object-cover"
                  >
                </div>
                <h4 class="font-semibold text-gray-900 line-clamp-2 text-sm mb-1">{{ course.title }}</h4>
                <p class="text-xs text-gray-500 mb-2">{{ course.instructor?.name }}</p>
                <div class="flex items-center gap-2">
                  <span class="text-sm font-bold text-gray-900">${{ course.price }}</span>
                  <div class="flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-amber-400" viewBox="0 0 20 20" fill="currentColor">
                      <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                    <span class="text-xs text-gray-500">{{ (course.rating_avg || 4.5).toFixed(1) }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <!-- Learning Streak -->
          <div class="bg-gradient-to-br from-orange-500 to-pink-600 rounded-xl p-6 text-white">
            <div class="flex items-center gap-3 mb-4">
              <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z" />
                </svg>
              </div>
              <div>
                <p class="text-sm text-white/80">{{ $t('student_dashboard.learning_streak') }}</p>
                <p class="text-2xl font-bold">{{ learningStreak }} days 🔥</p>
              </div>
            </div>
            <p class="text-sm text-white/80">Keep learning to maintain your streak!</p>
          </div>

          <!-- Achievements -->
          <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-4 border-b border-gray-100">
              <h3 class="font-bold text-gray-900">{{ $t('student_dashboard.tabs.achievements') }}</h3>
            </div>
            <div class="p-4 grid grid-cols-3 gap-3">
              <div v-for="achievement in achievements" :key="achievement.id" class="achievement" :class="{ 'locked': !achievement.unlocked }">
                <div class="text-2xl mb-1">{{ achievement.icon }}</div>
                <p class="text-xs text-center">{{ achievement.name }}</p>
              </div>
            </div>
          </div>

          <!-- Quick Actions -->
          <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-4 border-b border-gray-100">
              <h3 class="font-bold text-gray-900">{{ $t('student_dashboard.quick_actions') }}</h3>
            </div>
            <div class="p-2">
              <router-link to="/wishlist" class="quick-action-btn">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-pink-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                </svg>
                {{ $t('nav.wishlist') }}
              </router-link>
              <router-link to="/cart" class="quick-action-btn">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                {{ $t('nav.cart') }}
              </router-link>
              
              <router-link to="/account-settings" class="quick-action-btn">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                {{ $t('nav.settings') }}
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, h } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import { useCourseStore } from '../stores/course';
import Navbar from '../components/Navbar.vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const router = useRouter();
const authStore = useAuthStore();
const courseStore = useCourseStore();

// State
const loading = ref(true);
const enrolledCourses = ref([]);
const learningStreak = ref(7);
const statsData = ref({
  enrolled: 0,
  hours: 0,
  completed: 0,
  certificates: 0
});

const achievements = ref([
  { id: 1, name: 'First Course', icon: '🎓', unlocked: false },
  { id: 2, name: '7 Day Streak', icon: '🔥', unlocked: false },
  { id: 3, name: 'Fast Learner', icon: '⚡', unlocked: false },
  { id: 4, name: 'Master', icon: '👑', unlocked: false },
]);

// Icon Components
const CoursesIcon = () => h('svg', { xmlns: 'http://www.w3.org/2000/svg', class: 'w-6 h-6', fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor' }, [
  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253' })
]);

const ClockIcon = () => h('svg', { xmlns: 'http://www.w3.org/2000/svg', class: 'w-6 h-6', fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor' }, [
  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z' })
]);

const TrophyIcon = () => h('svg', { xmlns: 'http://www.w3.org/2000/svg', class: 'w-6 h-6', fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor' }, [
  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z' })
]);

const CheckIcon = () => h('svg', { xmlns: 'http://www.w3.org/2000/svg', class: 'w-6 h-6', fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor' }, [
  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' })
]);

// Computed
const userName = computed(() => {
  return authStore.user?.name?.split(' ')[0] || 'Learner';
});

const statsCards = computed(() => [
  {
    title: t('student_dashboard.stats.enrolled_courses'),
    value: statsData.value.enrolled,
    icon: CoursesIcon,
    bgClass: 'bg-gradient-to-br from-blue-500 to-indigo-600',
  },
  {
    title: t('student_dashboard.stats.hours_learned'),
    value: statsData.value.hours,
    icon: ClockIcon,
    bgClass: 'bg-gradient-to-br from-purple-500 to-pink-600',
  },
  {
    title: t('student_dashboard.stats.certificates'),
    value: statsData.value.certificates,
    icon: TrophyIcon,
    bgClass: 'bg-gradient-to-br from-amber-500 to-orange-600',
  },
  {
    title: t('student_dashboard.stats.completed'),
    value: statsData.value.completed,
    icon: CheckIcon,
    bgClass: 'bg-gradient-to-br from-green-500 to-emerald-600',
  },
]);

const recommendedCourses = computed(() => {
  return courseStore.courses.slice(0, 4);
});

// Methods
const continueCourse = (course) => {
  router.push(`/learn/course/${course.slug || course.id}`);
};

// Load data - Updated for dynamic stats
onMounted(async () => {
  try {
    loading.value = true;
    
    // Fetch dashboard stats
    const statsResponse = await axios.get('/api/student/dashboard-stats');
    const stats = statsResponse.data;
    
    // Update simple stats
    learningStreak.value = stats.learning_streak;
    
    // Update achievements only if they are returned, otherwise keep defaults or merge
    if (stats.achievements) {
        achievements.value = stats.achievements;
    }
    
    // Update stats cards keys
    statsData.value = {
      enrolled: stats.enrolled_courses_count,
      hours: stats.hours_learned,
      completed: stats.completed_courses_count,
      certificates: stats.certificates_count
    };

    // Fetch enrolled courses (now includes progress from backend)
    const coursesResponse = await axios.get('/api/my-courses');
    // Handle paginated response: response.data.data
    const coursesData = coursesResponse.data.data || coursesResponse.data; // graceful fallback if structure changes
    
    if (Array.isArray(coursesData)) {
        enrolledCourses.value = coursesData.map(course => ({
          ...course,
          progress: course.progress || 0,
        }));
    } else {
        console.error('Unexpected courses response format', coursesResponse.data);
        enrolledCourses.value = [];
    }
    
    // Fetch recommended courses
    if (courseStore.courses.length === 0) {
      await courseStore.fetchCourses();
    }
  } catch (e) {
    console.error('Error loading student dashboard', e);
  } finally {
    loading.value = false;
  }
});
</script>

<style scoped>
.browse-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.25rem;
  background: white;
  color: #7c3aed;
  font-weight: 600;
  border-radius: 0.75rem;
  transition: all 0.3s ease;
}

.browse-btn:hover {
  background: #f5f3ff;
  transform: translateY(-2px);
}

.stat-card {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1.25rem;
  background: white;
  border-radius: 1rem;
  border: 1px solid #e5e7eb;
  transition: all 0.3s ease;
}

.stat-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
}

.stat-icon {
  width: 3rem;
  height: 3rem;
  border-radius: 0.75rem;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.loading-spinner {
  width: 40px;
  height: 40px;
  border: 3px solid #e5e7eb;
  border-top-color: #a855f7;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.btn-primary {
  display: inline-block;
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

.play-btn {
  width: 40px;
  height: 40px;
  background: linear-gradient(135deg, #a855f7, #ec4899);
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
  align-self: center;
}

.play-btn:hover {
  transform: scale(1.1);
  box-shadow: 0 5px 20px rgba(168, 85, 247, 0.4);
}

.recommended-card {
  padding: 0.75rem;
  border-radius: 0.75rem;
  cursor: pointer;
  transition: all 0.3s ease;
}

.recommended-card:hover {
  background: #f9fafb;
  transform: translateY(-4px);
}

.achievement {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 0.75rem;
  border-radius: 0.75rem;
  background: #f9fafb;
  transition: all 0.3s ease;
}

.achievement:hover {
  background: #f3e8ff;
}

.achievement.locked {
  opacity: 0.4;
  filter: grayscale(1);
}

.quick-action-btn {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1rem;
  width: 100%;
  color: #374151;
  border-radius: 0.5rem;
  transition: all 0.2s ease;
}

.quick-action-btn:hover {
  background: #f5f3ff;
  color: #7c3aed;
}
</style>
