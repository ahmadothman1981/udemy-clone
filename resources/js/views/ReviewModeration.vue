<template>
  <div class="bg-slate-50 dark:bg-slate-900 min-h-screen flex font-inter transition-colors duration-300">
    <AdminSidebar @logout="handleLogout" />
    
    <main class="flex-1 flex flex-col min-w-0 overflow-hidden">
      <header class="bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 h-16 flex items-center justify-between px-8 sticky top-0 z-30 transition-colors">
        <h1 class="text-xl font-bold text-slate-800 dark:text-white">Review Moderation</h1>
      </header>

      <div class="flex-1 overflow-auto p-8">
        <!-- Filters -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-4 mb-6 flex flex-wrap items-center gap-4 transition-colors">
          <select v-model="ratingFilter" @change="fetchReviews" class="px-4 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-800 dark:text-white transition-colors">
            <option value="">All Ratings</option>
            <option v-for="r in [5,4,3,2,1]" :key="r" :value="r">{{ r }} Stars</option>
          </select>
          <input v-model="courseSearch" @input="debouncedSearch" placeholder="Filter by course ID..." class="px-4 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-600 rounded-lg w-48 text-slate-800 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-purple-500 transition-colors" />
        </div>

        <!-- Reviews List -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden transition-colors">
          <div class="divide-y divide-slate-100 dark:divide-slate-700 transition-colors">
            <div v-for="review in reviews" :key="review.id" class="p-6 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
              <div class="flex justify-between items-start">
                <div class="flex gap-4">
                  <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center text-white font-semibold">
                    {{ review.user?.name?.[0]?.toUpperCase() }}
                  </div>
                  <div>
                    <div class="font-medium text-slate-800 dark:text-white transition-colors">{{ review.user?.name }}</div>
                    <div class="text-sm text-slate-500 dark:text-slate-400 mb-2 transition-colors">on "{{ review.course?.title }}"</div>
                    <div class="flex items-center gap-1 mb-2">
                      <Star v-for="i in 5" :key="i" class="w-4 h-4" :class="i <= review.rating ? 'text-yellow-400 fill-yellow-400' : 'text-slate-300 dark:text-slate-600'" />
                    </div>
                    <p class="text-slate-600 dark:text-slate-300 transition-colors">{{ review.content }}</p>
                    <div class="text-xs text-slate-400 dark:text-slate-500 mt-2 transition-colors">{{ new Date(review.created_at).toLocaleDateString() }}</div>
                  </div>
                </div>
                <button @click="removeReview(review)" class="p-2 text-red-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors" title="Remove">
                  <Trash2 class="w-4 h-4" />
                </button>
              </div>
            </div>
            <div v-if="reviews.length === 0" class="p-12 text-center text-slate-400 dark:text-slate-500 transition-colors">No reviews found.</div>
          </div>
          <!-- Pagination -->
          <div class="p-4 border-t border-slate-200 dark:border-slate-700 flex justify-between items-center transition-colors">
            <span class="text-sm text-slate-500 dark:text-slate-400">{{ pagination.from }} - {{ pagination.to }} of {{ pagination.total }}</span>
            <div class="flex gap-2">
              <button @click="changePage(pagination.current_page - 1)" :disabled="pagination.current_page === 1" class="px-3 py-1 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-600 dark:text-slate-300 disabled:opacity-50 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">Previous</button>
              <button @click="changePage(pagination.current_page + 1)" :disabled="pagination.current_page === pagination.last_page" class="px-3 py-1 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-600 dark:text-slate-300 disabled:opacity-50 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">Next</button>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import AdminSidebar from '../components/admin/AdminSidebar.vue';
import { Star, Trash2 } from 'lucide-vue-next';
import axios from 'axios';
import { confirmDelete, showSuccess, showError } from '../utils/sweetalert';
import debounce from 'lodash/debounce';

const router = useRouter();
const auth = useAuthStore();

const reviews = ref([]);
const ratingFilter = ref('');
const courseSearch = ref('');
const pagination = ref({});

const fetchReviews = async (page = 1) => {
  const params = new URLSearchParams({ page });
  if (ratingFilter.value) params.append('rating', ratingFilter.value);
  if (courseSearch.value) params.append('course_id', courseSearch.value);
  const res = await axios.get(`/api/admin/reviews?${params}`);
  reviews.value = res.data.data;
  pagination.value = res.data;
};

const debouncedSearch = debounce(() => fetchReviews(1), 300);
const changePage = (p) => fetchReviews(p);

const removeReview = async (review) => {
  const result = await confirmDelete('this review');
  if (!result.isConfirmed) return;
  await axios.delete(`/api/admin/reviews/${review.id}`);
  showSuccess('Review removed');
  fetchReviews();
};

const handleLogout = async () => { await auth.logout(); router.push('/login'); };

onMounted(() => fetchReviews());
</script>
