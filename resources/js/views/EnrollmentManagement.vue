<template>
  <div class="bg-slate-50 dark:bg-slate-900 min-h-screen flex font-inter transition-colors duration-300">
    <AdminSidebar @logout="handleLogout" />
    
    <main class="flex-1 flex flex-col min-w-0 overflow-hidden">
      <AdminHeader :title="$t('admin.enrollments.title')" @logout="handleLogout" />

      <div class="flex-1 overflow-auto p-8">
        <!-- Add Enrollment Form -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 mb-6 transition-colors">
          <h2 class="text-lg font-semibold mb-4 text-slate-800 dark:text-white">{{ $t('admin.enrollments.manual_title') }}</h2>
          <div class="flex flex-wrap gap-4 items-end">
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">{{ $t('admin.enrollments.user_id') }}</label>
              <input v-model="newEnrollment.user_id" type="number" class="px-4 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-600 rounded-lg w-40 text-slate-800 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-purple-500 transition-colors" :placeholder="$t('admin.enrollments.user_id_ph')" />
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">{{ $t('admin.enrollments.course_id') }}</label>
              <input v-model="newEnrollment.course_id" type="number" class="px-4 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-600 rounded-lg w-40 text-slate-800 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-purple-500 transition-colors" :placeholder="$t('admin.enrollments.course_id_ph')" />
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">{{ $t('admin.enrollments.reason') }}</label>
              <input v-model="newEnrollment.reason" class="px-4 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-600 rounded-lg w-64 text-slate-800 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-purple-500 transition-colors" :placeholder="$t('admin.enrollments.reason_ph')" />
            </div>
            <button @click="addEnrollment" class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">{{ $t('admin.enrollments.add_btn') }}</button>
          </div>
        </div>

        <!-- Filters -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-4 mb-6 flex flex-wrap items-center gap-4 transition-colors">
          <input v-model="userFilter" @input="debouncedSearch" :placeholder="$t('admin.enrollments.user_filter_ph')" class="px-4 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-600 rounded-lg w-48 text-slate-800 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-purple-500 transition-colors" />
          <input v-model="courseFilter" @input="debouncedSearch" :placeholder="$t('admin.enrollments.course_filter_ph')" class="px-4 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-600 rounded-lg w-48 text-slate-800 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-purple-500 transition-colors" />
        </div>

        <!-- Enrollments Table -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden transition-colors">
          <div class="overflow-x-auto">
            <table class="w-full text-left">
              <thead class="bg-slate-50 dark:bg-slate-700/50 text-slate-500 dark:text-slate-400 text-xs uppercase border-b border-slate-200 dark:border-slate-700 transition-colors">
                <tr>
                  <th class="px-6 py-4">{{ $t('admin.enrollments.th_student') }}</th>
                  <th class="px-6 py-4">{{ $t('admin.enrollments.th_course') }}</th>
                  <th class="px-6 py-4">{{ $t('admin.enrollments.th_progress') }}</th>
                  <th class="px-6 py-4">{{ $t('admin.enrollments.th_enrolled_at') }}</th>
                  <th class="px-6 py-4 text-right">{{ $t('admin.enrollments.th_actions') }}</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 dark:divide-slate-700 transition-colors">
                <tr v-for="enrollment in enrollments" :key="enrollment.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <div class="w-8 h-8 rounded-full bg-purple-100 dark:bg-purple-900/40 text-purple-600 dark:text-purple-400 flex items-center justify-center font-semibold text-sm transition-colors">
                        {{ enrollment.user?.name?.[0]?.toUpperCase() }}
                      </div>
                      <div>
                        <div class="font-medium text-slate-800 dark:text-white transition-colors">{{ enrollment.user?.name }}</div>
                        <div class="text-xs text-slate-500 dark:text-slate-400 transition-colors">{{ enrollment.user?.email }}</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300 transition-colors">{{ enrollment.course?.title }}</td>
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-2">
                      <div class="w-24 h-2 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden transition-colors">
                        <div class="h-full bg-purple-500 rounded-full" :style="{ width: (enrollment.progress || 0) + '%' }"></div>
                      </div>
                      <span class="text-xs text-slate-500 dark:text-slate-400 transition-colors">{{ enrollment.progress || 0 }}%</span>
                    </div>
                  </td>
                  <td class="px-6 py-4 text-sm text-slate-500 dark:text-slate-400 transition-colors">{{ new Date(enrollment.created_at).toLocaleDateString() }}</td>
                  <td class="px-6 py-4 text-right">
                    <button @click="removeEnrollment(enrollment)" class="p-2 text-red-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors" :title="$t('admin.enrollments.remove')">
                      <Trash2 class="w-4 h-4" />
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <!-- Pagination -->
          <div class="p-4 border-t border-slate-200 dark:border-slate-700 flex justify-between items-center transition-colors">
            <span class="text-sm text-slate-500 dark:text-slate-400">{{ $t('admin.enrollments.showing', {from: pagination.from, to: pagination.to, total: pagination.total}) }}</span>
            <div class="flex gap-2">
              <button @click="changePage(pagination.current_page - 1)" :disabled="pagination.current_page === 1" class="px-3 py-1 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-600 dark:text-slate-300 disabled:opacity-50 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">{{ $t('admin.enrollments.previous') }}</button>
              <button @click="changePage(pagination.current_page + 1)" :disabled="pagination.current_page === pagination.last_page" class="px-3 py-1 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-600 dark:text-slate-300 disabled:opacity-50 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">{{ $t('admin.enrollments.next') }}</button>
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
import AdminHeader from '../components/admin/AdminHeader.vue';
import { Trash2 } from 'lucide-vue-next';
import axios from 'axios';
import { confirmDelete, confirmAction, showSuccess, showError } from '../utils/sweetalert';
import debounce from 'lodash/debounce';
import { useI18n } from 'vue-i18n';
const { t } = useI18n();

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
    showError(t('admin.enrollments.error_missing_fields'));
    return;
  }
  const result = await confirmAction({ title: t('admin.enrollments.confirm_add_title'), text: t('admin.enrollments.confirm_add_text'), confirmButtonText: t('admin.enrollments.confirm_add_btn') });
  if (!result.isConfirmed) return;
  
  try {
    await axios.post('/api/admin/enrollments', newEnrollment.value);
    showSuccess(t('admin.enrollments.created'));
    newEnrollment.value = { user_id: '', course_id: '', reason: '' };
    fetchEnrollments();
  } catch (e) {
    showError(e.response?.data?.message || t('admin.enrollments.create_error'));
  }
};

const removeEnrollment = async (enrollment) => {
  const result = await confirmDelete(t('admin.enrollments.enrollment_item'));
  if (!result.isConfirmed) return;
  await axios.delete(`/api/admin/enrollments/${enrollment.id}`);
  showSuccess(t('admin.enrollments.removed'));
  fetchEnrollments();
};

const handleLogout = async () => { await auth.logout(); router.push('/login'); };

onMounted(() => fetchEnrollments());
</script>
