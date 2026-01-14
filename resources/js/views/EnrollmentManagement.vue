<template>
  <div class="bg-slate-50 min-h-screen flex font-inter">
    <AdminSidebar @logout="handleLogout" />
    
    <main class="flex-1 flex flex-col min-w-0 overflow-hidden">
      <header class="bg-white border-b border-slate-200 h-16 flex items-center justify-between px-8 sticky top-0 z-30">
        <h1 class="text-xl font-bold text-slate-800">Enrollment Management</h1>
      </header>

      <div class="flex-1 overflow-auto p-8">
        <!-- Add Enrollment Form -->
        <div class="bg-white rounded-xl shadow-sm border p-6 mb-6">
          <h2 class="text-lg font-semibold mb-4">Manual Enrollment</h2>
          <div class="flex flex-wrap gap-4 items-end">
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">User ID</label>
              <input v-model="newEnrollment.user_id" type="number" class="px-4 py-2 border rounded-lg w-40" placeholder="User ID" />
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Course ID</label>
              <input v-model="newEnrollment.course_id" type="number" class="px-4 py-2 border rounded-lg w-40" placeholder="Course ID" />
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Reason (optional)</label>
              <input v-model="newEnrollment.reason" class="px-4 py-2 border rounded-lg w-64" placeholder="e.g., Refund re-enrollment" />
            </div>
            <button @click="addEnrollment" class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">Add Enrollment</button>
          </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-sm border p-4 mb-6 flex flex-wrap items-center gap-4">
          <input v-model="userFilter" @input="debouncedSearch" placeholder="Filter by User ID..." class="px-4 py-2 border rounded-lg w-48" />
          <input v-model="courseFilter" @input="debouncedSearch" placeholder="Filter by Course ID..." class="px-4 py-2 border rounded-lg w-48" />
        </div>

        <!-- Enrollments Table -->
        <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full text-left">
              <thead class="bg-slate-50 text-slate-500 text-xs uppercase border-b">
                <tr>
                  <th class="px-6 py-4">Student</th>
                  <th class="px-6 py-4">Course</th>
                  <th class="px-6 py-4">Progress</th>
                  <th class="px-6 py-4">Enrolled At</th>
                  <th class="px-6 py-4 text-right">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <tr v-for="enrollment in enrollments" :key="enrollment.id" class="hover:bg-slate-50">
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <div class="w-8 h-8 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center font-semibold text-sm">
                        {{ enrollment.user?.name?.[0]?.toUpperCase() }}
                      </div>
                      <div>
                        <div class="font-medium text-slate-800">{{ enrollment.user?.name }}</div>
                        <div class="text-xs text-slate-500">{{ enrollment.user?.email }}</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 text-sm text-slate-600">{{ enrollment.course?.title }}</td>
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-2">
                      <div class="w-24 h-2 bg-slate-200 rounded-full overflow-hidden">
                        <div class="h-full bg-purple-500 rounded-full" :style="{ width: (enrollment.progress || 0) + '%' }"></div>
                      </div>
                      <span class="text-xs text-slate-500">{{ enrollment.progress || 0 }}%</span>
                    </div>
                  </td>
                  <td class="px-6 py-4 text-sm text-slate-500">{{ new Date(enrollment.created_at).toLocaleDateString() }}</td>
                  <td class="px-6 py-4 text-right">
                    <button @click="removeEnrollment(enrollment)" class="p-2 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg" title="Remove">
                      <Trash2 class="w-4 h-4" />
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
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
import { confirmDelete, confirmAction, showSuccess, showError } from '../utils/sweetalert';
import debounce from 'lodash/debounce';

const router = useRouter();
const auth = useAuthStore();

const enrollments = ref([]);
const userFilter = ref('');
const courseFilter = ref('');
const pagination = ref({});
const newEnrollment = ref({ user_id: '', course_id: '', reason: '' });

const fetchEnrollments = async (page = 1) => {
  const params = new URLSearchParams({ page });
  if (userFilter.value) params.append('user_id', userFilter.value);
  if (courseFilter.value) params.append('course_id', courseFilter.value);
  const res = await axios.get(`/api/admin/enrollments?${params}`);
  enrollments.value = res.data.data;
  pagination.value = res.data;
};

const debouncedSearch = debounce(() => fetchEnrollments(1), 300);
const changePage = (p) => fetchEnrollments(p);

const addEnrollment = async () => {
  if (!newEnrollment.value.user_id || !newEnrollment.value.course_id) {
    showError('Please provide both User ID and Course ID');
    return;
  }
  const result = await confirmAction({ title: 'Add Enrollment?', text: 'Create manual enrollment for this user?', confirmButtonText: 'Yes, add it!' });
  if (!result.isConfirmed) return;
  
  try {
    await axios.post('/api/admin/enrollments', newEnrollment.value);
    showSuccess('Enrollment created');
    newEnrollment.value = { user_id: '', course_id: '', reason: '' };
    fetchEnrollments();
  } catch (e) {
    showError(e.response?.data?.message || 'Failed to create enrollment');
  }
};

const removeEnrollment = async (enrollment) => {
  const result = await confirmDelete('this enrollment');
  if (!result.isConfirmed) return;
  await axios.delete(`/api/admin/enrollments/${enrollment.id}`);
  showSuccess('Enrollment removed');
  fetchEnrollments();
};

const handleLogout = async () => { await auth.logout(); router.push('/login'); };

onMounted(() => fetchEnrollments());
</script>
