<template>
  <div class="min-h-screen bg-gray-50">
    <Navbar />
    
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-purple-700 via-purple-600 to-indigo-600 text-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
          <div>
            <h1 class="text-3xl font-bold mb-2">{{ $t('instructor_dashboard.title') }}</h1>
            <p class="text-purple-100">Welcome back! Here's what's happening with your courses.</p>
          </div>
          <button @click="showCreateCourse = true" class="create-course-btn">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 rtl:ms-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            {{ $t('instructor_dashboard.sidebar.create_course') }}
          </button>
        </div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="flex gap-8">
        <!-- Sidebar -->
        <aside class="w-64 flex-shrink-0 hidden lg:block">
          <nav class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-4 border-b border-gray-100">
              <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">{{ $t('common.actions') }}</h3>
            </div>
            <ul class="py-2">
              <li v-for="item in menuItems" :key="item.name">
                <a 
                  href="#" 
                  @click.prevent="activeTab = item.id"
                  class="sidebar-item"
                  :class="{ 'active': activeTab === item.id }"
                >
                  <component :is="item.icon" class="w-5 h-5 rtl:ms-2" />
                  {{ item.label }}
                </a>
              </li>
            </ul>
          </nav>
          
          <!-- Quick Stats Widget -->
          <div class="mt-6 bg-gradient-to-br from-purple-600 to-indigo-700 rounded-xl p-5 text-white">
            <h4 class="font-semibold mb-3">This Month</h4>
            <div class="space-y-3">
              <div class="flex justify-between items-center">
                <span class="text-purple-200 text-sm">New Students</span>
                <span class="font-bold">+{{ monthlyStats.newStudents }}</span>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-purple-200 text-sm">Revenue</span>
                <span class="font-bold">${{ monthlyStats.revenue }}</span>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-purple-200 text-sm">Reviews</span>
                <span class="font-bold">+{{ monthlyStats.reviews }}</span>
              </div>
            </div>
          </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 min-w-0">
          <!-- Stats Cards -->
          <div v-if="['dashboard', 'performance'].includes(activeTab)" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div v-for="stat in statsCards" :key="stat.title" class="stat-card group">
              <div class="stat-icon" :class="stat.bgClass">
                <component :is="stat.icon" class="w-6 h-6 text-white" />
              </div>
              <div class="flex-1">
                <p class="text-sm text-gray-500 mb-1">{{ stat.title }}</p>
                <p class="text-2xl font-bold text-gray-900">{{ stat.value }}</p>
                <p v-if="stat.change" class="text-xs mt-1" :class="stat.changePositive ? 'text-green-600' : 'text-red-600'">
                  {{ stat.changePositive ? '↑' : '↓' }} {{ stat.change }} from last month
                </p>
              </div>
            </div>
          </div>

          <!-- Courses Section -->
          <div v-if="activeTab === 'courses'" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
              <div>
                <h2 class="text-xl font-bold text-gray-900">{{ $t('instructor_dashboard.your_courses') }}</h2>
                <p class="text-sm text-gray-500">Manage and monitor your course performance</p>
              </div>
              <div class="flex gap-2">
                <select v-model="courseFilter" class="filter-select">
                  <option value="all">All Courses</option>
                  <option value="published">Published</option>
                  <option value="draft">Drafts</option>
                </select>
              </div>
            </div>

            <!-- Loading State -->
            <div v-if="loading" class="p-12 text-center">
              <div class="loading-spinner mx-auto"></div>
              <p class="text-gray-500 mt-4">Loading courses...</p>
            </div>

            <!-- Empty State -->
            <div v-else-if="filteredCourses.length === 0" class="p-12 text-center">
              <div class="w-20 h-20 mx-auto mb-4 bg-purple-100 rounded-full flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
              </div>
              <h3 class="text-lg font-bold text-gray-900 mb-2">No courses yet</h3>
              <p class="text-gray-500 mb-6">Start creating your first course and share your knowledge with the world.</p>
              <button @click="showCreateCourse = true" class="btn-primary">
                {{ $t('instructor_dashboard.sidebar.create_course') }}
              </button>
            </div>

            <!-- Courses Table -->
            <div v-else class="overflow-x-auto">
              <table class="w-full">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-4 text-start text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ $t('instructor_dashboard.course_table.course') }}</th>
                    <th class="px-6 py-4 text-start text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ $t('instructor_dashboard.course_table.students') }}</th>
                    <th class="px-6 py-4 text-start text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ $t('instructor_dashboard.course_table.rating') }}</th>
                    <th class="px-6 py-4 text-start text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ $t('instructor_dashboard.course_table.revenue') }}</th>
                    <th class="px-6 py-4 text-start text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ $t('instructor_dashboard.course_table.status') }}</th>
                    <th class="px-6 py-4 text-end text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ $t('common.actions') }}</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                  <tr v-for="course in filteredCourses" :key="course.id" class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4">
                      <div class="flex items-center gap-4">
                        <div class="w-16 h-10 rounded-lg overflow-hidden bg-gray-200 flex-shrink-0">
                          <img 
                            :src="course.thumbnail || `https://placehold.co/160x100/a855f7/ffffff?text=${encodeURIComponent(course.title?.substring(0, 10) || 'Course')}`" 
                            :alt="course.title"
                            class="w-full h-full object-cover"
                          >
                        </div>
                        <div class="min-w-0">
                          <p class="font-semibold text-gray-900 truncate max-w-xs">{{ course.title }}</p>
                          <p class="text-xs text-gray-500">Last updated {{ formatDate(course.updated_at) }}</p>
                        </div>
                      </div>
                    </td>
                    <td class="px-6 py-4">
                      <div class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <span class="text-gray-900 font-medium">{{ course.enrollments_count || 0 }}</span>
                      </div>
                    </td>
                    <td class="px-6 py-4">
                      <div class="flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-amber-400" viewBox="0 0 20 20" fill="currentColor">
                          <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                        <span class="text-gray-900 font-medium">{{ (course.rating_avg || 0).toFixed(1) }}</span>
                        <span class="text-gray-400 text-sm">({{ course.reviews_count || 0 }})</span>
                      </div>
                    </td>
                    <td class="px-6 py-4">
                      <span class="text-gray-900 font-medium">${{ course.revenue || 0 }}</span>
                    </td>
                    <td class="px-6 py-4">
                      <span 
                        class="status-badge"
                        :class="course.published ? 'status-published' : 'status-draft'"
                      >
                        {{ course.published ? 'Published' : 'Draft' }}
                      </span>
                    </td>
                    <td class="px-6 py-4">
                      <div class="flex justify-end gap-2">
                        <button @click="editCourse(course)" class="action-btn" :title="$t('common.edit')">
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                          </svg>
                        </button>
                        <button @click="viewAnalytics(course)" class="action-btn" title="View Analytics">
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                          </svg>
                        </button>
                        <button @click="togglePublish(course)" class="action-btn" :title="course.published ? 'Unpublish' : 'Publish'">
                          <svg v-if="course.published" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                          </svg>
                          <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                          </svg>
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Recent Activity / Reviews Section -->
          <div v-if="['dashboard', 'communication'].includes(activeTab)" class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-8">
            <!-- Recent Reviews -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
              <div class="p-6 border-b border-gray-100">
                <h3 class="font-bold text-gray-900">{{ $t('instructor_dashboard.recent_reviews') }}</h3>
              </div>
              <div class="divide-y divide-gray-100">
                <div v-for="review in recentReviews" :key="review.id" class="p-4 hover:bg-gray-50 transition-colors">
                  <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center flex-shrink-0">
                      <span class="text-sm font-bold text-purple-600">{{ review.user?.name?.[0] || 'U' }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                      <div class="flex items-center gap-2 mb-1">
                        <span class="font-semibold text-gray-900 text-sm">{{ review.user?.name || 'Student' }}</span>
                        <div class="flex">
                          <svg v-for="i in 5" :key="i" xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" :class="i <= review.rating ? 'text-amber-400' : 'text-gray-300'" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                          </svg>
                        </div>
                      </div>
                      <p class="text-sm text-gray-600 line-clamp-2">{{ review.comment }}</p>
                      <p class="text-xs text-gray-400 mt-1">on {{ review.course?.title }}</p>
                    </div>
                  </div>
                </div>
                <div v-if="recentReviews.length === 0" class="p-8 text-center text-gray-500">
                  No reviews yet
                </div>
              </div>
            </div>

            <!-- Recent Questions -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
              <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                <h3 class="font-bold text-gray-900">{{ $t('instructor_dashboard.student_questions') }}</h3>
                <span class="text-xs bg-red-100 text-red-600 px-2 py-1 rounded-full font-semibold">{{ unansweredCount }} unanswered</span>
              </div>
              <div class="divide-y divide-gray-100">
                <div v-for="question in recentQuestions" :key="question.id" class="p-4 hover:bg-gray-50 transition-colors cursor-pointer">
                  <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                      <span class="text-sm font-bold text-blue-600">{{ question.user?.name?.[0] || 'S' }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                      <p class="font-semibold text-gray-900 text-sm line-clamp-1">{{ question.title }}</p>
                      <p class="text-sm text-gray-600 line-clamp-1">{{ question.body }}</p>
                      <p class="text-xs text-gray-400 mt-1">{{ question.course?.title }} • {{ formatDate(question.created_at) }}</p>
                    </div>
                    <span v-if="!question.answered" class="w-2 h-2 bg-red-500 rounded-full flex-shrink-0 mt-2"></span>
                  </div>
                </div>
                <div v-if="recentQuestions.length === 0" class="p-8 text-center text-gray-500">
                  No questions yet
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Create Course Modal -->
    <div v-if="showCreateCourse" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showCreateCourse = false"></div>
      <div class="relative bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6 animate-modalIn">
        <button @click="showCreateCourse = false" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
        <h2 class="text-2xl font-bold text-gray-900 mb-2">Create New Course</h2>
        <p class="text-gray-500 mb-6">Start by giving your course a title. You can change it later.</p>
        
        <form @submit.prevent="createCourse">
          <div class="mb-4">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Course Title</label>
            <input 
              v-model="newCourseTitle" 
              type="text" 
              placeholder="e.g., Complete Python Bootcamp"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
              required
            >
          </div>
          <div class="mb-6">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Category</label>
            <select v-model="newCourseCategory" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all">
              <option value="">Select a category</option>
              <option value="development">Development</option>
              <option value="business">Business</option>
              <option value="design">Design</option>
              <option value="marketing">Marketing</option>
            </select>
          </div>
          <div class="flex gap-3">
            <button type="button" @click="showCreateCourse = false" class="flex-1 px-4 py-3 border border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-50 transition-colors">
              Cancel
            </button>
            <button type="submit" class="flex-1 px-4 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-lg font-semibold hover:from-purple-700 hover:to-indigo-700 transition-all">
              Create Course
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, h } from 'vue';
import axios from 'axios';
import Navbar from '../components/Navbar.vue';
import { useI18n } from 'vue-i18n';

// State
const { t } = useI18n();
const loading = ref(true);
const stats = ref(null);
const courses = ref([]);
const recentReviews = ref([]);
const recentQuestions = ref([]);
const activeTab = ref('dashboard');
const courseFilter = ref('all');
const showCreateCourse = ref(false);
const newCourseTitle = ref('');
const newCourseCategory = ref('');

// Icon Components
const DashboardIcon = () => h('svg', { xmlns: 'http://www.w3.org/2000/svg', class: 'w-5 h-5', fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor' }, [
  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z' })
]);

const CoursesIcon = () => h('svg', { xmlns: 'http://www.w3.org/2000/svg', class: 'w-5 h-5', fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor' }, [
  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253' })
]);

const CommunicationIcon = () => h('svg', { xmlns: 'http://www.w3.org/2000/svg', class: 'w-5 h-5', fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor' }, [
  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z' })
]);

const PerformanceIcon = () => h('svg', { xmlns: 'http://www.w3.org/2000/svg', class: 'w-5 h-5', fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor' }, [
  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z' })
]);

const RevenueIcon = () => h('svg', { xmlns: 'http://www.w3.org/2000/svg', class: 'w-5 h-5', fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor' }, [
  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z' })
]);

const StudentsIcon = () => h('svg', { xmlns: 'http://www.w3.org/2000/svg', class: 'w-5 h-5', fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor' }, [
  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z' })
]);

const RatingIcon = () => h('svg', { xmlns: 'http://www.w3.org/2000/svg', class: 'w-5 h-5', fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor' }, [
  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z' })
]);

const CoursesCountIcon = () => h('svg', { xmlns: 'http://www.w3.org/2000/svg', class: 'w-5 h-5', fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor' }, [
  h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10' })
]);

// Menu items
const menuItems = computed(() => [
  { id: 'dashboard', label: t('instructor_dashboard.sidebar.dashboard'), icon: DashboardIcon },
  { id: 'courses', label: t('instructor_dashboard.sidebar.courses'), icon: CoursesIcon },
  { id: 'communication', label: t('instructor_dashboard.sidebar.communication'), icon: CommunicationIcon },
  { id: 'performance', label: t('instructor_dashboard.sidebar.performance'), icon: PerformanceIcon },
]);

// Computed
const monthlyStats = computed(() => ({
  newStudents: stats.value?.monthly_students || 23,
  revenue: stats.value?.monthly_revenue || '1,245',
  reviews: stats.value?.monthly_reviews || 12,
}));

const statsCards = computed(() => [
  {
    title: t('instructor_dashboard.stats.total_revenue'),
    value: `$${stats.value?.total_revenue || '0'}`,
    icon: RevenueIcon,
    bgClass: 'bg-gradient-to-br from-green-500 to-emerald-600',
    change: '12%',
    changePositive: true,
  },
  {
    title: t('instructor_dashboard.stats.total_students'),
    value: stats.value?.total_students || 0,
    icon: StudentsIcon,
    bgClass: 'bg-gradient-to-br from-blue-500 to-indigo-600',
    change: '8%',
    changePositive: true,
  },
  {
    title: t('instructor_dashboard.stats.average_rating'),
    value: `${stats.value?.average_rating || '0'} / 5.0`,
    icon: RatingIcon,
    bgClass: 'bg-gradient-to-br from-amber-500 to-orange-600',
    change: '0.2',
    changePositive: true,
  },
  {
    title: t('instructor_dashboard.sidebar.courses'),
    value: courses.value.filter(c => c.published).length,
    icon: CoursesCountIcon,
    bgClass: 'bg-gradient-to-br from-purple-500 to-pink-600',
  },
]);

const filteredCourses = computed(() => {
  if (courseFilter.value === 'published') return courses.value.filter(c => c.published);
  if (courseFilter.value === 'draft') return courses.value.filter(c => !c.published);
  return courses.value;
});

const unansweredCount = computed(() => stats.value?.unanswered_questions_count || 0);

// Methods
const formatDate = (date) => {
  if (!date) return 'N/A';
  return new Date(date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
};

const editCourse = (course) => {
  console.log('Edit course:', course.id);
};

const viewAnalytics = (course) => {
  console.log('View analytics:', course.id);
};

const togglePublish = async (course) => {
  try {
    await axios.patch(`/api/instructor/courses/${course.id}/publish`);
    course.published = !course.published;
  } catch (e) {
    console.error('Failed to toggle publish', e);
  }
};

const createCourse = async () => {
  try {
    const res = await axios.post('/api/instructor/courses', {
      title: newCourseTitle.value,
      category: newCourseCategory.value,
    });
    courses.value.unshift(res.data);
    showCreateCourse.value = false;
    newCourseTitle.value = '';
    newCourseCategory.value = '';
  } catch (e) {
    console.error('Failed to create course', e);
  }
};

// Load data
onMounted(async () => {
  try {
    const [statsRes, coursesRes] = await Promise.all([
      axios.get('/api/instructor/dashboard'),
      axios.get('/api/instructor/courses'),
    ]);
    stats.value = statsRes.data;
    courses.value = coursesRes.data;
    
    // Assign real data from backend
    recentReviews.value = statsRes.data.recent_reviews || [];
    recentQuestions.value = statsRes.data.recent_questions || [];
    
    // Fallback if empty for demo feeling (optional, maybe remove for production)
    // If we want real empty state, just leave as is.
  } catch (e) {
    console.error("Error loading instructor data", e);
  } finally {
    loading.value = false;
  }
});
</script>

<style scoped>
.create-course-btn {
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

.create-course-btn:hover {
  background: #f5f3ff;
  transform: translateY(-2px);
}

.sidebar-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1rem;
  color: #6b7280;
  transition: all 0.2s ease;
}

.sidebar-item:hover {
  color: #7c3aed;
  background: #f5f3ff;
}

.sidebar-item.active {
  color: #7c3aed;
  background: #f5f3ff;
  border-right: 3px solid #7c3aed;
  font-weight: 600;
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

.filter-select {
  padding: 0.5rem 1rem;
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 0.5rem;
  font-size: 0.875rem;
  color: #374151;
  cursor: pointer;
  transition: all 0.2s ease;
}

.filter-select:focus {
  outline: none;
  border-color: #a855f7;
  box-shadow: 0 0 0 3px rgba(168, 85, 247, 0.1);
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

.status-badge {
  padding: 0.25rem 0.75rem;
  font-size: 0.75rem;
  font-weight: 600;
  border-radius: 9999px;
}

.status-published {
  background: #d1fae5;
  color: #065f46;
}

.status-draft {
  background: #f3f4f6;
  color: #4b5563;
}

.action-btn {
  padding: 0.5rem;
  color: #6b7280;
  border-radius: 0.5rem;
  transition: all 0.2s ease;
}

.action-btn:hover {
  color: #7c3aed;
  background: #f5f3ff;
}

@keyframes modalIn {
  from {
    opacity: 0;
    transform: scale(0.95) translateY(10px);
  }
  to {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}

.animate-modalIn {
  animation: modalIn 0.2s ease-out;
}
</style>
