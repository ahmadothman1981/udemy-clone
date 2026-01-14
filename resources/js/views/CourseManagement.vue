<template>
  <div class="bg-slate-50 dark:bg-slate-900 min-h-screen flex font-inter transition-colors duration-300">
    <AdminSidebar @logout="handleLogout" />
    
    <main class="flex-1 flex flex-col min-w-0 overflow-hidden">
      <AdminHeader title="Course Management" @logout="handleLogout" />

      <div class="flex-1 overflow-auto p-8">
        <!-- Filters -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-4 mb-6 flex flex-wrap items-center gap-4 transition-colors">
          <input v-model="searchQuery" @input="debouncedSearch" type="text" placeholder="Search courses..." 
            class="px-4 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-purple-500 w-64 text-slate-800 dark:text-white placeholder-slate-400 transition-colors">
          <select v-model="statusFilter" @change="fetchCourses" class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-700 dark:text-white transition-colors">
            <option value="">All Status</option>
            <option value="pending">Pending</option>
            <option value="published">Published</option>
            <option value="rejected">Rejected</option>
          </select>
          <select v-model="categoryFilter" @change="fetchCourses" class="px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-700 dark:text-white transition-colors">
            <option value="">All Categories</option>
            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
          </select>
        </div>

        <!-- Bulk Actions Toolbar -->
        <div v-if="selectedCourses.length > 0" class="bg-purple-50 dark:bg-purple-900/20 p-4 border-b border-purple-100 dark:border-purple-800/50 flex items-center justify-between mb-4 rounded-xl animate-in fade-in slide-in-from-top-2 transition-colors">
            <div class="flex items-center gap-4">
                <span class="text-sm font-bold text-purple-700 dark:text-purple-300">{{ selectedCourses.length }} courses selected</span>
                <div class="h-4 w-px bg-purple-200 dark:bg-purple-800"></div>
                <button @click="selectedCourses = []; selectAll = false" class="text-sm text-purple-600 dark:text-purple-400 hover:text-purple-800 dark:hover:text-purple-300 hover:underline">
                    Deselect All
                </button>
            </div>
            <div class="flex items-center gap-2">
                <button @click="handleBulkAction('approve')" class="px-3 py-1.5 bg-white dark:bg-slate-800 border border-purple-200 dark:border-slate-600 text-green-600 dark:text-green-400 rounded-lg text-sm font-medium hover:bg-green-50 dark:hover:bg-green-900/20 shadow-sm transition-colors flex items-center gap-2">
                    <CheckCircle class="w-4 h-4" /> Approve
                </button>
                <button @click="handleBulkAction('reject')" class="px-3 py-1.5 bg-white dark:bg-slate-800 border border-purple-200 dark:border-slate-600 text-red-600 dark:text-red-400 rounded-lg text-sm font-medium hover:bg-red-50 dark:hover:bg-red-900/20 shadow-sm transition-colors flex items-center gap-2">
                    <XCircle class="w-4 h-4" /> Reject
                </button>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden transition-colors">
          <div class="overflow-x-auto">
            <table class="w-full text-left">
              <thead class="bg-slate-50 dark:bg-slate-700/50 text-slate-500 dark:text-slate-400 text-xs uppercase border-b border-slate-200 dark:border-slate-700 transition-colors">
                <tr>
                  <th class="px-6 py-4 w-12">
                    <input type="checkbox" v-model="selectAll" class="rounded border-slate-300 dark:border-slate-600 dark:bg-slate-700 text-purple-600 focus:ring-purple-500 w-4 h-4 transition-colors" />
                  </th>
                  <th class="px-6 py-4">Course</th>
                  <th class="px-6 py-4">Instructor</th>
                  <th class="px-6 py-4">Price</th>
                  <th class="px-6 py-4">Status</th>
                  <th class="px-6 py-4 text-right">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 dark:divide-slate-700 transition-colors">
                <template v-if="loading">
                    <tr v-for="n in 5" :key="n" class="animate-pulse border-b border-slate-100 dark:border-slate-700">
                        <td class="px-6 py-4"><SkeletonLoader width="1rem" height="1rem" /></td>
                        <td class="px-6 py-4">
                             <div class="flex items-center gap-3">
                                 <SkeletonLoader type="rect" width="4rem" height="2.5rem" className="rounded" />
                                 <div class="space-y-2">
                                     <SkeletonLoader width="10rem" height="1rem" />
                                     <SkeletonLoader width="6rem" height="0.75rem" />
                                 </div>
                             </div>
                        </td>
                        <td class="px-6 py-4"><SkeletonLoader width="8rem" height="1rem" /></td>
                        <td class="px-6 py-4"><SkeletonLoader width="4rem" height="1rem" /></td>
                        <td class="px-6 py-4"><SkeletonLoader width="5rem" height="1.5rem" className="rounded-full" /></td>
                        <td class="px-6 py-4"><SkeletonLoader width="8rem" height="2rem" className="ml-auto" /></td>
                    </tr>
                </template>
                <template v-else>
                <tr v-for="course in courses" :key="course.id" :class="{'bg-purple-50/30 dark:bg-purple-900/10': selectedCourses.includes(course.id)}" class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                  <td class="px-6 py-4">
                    <input type="checkbox" :value="course.id" v-model="selectedCourses" class="rounded border-slate-300 dark:border-slate-600 dark:bg-slate-700 text-purple-600 focus:ring-purple-500 w-4 h-4 transition-colors" />
                  </td>
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <img :src="course.thumbnail || '/placeholder.jpg'" class="w-16 h-10 object-cover rounded bg-slate-200 dark:bg-slate-700" />
                      <div>
                        <div class="font-medium text-slate-800 dark:text-white transition-colors">{{ course.title }}</div>
                        <div class="text-xs text-slate-500 dark:text-slate-400 transition-colors">{{ course.category?.name }}</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300 transition-colors">{{ course.instructor?.name }}</td>
                  <td class="px-6 py-4 text-sm font-medium text-slate-800 dark:text-white transition-colors">${{ course.price }}</td>
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
                </template>
              </tbody>
            </table>
          </div>
          <!-- Pagination -->
          <div class="p-4 border-t border-slate-200 dark:border-slate-700 flex justify-between items-center transition-colors">
            <span class="text-sm text-slate-500 dark:text-slate-400">{{ pagination.from }} - {{ pagination.to }} of {{ pagination.total }}</span>
            <div class="flex gap-2">
              <button @click="changePage(pagination.current_page - 1)" :disabled="pagination.current_page === 1" class="px-3 py-1 border border-slate-200 dark:border-slate-700 rounded-lg dark:text-slate-300 disabled:opacity-50 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">Previous</button>
              <button @click="changePage(pagination.current_page + 1)" :disabled="pagination.current_page === pagination.last_page" class="px-3 py-1 border border-slate-200 dark:border-slate-700 rounded-lg dark:text-slate-300 disabled:opacity-50 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">Next</button>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- Edit Modal -->
    <div v-if="editModalVisible" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click.self="editModalVisible = false">
      <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-lg mx-4 transition-colors">
        <div class="p-6 border-b border-slate-200 dark:border-slate-700"><h3 class="text-lg font-bold text-slate-800 dark:text-white">Edit Course</h3></div>
        <div class="p-6 space-y-4">
          <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Title</label>
            <input v-model="editForm.title" class="w-full px-4 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-lg text-slate-800 dark:text-white focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors" />
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Price</label>
            <input v-model="editForm.price" type="number" class="w-full px-4 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-lg text-slate-800 dark:text-white focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors" />
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Category</label>
            <select v-model="editForm.category_id" class="w-full px-4 py-2 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-600 rounded-lg text-slate-800 dark:text-white focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors">
              <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
            </select>
          </div>
        </div>
        <div class="p-4 bg-slate-50 dark:bg-slate-800/50 flex justify-end gap-2 rounded-b-2xl">
          <button @click="editModalVisible = false" class="px-4 py-2 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-lg hover:bg-slate-300 dark:hover:bg-slate-600 transition-colors">Cancel</button>
          <button @click="saveEdit" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">Save</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import AdminSidebar from '../components/admin/AdminSidebar.vue';
import AdminHeader from '../components/admin/AdminHeader.vue';
import SkeletonLoader from '../components/common/SkeletonLoader.vue';
import { CheckCircle, XCircle, Pencil, EyeOff, Eye, CheckSquare, Trash2 } from 'lucide-vue-next';
import axios from 'axios';
import { confirmAction, confirmUpdate, showSuccess, showError, promptInput } from '../utils/sweetalert';
import debounce from 'lodash/debounce';

const router = useRouter();
const auth = useAuthStore();

const courses = ref([]);
const loading = ref(false);
const categories = ref([]);
const searchQuery = ref('');
const statusFilter = ref('');
const categoryFilter = ref('');
const pagination = ref({});
const editModalVisible = ref(false);
const editForm = ref({});
const selectedCourse = ref(null);

// Bulk Selection
const selectedCourses = ref([]);
const selectAll = ref(false);

const statusClass = (status) => ({
  'px-2 py-1 text-xs font-medium rounded-full': true,
  'bg-green-100 text-green-700': status === 'published',
  'bg-yellow-100 text-yellow-700': status === 'pending',
  'bg-red-100 text-red-700': status === 'rejected',
  'bg-slate-100 text-slate-700': status === 'draft'
}[status] || 'bg-slate-100 text-slate-700');

// Watch courses to reset selection
watch(courses, () => {
    selectedCourses.value = [];
    selectAll.value = false;
});

// Watch selectAll
watch(selectAll, (val) => {
    if (val) {
        selectedCourses.value = courses.value.map(c => c.id);
    } else {
        selectedCourses.value = [];
    }
});

const fetchCourses = async (page = 1) => {
  loading.value = true;
  try {
      const params = new URLSearchParams({ page });
      if (searchQuery.value) params.append('q', searchQuery.value);
      if (statusFilter.value) params.append('status', statusFilter.value);
      if (categoryFilter.value) params.append('category_id', categoryFilter.value);
      
      const res = await axios.get(`/api/admin/courses?${params}`);
      courses.value = res.data.data;
      pagination.value = res.data;
  } catch (e) {
      console.error(e);
  } finally {
      loading.value = false;
  }
};

const fetchCategories = async () => {
  const res = await axios.get('/api/categories');
  categories.value = res.data;
};

const debouncedSearch = debounce(() => fetchCourses(1), 300);
const changePage = (p) => fetchCourses(p);

// Bulk Actions
const handleBulkAction = async (action) => {
    if (selectedCourses.value.length === 0) return;
    
    const count = selectedCourses.value.length;
    let confirmText = `Are you sure you want to ${action} ${count} selected courses?`;
    let reason = null;
    
    if (action === 'reject') {
        const result = await promptInput({ 
            title: `Bulk Reject ${count} Courses`, 
            inputPlaceholder: 'Enter rejection reason...' 
        });
        if (!result.isConfirmed) return;
        reason = result.value;
    } else {
        const result = await confirmAction({ 
            title: `Bulk ${action.charAt(0).toUpperCase() + action.slice(1)}`, 
            text: confirmText, 
            confirmButtonText: `Yes, ${action} them!` 
        });
        if (!result.isConfirmed) return;
    }
    
    try {
        await axios.post('/api/admin/courses/bulk', {
            course_ids: selectedCourses.value,
            action: action,
            reason: reason
        });
        showSuccess(`Successfully ${action}ed ${count} courses`);
        selectedCourses.value = [];
        selectAll.value = false;
        fetchCourses(pagination.value.current_page);
    } catch (e) {
        showError('Failed to perform bulk action');
    }
};

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

onMounted(() => { 
    fetchCourses(); 
    fetchCategories(); 

    if (window.Echo) {
        const userId = window.user?.id || document.querySelector('meta[name="user-id"]')?.getAttribute('content');
        if (userId) {
             window.Echo.private(`App.Models.User.${userId}`)
                .notification((notification) => {
                    if (notification.type === 'course_submission') {
                        showSuccess(`New Course Submitted: ${notification.title}`);
                        // Refresh if we are viewing pending or all
                        if (!statusFilter.value || statusFilter.value === 'pending') {
                            fetchCourses(pagination.value.current_page);
                        }
                    }
                });
        }
    }
});
</script>
