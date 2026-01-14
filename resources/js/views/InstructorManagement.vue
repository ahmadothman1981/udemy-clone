<template>
  <div class="bg-slate-50 dark:bg-slate-900 min-h-screen flex font-inter transition-colors duration-300">
    <AdminSidebar @logout="handleLogout" />
    
    <main class="flex-1 flex flex-col min-w-0 overflow-hidden">
      <!-- Header -->
      <!-- Header -->
      <AdminHeader title="Instructor Management" @logout="handleLogout" />

      <div class="flex-1 overflow-auto p-8">
        <!-- Tabs -->
        <div class="flex space-x-1 rounded-xl bg-slate-200 dark:bg-slate-800 p-1 mb-6 max-w-md transition-colors">
          <button 
            v-for="tab in ['All Instructors', 'Pending Approval']" 
            :key="tab"
            @click="activeTab = tab"
            :class="[
              'flex-1 px-4 py-2 text-sm font-medium rounded-lg transition-all',
              activeTab === tab 
                ? 'bg-white dark:bg-slate-600 text-slate-900 dark:text-white shadow-sm' 
                : 'text-slate-600 dark:text-slate-400 hover:bg-white/50 dark:hover:bg-slate-700 hover:text-slate-800 dark:hover:text-slate-200'
            ]"
          >
            {{ tab }}
          </button>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8" v-if="activeTab === 'All Instructors'">
          <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-4 transition-colors">
            <div class="text-sm text-slate-500 dark:text-slate-400">Total Instructors</div>
            <div class="text-2xl font-bold text-slate-800 dark:text-white">{{ pagination.total || 0 }}</div>
          </div>
          <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-4 transition-colors">
            <div class="text-sm text-slate-500 dark:text-slate-400">Pending Approval</div>
            <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-500">{{ pendingInstructors.length }}</div>
          </div>
          <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-4 transition-colors">
            <div class="text-sm text-slate-500 dark:text-slate-400">Verified</div>
            <div class="text-2xl font-bold text-green-600 dark:text-green-500">{{ verifiedCount }}</div>
          </div>
          <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-4 transition-colors">
            <div class="text-sm text-slate-500 dark:text-slate-400">Restricted</div>
            <div class="text-2xl font-bold text-red-600 dark:text-red-500">{{ restrictedCount }}</div>
          </div>
        </div>

        <!-- Content -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden transition-colors">
          <!-- Toolbar -->
          <div class="p-4 border-b border-slate-100 dark:border-slate-700 flex items-center gap-4 transition-colors" v-if="activeTab === 'All Instructors'">
            <div class="flex-1">
              <input 
                v-model="searchQuery" 
                @input="debouncedSearch"
                type="text" 
                placeholder="Search instructors..." 
                class="w-full max-w-md px-4 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-800 dark:text-white placeholder-slate-400 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors"
              >
            </div>
            <select v-model="statusFilter" @change="fetchInstructors" class="px-4 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-800 dark:text-white transition-colors">
              <option value="">All Status</option>
              <option value="approved">Verified</option>
              <option value="pending">Pending</option>
              <option value="rejected">Rejected</option>
            </select>
          </div>

          <!-- Table -->
          <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead class="bg-slate-50/50 dark:bg-slate-700/50 text-slate-500 dark:text-slate-400 text-xs uppercase tracking-wider border-b border-slate-100 dark:border-slate-700 transition-colors">
                <tr>
                  <th class="px-6 py-4 font-semibold">Instructor</th>
                  <th class="px-6 py-4 font-semibold">Courses</th>
                  <th class="px-6 py-4 font-semibold">Students</th>
                  <th class="px-6 py-4 font-semibold">Rating</th>
                  <th class="px-6 py-4 font-semibold">Status</th>
                  <th class="px-6 py-4 font-semibold text-right">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 dark:divide-slate-700 transition-colors">
                <tr v-for="instructor in visibleInstructors" :key="instructor.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors group">
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center text-white font-semibold">
                        {{ instructor.name?.[0]?.toUpperCase() }}
                      </div>
                      <div>
                        <div class="font-medium text-slate-800 dark:text-white transition-colors">{{ instructor.name }}</div>
                        <div class="text-xs text-slate-500 dark:text-slate-400 transition-colors">{{ instructor.email }}</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300 transition-colors">{{ instructor.stats?.course_count || 0 }}</td>
                  <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300 transition-colors">{{ instructor.stats?.student_count || 0 }}</td>
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-1">
                      <Star class="w-4 h-4 text-yellow-400 fill-yellow-400" />
                      <span class="text-sm text-slate-600 dark:text-slate-300">{{ (instructor.stats?.avg_rating || 0).toFixed(1) }}</span>
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <span :class="[
                      'px-2 py-1 text-xs font-medium rounded-full',
                      instructor.instructor_verification_status === 'approved' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' :
                      instructor.instructor_verification_status === 'pending' ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400' :
                      'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'
                    ]">
                      {{ instructor.instructor_verification_status }}
                    </span>
                    <span v-if="(instructor.instructor_restrictions?.length || 0) > 0" class="ml-2 px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">
                      Restricted
                    </span>
                  </td>
                  <td class="px-6 py-4 text-right">
                    <div class="flex items-center justify-end gap-2">
                      <!-- For Pending Tab -->
                      <template v-if="activeTab === 'Pending Approval'">
                        <button @click="verifyInstructor(instructor, 'approve')" class="px-3 py-1.5 text-xs font-medium text-white bg-green-500 hover:bg-green-600 dark:bg-green-600 dark:hover:bg-green-700 rounded-lg flex items-center gap-1 transition-colors">
                          <CheckCircle class="w-3.5 h-3.5" /> Approve
                        </button>
                        <button @click="verifyInstructor(instructor, 'reject')" class="px-3 py-1.5 text-xs font-medium text-white bg-red-500 hover:bg-red-600 dark:bg-red-600 dark:hover:bg-red-700 rounded-lg flex items-center gap-1 transition-colors">
                          <XCircle class="w-3.5 h-3.5" /> Reject
                        </button>
                      </template>
                      <!-- For All Instructors Tab -->
                      <template v-else>
                        <button @click="showStatsModal(instructor)" class="p-2 text-slate-400 hover:text-purple-600 dark:hover:text-purple-400 hover:bg-purple-50 dark:hover:bg-purple-900/20 rounded-lg transition-colors" title="View Stats">
                          <BarChart2 class="w-4 h-4" />
                        </button>
                        <button @click="showRestrictModal(instructor)" class="p-2 text-slate-400 hover:text-orange-600 dark:hover:text-orange-400 hover:bg-orange-50 dark:hover:bg-orange-900/20 rounded-lg transition-colors" title="Manage Restrictions">
                          <Ban class="w-4 h-4" />
                        </button>
                      </template>
                    </div>
                  </td>
                </tr>
                <tr v-if="visibleInstructors.length === 0">
                  <td colspan="6" class="px-6 py-12 text-center text-slate-400 italic">
                    {{ activeTab === 'All Instructors' ? 'No instructors found.' : 'No pending instructor requests.' }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div class="p-4 border-t border-slate-100 dark:border-slate-700 flex items-center justify-between transition-colors" v-if="activeTab === 'All Instructors' && pagination.total > 0">
            <span class="text-sm text-slate-500 dark:text-slate-400">
              Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }}
            </span>
            <div class="flex gap-2">
              <button @click="changePage(pagination.current_page - 1)" :disabled="pagination.current_page === 1" class="px-3 py-1 text-sm border border-slate-200 dark:border-slate-600 rounded-lg text-slate-600 dark:text-slate-300 disabled:opacity-50 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                Previous
              </button>
              <button @click="changePage(pagination.current_page + 1)" :disabled="pagination.current_page === pagination.last_page" class="px-3 py-1 text-sm border border-slate-200 dark:border-slate-600 rounded-lg text-slate-600 dark:text-slate-300 disabled:opacity-50 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                Next
              </button>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- Stats Modal -->
    <div v-if="statsModalVisible" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click.self="statsModalVisible = false">
      <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-lg mx-4 overflow-hidden transition-colors">
        <div class="p-6 border-b border-slate-100 dark:border-slate-700 transition-colors">
          <h3 class="text-lg font-bold text-slate-800 dark:text-white">Instructor Stats: {{ selectedInstructor?.name }}</h3>
        </div>
        <div class="p-6 space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div class="bg-slate-50 dark:bg-slate-700/50 rounded-lg p-4 transition-colors">
              <div class="text-sm text-slate-500 dark:text-slate-400">Total Courses</div>
              <div class="text-2xl font-bold text-slate-800 dark:text-white">{{ selectedStats?.course_count || 0 }}</div>
            </div>
            <div class="bg-slate-50 dark:bg-slate-700/50 rounded-lg p-4 transition-colors">
              <div class="text-sm text-slate-500 dark:text-slate-400">Total Students</div>
              <div class="text-2xl font-bold text-slate-800 dark:text-white">{{ selectedStats?.student_count || 0 }}</div>
            </div>
            <div class="bg-slate-50 dark:bg-slate-700/50 rounded-lg p-4 transition-colors">
              <div class="text-sm text-slate-500 dark:text-slate-400">Avg Rating</div>
              <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-500">{{ (selectedStats?.avg_rating || 0).toFixed(1) }} ⭐</div>
            </div>
            <div class="bg-slate-50 dark:bg-slate-700/50 rounded-lg p-4 transition-colors">
              <div class="text-sm text-slate-500 dark:text-slate-400">Total Revenue</div>
              <div class="text-2xl font-bold text-green-600 dark:text-green-500">${{ (selectedStats?.total_revenue || 0).toFixed(2) }}</div>
            </div>
          </div>
          <div v-if="selectedStats?.restrictions?.length > 0" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 transition-colors">
            <div class="text-sm font-medium text-red-700 dark:text-red-400 mb-2">Active Restrictions:</div>
            <ul class="list-disc list-inside text-sm text-red-600 dark:text-red-300">
              <li v-for="r in selectedStats.restrictions" :key="r">{{ r.replace('_', ' ') }}</li>
            </ul>
          </div>
        </div>
        <div class="p-4 bg-slate-50 dark:bg-slate-800/50 flex justify-end transition-colors">
          <button @click="statsModalVisible = false" class="px-4 py-2 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-lg hover:bg-slate-300 dark:hover:bg-slate-600 transition-colors">Close</button>
        </div>
      </div>
    </div>

    <!-- Restrict Modal -->
    <div v-if="restrictModalVisible" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click.self="restrictModalVisible = false">
      <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden transition-colors">
        <div class="p-6 border-b border-slate-100 dark:border-slate-700 transition-colors">
          <h3 class="text-lg font-bold text-slate-800 dark:text-white">Manage Restrictions: {{ selectedInstructor?.name }}</h3>
        </div>
        <div class="p-6 space-y-4">
          <button @click="restrictInstructor('block_new_courses')" class="w-full p-4 text-left bg-slate-50 dark:bg-slate-700/30 rounded-lg hover:bg-orange-50 dark:hover:bg-orange-900/20 hover:border-orange-200 dark:hover:border-orange-800 border border-slate-200 dark:border-slate-600 transition-colors">
            <div class="font-medium text-slate-800 dark:text-white">Block New Courses</div>
            <div class="text-sm text-slate-500 dark:text-slate-400">Prevent instructor from creating new courses</div>
          </button>
          <button @click="restrictInstructor('block_new_students')" class="w-full p-4 text-left bg-slate-50 dark:bg-slate-700/30 rounded-lg hover:bg-orange-50 dark:hover:bg-orange-900/20 hover:border-orange-200 dark:hover:border-orange-800 border border-slate-200 dark:border-slate-600 transition-colors">
            <div class="font-medium text-slate-800 dark:text-white">Block New Students</div>
            <div class="text-sm text-slate-500 dark:text-slate-400">Stop accepting new enrollments</div>
          </button>
          <button @click="restrictInstructor('full_restrict')" class="w-full p-4 text-left bg-red-50 dark:bg-red-900/20 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/30 border border-red-200 dark:border-red-800 transition-colors">
            <div class="font-medium text-red-700 dark:text-red-400">Full Restriction</div>
            <div class="text-sm text-red-500 dark:text-red-300">Block all instructor activities</div>
          </button>
          <button @click="restrictInstructor('unrestrict')" class="w-full p-4 text-left bg-green-50 dark:bg-green-900/20 rounded-lg hover:bg-green-100 dark:hover:bg-green-900/30 border border-green-200 dark:border-green-800 transition-colors">
            <div class="font-medium text-green-700 dark:text-green-400">Remove All Restrictions</div>
            <div class="text-sm text-green-500 dark:text-green-300">Restore full instructor access</div>
          </button>
        </div>
        <div class="p-4 bg-slate-50 dark:bg-slate-800/50 flex justify-end transition-colors">
          <button @click="restrictModalVisible = false" class="px-4 py-2 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-lg hover:bg-slate-300 dark:hover:bg-slate-600 transition-colors">Cancel</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import AdminSidebar from '../components/admin/AdminSidebar.vue';
import AdminHeader from '../components/admin/AdminHeader.vue';
import { Star, CheckCircle, XCircle, BarChart2, Ban } from 'lucide-vue-next';
import axios from 'axios';
import { confirmAction, confirmDelete, showSuccess, showError, promptInput } from '../utils/sweetalert';
import debounce from 'lodash/debounce';

const router = useRouter();
const auth = useAuthStore();

const activeTab = ref('All Instructors');
const searchQuery = ref('');
const statusFilter = ref('');
const instructors = ref([]);
const pendingInstructors = ref([]);
const pagination = ref({ current_page: 1, last_page: 1, total: 0, from: 0, to: 0 });

const statsModalVisible = ref(false);
const restrictModalVisible = ref(false);
const selectedInstructor = ref(null);
const selectedStats = ref(null);

const visibleInstructors = computed(() => {
  return activeTab.value === 'Pending Approval' ? pendingInstructors.value : instructors.value;
});

const verifiedCount = computed(() => instructors.value.filter(i => i.instructor_verification_status === 'approved').length);
const restrictedCount = computed(() => instructors.value.filter(i => (i.instructor_restrictions?.length || 0) > 0).length);

const fetchInstructors = async (page = 1) => {
  try {
    const params = new URLSearchParams({ page });
    if (searchQuery.value) params.append('q', searchQuery.value);
    if (statusFilter.value) params.append('verification_status', statusFilter.value);
    
    const res = await axios.get(`/api/admin/instructors?${params}`);
    instructors.value = res.data.data;
    pagination.value = {
      current_page: res.data.current_page,
      last_page: res.data.last_page,
      total: res.data.total,
      from: res.data.from,
      to: res.data.to,
    };
  } catch (e) {
    showError('Failed to load instructors');
  }
};

const fetchPendingInstructors = async () => {
  try {
    const res = await axios.get('/api/admin/instructors/pending');
    pendingInstructors.value = res.data;
  } catch (e) {
    console.error(e);
  }
};

const debouncedSearch = debounce(() => fetchInstructors(1), 300);

const changePage = (page) => {
  if (page < 1 || page > pagination.value.last_page) return;
  fetchInstructors(page);
};

const verifyInstructor = async (instructor, action) => {
  let reason = '';
  if (action === 'reject') {
    const result = await promptInput({ title: 'Rejection Reason', inputPlaceholder: 'Enter reason for rejection...' });
    if (!result.isConfirmed) return;
    reason = result.value;
  } else {
    const result = await confirmAction({ title: 'Approve Instructor?', text: `Approve ${instructor.name} as an instructor?`, confirmButtonText: 'Yes, approve!' });
    if (!result.isConfirmed) return;
  }

  try {
    await axios.post(`/api/admin/instructors/${instructor.id}/verify`, { action, reason });
    showSuccess(`Instructor ${action}d successfully`);
    fetchPendingInstructors();
    fetchInstructors();
  } catch (e) {
    showError('Failed to update instructor status');
  }
};

const showStatsModal = async (instructor) => {
  selectedInstructor.value = instructor;
  try {
    const res = await axios.get(`/api/admin/instructors/${instructor.id}/stats`);
    selectedStats.value = res.data;
    statsModalVisible.value = true;
  } catch (e) {
    showError('Failed to load stats');
  }
};

const showRestrictModal = (instructor) => {
  selectedInstructor.value = instructor;
  restrictModalVisible.value = true;
};

const restrictInstructor = async (action) => {
  const result = await confirmAction({ 
    title: 'Confirm Action', 
    text: `Apply "${action.replace(/_/g, ' ')}" to ${selectedInstructor.value.name}?`,
    confirmButtonText: 'Yes, proceed!'
  });
  if (!result.isConfirmed) return;

  try {
    await axios.post(`/api/admin/instructors/${selectedInstructor.value.id}/restrict`, { action });
    showSuccess('Restriction updated');
    restrictModalVisible.value = false;
    fetchInstructors();
  } catch (e) {
    showError('Failed to update restrictions');
  }
};

const handleLogout = async () => {
  await auth.logout();
  router.push('/login');
};

onMounted(() => {
  fetchInstructors();
  fetchPendingInstructors();
});
</script>
