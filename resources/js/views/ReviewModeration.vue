<template>
  <div class="bg-slate-50 min-h-screen flex font-inter">
    <AdminSidebar @logout="handleLogout" />
    
    <main class="flex-1 flex flex-col min-w-0 overflow-hidden">
      <header class="bg-white border-b border-slate-200 h-16 flex items-center justify-between px-8 sticky top-0 z-30">
        <h1 class="text-xl font-bold text-slate-800">Review Moderation</h1>
      </header>

      <div class="flex-1 overflow-auto p-8">
        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-sm border p-4 mb-6 flex flex-wrap items-center gap-4">
          <select v-model="ratingFilter" @change="fetchReviews" class="px-4 py-2 border rounded-lg">
            <option value="">All Ratings</option>
            <option v-for="r in [5,4,3,2,1]" :key="r" :value="r">{{ r }} Stars</option>
          </select>
          <input v-model="courseSearch" @input="debouncedSearch" placeholder="Filter by course ID..." class="px-4 py-2 border rounded-lg w-48" />
        </div>

        <!-- Reviews List -->
        <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
          <div class="divide-y divide-slate-100">
            <div v-for="review in reviews" :key="review.id" class="p-6 hover:bg-slate-50">
              <div class="flex justify-between items-start">
                <div class="flex gap-4">
                  <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center text-white font-semibold">
                    {{ review.user?.name?.[0]?.toUpperCase() }}
                  </div>
                  <div>
                    <div class="font-medium text-slate-800">{{ review.user?.name }}</div>
                    <div class="text-sm text-slate-500 mb-2">on "{{ review.course?.title }}"</div>
                    <div class="flex items-center gap-1 mb-2">
                      <Star v-for="i in 5" :key="i" class="w-4 h-4" :class="i <= review.rating ? 'text-yellow-400 fill-yellow-400' : 'text-slate-300'" />
                    </div>
                    <p class="text-slate-600">{{ review.content }}</p>
                    <div class="text-xs text-slate-400 mt-2">{{ new Date(review.created_at).toLocaleDateString() }}</div>
                  </div>
                </div>
                <button @click="removeReview(review)" class="p-2 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg" title="Remove">
                  <Trash2 class="w-4 h-4" />
                </button>
              </div>
            </div>
            <div v-if="reviews.length === 0" class="p-12 text-center text-slate-400">No reviews found.</div>
          </div>
          <!-- Pagination -->
          <div class="p-4 border-t flex justify-between items-center">
            <span class="text-sm text-slate-500">{{ pagination.from }} - {{ pagination.to }} of {{ pagination.total }}</span>
            <div class="flex gap-2">
              <button @click="changePage(pagination.current_page - 1)" :disabled="pagination.current_page === 1" class="px-3 py-1 border rounded-lg disabled:opacity-50">Previous</button>
              <button @click="changePage(pagination.current_page + 1)" :disabled="pagination.current_page === pagination.last_page" class="px-3 py-1 border rounded-lg disabled:opacity-50">Next</button>
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
