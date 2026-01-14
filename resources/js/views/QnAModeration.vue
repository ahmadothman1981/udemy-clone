<template>
  <div class="bg-slate-50 min-h-screen flex font-inter">
    <AdminSidebar @logout="handleLogout" />
    
    <main class="flex-1 flex flex-col min-w-0 overflow-hidden">
      <header class="bg-white border-b border-slate-200 h-16 flex items-center justify-between px-8 sticky top-0 z-30">
        <h1 class="text-xl font-bold text-slate-800">Q&A Moderation</h1>
      </header>

      <div class="flex-1 overflow-auto p-8">
        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-sm border p-4 mb-6 flex flex-wrap items-center gap-4">
          <select v-model="answeredFilter" @change="fetchQuestions" class="px-4 py-2 border rounded-lg">
            <option value="">All Questions</option>
            <option value="true">Answered</option>
            <option value="false">Unanswered</option>
          </select>
          <input v-model="courseSearch" @input="debouncedSearch" placeholder="Filter by course ID..." class="px-4 py-2 border rounded-lg w-48" />
        </div>

        <!-- Questions List -->
        <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
          <div class="divide-y divide-slate-100">
            <div v-for="question in questions" :key="question.id" class="p-6">
              <div class="flex justify-between items-start">
                <div class="flex-1">
                  <div class="flex items-center gap-2 mb-2">
                    <div class="w-8 h-8 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center font-semibold text-sm">
                      {{ question.user?.name?.[0]?.toUpperCase() }}
                    </div>
                    <span class="font-medium text-slate-800">{{ question.user?.name }}</span>
                    <span class="text-slate-400">•</span>
                    <span class="text-sm text-slate-500">{{ question.course?.title }}</span>
                  </div>
                  <p class="text-slate-700 mb-3">{{ question.question }}</p>
                  <div class="text-xs text-slate-400">{{ new Date(question.created_at).toLocaleDateString() }}</div>
                  
                  <!-- Answers -->
                  <div v-if="question.answers?.length" class="mt-4 ml-8 space-y-3">
                    <div v-for="answer in question.answers" :key="answer.id" class="bg-slate-50 rounded-lg p-4 relative">
                      <div class="flex items-center gap-2 mb-2">
                        <span class="text-sm font-medium text-slate-700">{{ answer.user?.name || 'Instructor' }}</span>
                      </div>
                      <p class="text-sm text-slate-600">{{ answer.answer }}</p>
                      <button @click="removeAnswer(answer)" class="absolute top-2 right-2 p-1 text-red-400 hover:text-red-600 rounded" title="Remove">
                        <Trash2 class="w-3 h-3" />
                      </button>
                    </div>
                  </div>
                </div>
                <button @click="removeQuestion(question)" class="p-2 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg" title="Remove">
                  <Trash2 class="w-4 h-4" />
                </button>
              </div>
            </div>
            <div v-if="questions.length === 0" class="p-12 text-center text-slate-400">No questions found.</div>
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
