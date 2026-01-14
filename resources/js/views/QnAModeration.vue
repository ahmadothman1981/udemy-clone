<template>
  <div class="bg-slate-50 dark:bg-slate-900 min-h-screen flex font-inter transition-colors duration-300">
    <AdminSidebar @logout="handleLogout" />
    
    <main class="flex-1 flex flex-col min-w-0 overflow-hidden">
      <header class="bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 h-16 flex items-center justify-between px-8 sticky top-0 z-30 transition-colors">
        <h1 class="text-xl font-bold text-slate-800 dark:text-white">Q&A Moderation</h1>
      </header>

      <div class="flex-1 overflow-auto p-8">
        <!-- Filters -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-4 mb-6 flex flex-wrap items-center gap-4 transition-colors">
          <select v-model="answeredFilter" @change="fetchQuestions" class="px-4 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-600 rounded-lg text-slate-800 dark:text-white transition-colors">
            <option value="">All Questions</option>
            <option value="true">Answered</option>
            <option value="false">Unanswered</option>
          </select>
          <input v-model="courseSearch" @input="debouncedSearch" placeholder="Filter by course ID..." class="px-4 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-600 rounded-lg w-48 text-slate-800 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-purple-500 transition-colors" />
        </div>

        <!-- Questions List -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden transition-colors">
          <div class="divide-y divide-slate-100 dark:divide-slate-700 transition-colors">
            <div v-for="question in questions" :key="question.id" class="p-6">
              <div class="flex justify-between items-start">
                <div class="flex-1">
                  <div class="flex items-center gap-2 mb-2">
                    <div class="w-8 h-8 rounded-full bg-purple-100 dark:bg-purple-900/40 text-purple-600 dark:text-purple-400 flex items-center justify-center font-semibold text-sm transition-colors">
                      {{ question.user?.name?.[0]?.toUpperCase() }}
                    </div>
                    <span class="font-medium text-slate-800 dark:text-white transition-colors">{{ question.user?.name }}</span>
                    <span class="text-slate-400 dark:text-slate-500 transition-colors">•</span>
                    <span class="text-sm text-slate-500 dark:text-slate-400 transition-colors">{{ question.course?.title }}</span>
                  </div>
                  <p class="text-slate-700 dark:text-slate-300 mb-3 transition-colors">{{ question.question }}</p>
                  <div class="text-xs text-slate-400 dark:text-slate-500 transition-colors">{{ new Date(question.created_at).toLocaleDateString() }}</div>
                  
                  <!-- Answers -->
                  <div v-if="question.answers?.length" class="mt-4 ml-8 space-y-3">
                    <div v-for="answer in question.answers" :key="answer.id" class="bg-slate-50 dark:bg-slate-700/50 rounded-lg p-4 relative transition-colors">
                      <div class="flex items-center gap-2 mb-2">
                        <span class="text-sm font-medium text-slate-700 dark:text-slate-300 transition-colors">{{ answer.user?.name || 'Instructor' }}</span>
                      </div>
                      <p class="text-sm text-slate-600 dark:text-slate-400 transition-colors">{{ answer.answer }}</p>
                      <button @click="removeAnswer(answer)" class="absolute top-2 right-2 p-1 text-red-400 hover:text-red-600 dark:hover:text-red-400 dark:text-red-400/70 rounded transition-colors" title="Remove">
                        <Trash2 class="w-3 h-3" />
                      </button>
                    </div>
                  </div>
                </div>
                <button @click="removeQuestion(question)" class="p-2 text-red-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors" title="Remove">
                  <Trash2 class="w-4 h-4" />
                </button>
              </div>
            </div>
            <div v-if="questions.length === 0" class="p-12 text-center text-slate-400 dark:text-slate-500 transition-colors">No questions found.</div>
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
import { Trash2 } from 'lucide-vue-next';
import axios from 'axios';
import { confirmDelete, showSuccess } from '../utils/sweetalert';
import debounce from 'lodash/debounce';

const router = useRouter();
const auth = useAuthStore();

const questions = ref([]);
const answeredFilter = ref('');
const courseSearch = ref('');
const pagination = ref({});

const fetchQuestions = async (page = 1) => {
  const params = new URLSearchParams({ page });
  if (answeredFilter.value) params.append('answered', answeredFilter.value);
  if (courseSearch.value) params.append('course_id', courseSearch.value);
  const res = await axios.get(`/api/admin/questions?${params}`);
  questions.value = res.data.data;
  pagination.value = res.data;
};

const debouncedSearch = debounce(() => fetchQuestions(1), 300);
const changePage = (p) => fetchQuestions(p);

const removeQuestion = async (question) => {
  const result = await confirmDelete('this question and all its answers');
  if (!result.isConfirmed) return;
  await axios.delete(`/api/admin/questions/${question.id}`);
  showSuccess('Question removed');
  fetchQuestions();
};

const removeAnswer = async (answer) => {
  const result = await confirmDelete('this answer');
  if (!result.isConfirmed) return;
  await axios.delete(`/api/admin/answers/${answer.id}`);
  showSuccess('Answer removed');
  fetchQuestions();
};

const handleLogout = async () => { await auth.logout(); router.push('/login'); };

onMounted(() => fetchQuestions());
</script>
