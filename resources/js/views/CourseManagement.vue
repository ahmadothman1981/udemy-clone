<template>
  <div class="bg-slate-50 min-h-screen flex font-inter">
    <AdminSidebar @logout="handleLogout" />
    
    <main class="flex-1 flex flex-col min-w-0 overflow-hidden">
      <header class="bg-white border-b border-slate-200 h-16 flex items-center justify-between px-8 sticky top-0 z-30">
        <h1 class="text-xl font-bold text-slate-800">Course Management</h1>
      </header>

      <div class="flex-1 overflow-auto p-8">
        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 mb-6 flex flex-wrap items-center gap-4">
          <input v-model="searchQuery" @input="debouncedSearch" type="text" placeholder="Search courses..." 
            class="px-4 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-purple-500 w-64">
          <select v-model="statusFilter" @change="fetchCourses" class="px-4 py-2 border border-slate-200 rounded-lg">
            <option value="">All Status</option>
            <option value="pending">Pending</option>
            <option value="published">Published</option>
            <option value="rejected">Rejected</option>
          </select>
          <select v-model="categoryFilter" @change="fetchCourses" class="px-4 py-2 border border-slate-200 rounded-lg">
            <option value="">All Categories</option>
            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
          </select>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full text-left">
              <thead class="bg-slate-50 text-slate-500 text-xs uppercase border-b">
                <tr>
                  <th class="px-6 py-4">Course</th>
                  <th class="px-6 py-4">Instructor</th>
                  <th class="px-6 py-4">Price</th>
                  <th class="px-6 py-4">Status</th>
                  <th class="px-6 py-4 text-right">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <tr v-for="course in courses" :key="course.id" class="hover:bg-slate-50">
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <img :src="course.thumbnail || '/placeholder.jpg'" class="w-16 h-10 object-cover rounded" />
                      <div>
                        <div class="font-medium text-slate-800">{{ course.title }}</div>
                        <div class="text-xs text-slate-500">{{ course.category?.name }}</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 text-sm text-slate-600">{{ course.instructor?.name }}</td>
                  <td class="px-6 py-4 text-sm font-medium">${{ course.price }}</td>
                  <td class="px-6 py-4">
                    <span :class="statusClass(course.status)">{{ course.status }}</span>
                    <span v-if="course.admin_hidden" class="ml-2 px-2 py-1 text-xs bg-red-100 text-red-700 rounded-full">Hidden</span>
                  </td>
                  <td class="px-6 py-4 text-right">
                    <div class="flex justify-end gap-2">
                      <button v-if="course.status === 'pending'" @click="approveCourse(course, 'approve')" class="p-2 text-green-600 hover:bg-green-50 rounded-lg" title="Approve">
                        <CheckCircle class="w-4 h-4" />
                      </button>
                      <button v-if="course.status === 'pending'" @click="approveCourse(course, 'reject')" class="p-2 text-red-600 hover:bg-red-50 rounded-lg" title="Reject">
                        <XCircle class="w-4 h-4" />
                      </button>
                      <button @click="openEditModal(course)" class="p-2 text-slate-400 hover:text-purple-600 hover:bg-purple-50 rounded-lg" title="Edit">
                        <Pencil class="w-4 h-4" />
                      </button>
                      <button v-if="!course.admin_hidden" @click="hideCourse(course)" class="p-2 text-slate-400 hover:text-orange-600 hover:bg-orange-50 rounded-lg" title="Hide">
                        <EyeOff class="w-4 h-4" />
                      </button>
                      <button v-else @click="restoreCourse(course)" class="p-2 text-slate-400 hover:text-green-600 hover:bg-green-50 rounded-lg" title="Restore">
                        <Eye class="w-4 h-4" />
                      </button>
                    </div>
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

    <!-- Edit Modal -->
    <div v-if="editModalVisible" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click.self="editModalVisible = false">
      <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4">
        <div class="p-6 border-b"><h3 class="text-lg font-bold">Edit Course</h3></div>
        <div class="p-6 space-y-4">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Title</label>
            <input v-model="editForm.title" class="w-full px-4 py-2 border rounded-lg" />
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Price</label>
            <input v-model="editForm.price" type="number" class="w-full px-4 py-2 border rounded-lg" />
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Category</label>
            <select v-model="editForm.category_id" class="w-full px-4 py-2 border rounded-lg">
              <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
            </select>
          </div>
        </div>
        <div class="p-4 bg-slate-50 flex justify-end gap-2">
          <button @click="editModalVisible = false" class="px-4 py-2 bg-slate-200 rounded-lg">Cancel</button>
          <button @click="saveEdit" class="px-4 py-2 bg-purple-600 text-white rounded-lg">Save</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import AdminSidebar from '../components/admin/AdminSidebar.vue';
import { CheckCircle, XCircle, Pencil, EyeOff, Eye } from 'lucide-vue-next';
import axios from 'axios';
import { confirmAction, confirmUpdate, showSuccess, showError, promptInput } from '../utils/sweetalert';
import debounce from 'lodash/debounce';

const router = useRouter();
const auth = useAuthStore();

const courses = ref([]);
const categories = ref([]);
const searchQuery = ref('');
const statusFilter = ref('');
const categoryFilter = ref('');
const pagination = ref({});
const editModalVisible = ref(false);
const editForm = ref({});
const selectedCourse = ref(null);

const statusClass = (status) => ({
  'px-2 py-1 text-xs font-medium rounded-full': true,
  'bg-green-100 text-green-700': status === 'published',
  'bg-yellow-100 text-yellow-700': status === 'pending',
  'bg-red-100 text-red-700': status === 'rejected',
}[status] || 'bg-slate-100 text-slate-700');

const fetchCourses = async (page = 1) => {
  const params = new URLSearchParams({ page });
  if (searchQuery.value) params.append('q', searchQuery.value);
  if (statusFilter.value) params.append('status', statusFilter.value);
  if (categoryFilter.value) params.append('category_id', categoryFilter.value);
  
  const res = await axios.get(`/api/admin/courses?${params}`);
  courses.value = res.data.data;
  pagination.value = res.data;
};

const fetchCategories = async () => {
  const res = await axios.get('/api/categories');
  categories.value = res.data;
};

const debouncedSearch = debounce(() => fetchCourses(1), 300);
const changePage = (p) => fetchCourses(p);

const approveCourse = async (course, action) => {
  let reason = '';
  if (action === 'reject') {
    const result = await promptInput({ title: 'Rejection Reason' });
    if (!result.isConfirmed) return;
    reason = result.value;
  } else {
    const result = await confirmAction({ title: 'Approve Course?', text: `Approve "${course.title}"?`, confirmButtonText: 'Approve' });
    if (!result.isConfirmed) return;
  }
  await axios.post(`/api/admin/courses/${course.id}/approve`, { action, reason });
  showSuccess(`Course ${action}d`);
  fetchCourses();
};

const hideCourse = async (course) => {
  const result = await promptInput({ title: 'Hide Course', inputPlaceholder: 'Enter reason for hiding...' });
  if (!result.isConfirmed) return;
  await axios.post(`/api/admin/courses/${course.id}/hide`, { reason: result.value });
  showSuccess('Course hidden');
  fetchCourses();
};

const restoreCourse = async (course) => {
  const result = await confirmAction({ title: 'Restore Course?', text: 'Make this course visible again?', confirmButtonText: 'Restore' });
  if (!result.isConfirmed) return;
  await axios.post(`/api/admin/courses/${course.id}/restore`);
  showSuccess('Course restored');
  fetchCourses();
};

const openEditModal = (course) => {
  selectedCourse.value = course;
  editForm.value = { title: course.title, price: course.price, category_id: course.category_id };
  editModalVisible.value = true;
};

const saveEdit = async () => {
  const result = await confirmUpdate('course details');
  if (!result.isConfirmed) return;
  await axios.put(`/api/admin/courses/${selectedCourse.value.id}`, editForm.value);
  showSuccess('Course updated');
  editModalVisible.value = false;
  fetchCourses();
};

const handleLogout = async () => { await auth.logout(); router.push('/login'); };

onMounted(() => { fetchCourses(); fetchCategories(); });
</script>
