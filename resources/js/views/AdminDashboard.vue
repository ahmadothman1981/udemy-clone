<template>
  <div class="bg-slate-50 dark:bg-slate-900 min-h-screen flex font-inter transition-colors duration-300">
    <!-- Sidebar -->
    <AdminSidebar @logout="handleLogout" />

    <!-- Main ContentWrapper -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        
        <!-- Top Header -->
        <!-- Top Header -->
        <AdminHeader :title="$t('admin.dashboard.title')" @logout="handleLogout" />

        <!-- Main Content -->
        <main class="flex-1 overflow-auto p-8">
            <!-- Date Range Picker & Export -->
            <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
              <DateRangePicker 
                v-model="selectedDays" 
                :compare="compareEnabled"
                @range-change="handleRangeChange"
                @compare-change="compareEnabled = $event"
              />
              <div class="flex items-center gap-2">
                <div class="relative">
                  <button @click="exportDropdownOpen = !exportDropdownOpen" class="flex items-center gap-2 px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-700 dark:text-slate-200 transition-colors">
                    <Download class="w-4 h-4" /> {{ $t('admin.dashboard.export') }}
                    <ChevronDown class="w-4 h-4" />
                  </button>
                  <div v-if="exportDropdownOpen" class="absolute right-0 mt-2 w-48 bg-white dark:bg-slate-800 rounded-lg shadow-lg border border-slate-200 dark:border-slate-700 py-1 z-50">
                    <button @click="exportData('summary')" class="w-full px-4 py-2 text-left text-sm hover:bg-slate-50 dark:hover:bg-slate-700 dark:text-slate-300">{{ $t('admin.dashboard.summary_report') }}</button>
                    <button @click="exportData('users')" class="w-full px-4 py-2 text-left text-sm hover:bg-slate-50 dark:hover:bg-slate-700 dark:text-slate-300">{{ $t('admin.dashboard.new_users_report') }}</button>
                    <button @click="exportData('revenue')" class="w-full px-4 py-2 text-left text-sm hover:bg-slate-50 dark:hover:bg-slate-700 dark:text-slate-300">{{ $t('admin.dashboard.revenue_report') }}</button>
                    <button @click="exportData('enrollments')" class="w-full px-4 py-2 text-left text-sm hover:bg-slate-50 dark:hover:bg-slate-700 dark:text-slate-300">{{ $t('admin.dashboard.enrollments_report') }}</button>
                    <button @click="exportData('courses')" class="w-full px-4 py-2 text-left text-sm hover:bg-slate-50 dark:hover:bg-slate-700 dark:text-slate-300">{{ $t('admin.dashboard.top_courses_report') }}</button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Stats Grid -->
            <div v-if="!stats" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div v-for="i in 4" :key="i" class="bg-white dark:bg-slate-800 rounded-xl p-6 shadow-sm border border-slate-200 dark:border-slate-700 transition-colors">
                    <div class="flex justify-between items-start mb-4">
                        <SkeletonLoader width="40%" height="1rem" />
                        <SkeletonLoader type="circle" width="3rem" height="3rem" />
                    </div>
                    <SkeletonLoader width="60%" height="2rem" />
                </div>
            </div>
            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <StatCard :title="$t('admin.dashboard.total_users')" :value="stats?.total_users || 0" type="blue">
                    <template #icon><Users class="w-6 h-6" /></template>
                </StatCard>
                <StatCard :title="$t('admin.dashboard.total_courses')" :value="stats?.total_courses || 0" type="purple">
                     <template #icon><BookOpen class="w-6 h-6" /></template>
                </StatCard>
                <StatCard :title="$t('admin.dashboard.total_revenue')" :value="formatCurrency(stats?.total_revenue || 0)" type="success">
                     <template #icon><DollarSign class="w-6 h-6" /></template>
                </StatCard>
                <StatCard :title="$t('admin.dashboard.pending_approval')" :value="stats?.pending_courses || 0" type="warning">
                     <template #icon><Clock class="w-6 h-6" /></template>
                </StatCard>
            </div>

            <!-- Period Stats (when comparison enabled) -->
            <div v-if="analytics?.period_totals" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
              <div class="bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 p-4 transition-colors">
                <div class="text-sm text-slate-500 dark:text-slate-400">{{ $t('admin.dashboard.period_new_users') }}</div>
                <div class="text-2xl font-bold text-slate-800 dark:text-white">{{ analytics.period_totals.new_users }}</div>
                <div v-if="analytics?.comparison" :class="getChangeClass(analytics.period_totals.new_users, analytics.comparison.new_users)" class="text-xs mt-1">
                  {{ getChangeText(analytics.period_totals.new_users, analytics.comparison.new_users) }}
                </div>
              </div>
              <div class="bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 p-4 transition-colors">
                <div class="text-sm text-slate-500 dark:text-slate-400">{{ $t('admin.dashboard.period_new_enrollments') }}</div>
                <div class="text-2xl font-bold text-slate-800 dark:text-white">{{ analytics.period_totals.new_enrollments }}</div>
                <div v-if="analytics?.comparison" :class="getChangeClass(analytics.period_totals.new_enrollments, analytics.comparison.new_enrollments)" class="text-xs mt-1">
                  {{ getChangeText(analytics.period_totals.new_enrollments, analytics.comparison.new_enrollments) }}
                </div>
              </div>
              <div class="bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 p-4 transition-colors">
                <div class="text-sm text-slate-500 dark:text-slate-400">{{ $t('admin.dashboard.period_revenue') }}</div>
                <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ formatCurrency(analytics.period_totals.revenue) }}</div>
                <div v-if="analytics?.comparison" :class="getChangeClass(analytics.period_totals.revenue, analytics.comparison.revenue)" class="text-xs mt-1">
                  {{ getChangeText(analytics.period_totals.revenue, analytics.comparison.revenue) }}
                </div>
              </div>
            </div>

            <!-- Charts Row -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
              <!-- New Users Chart -->
              <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 transition-colors">
                <h3 class="text-lg font-bold text-slate-800 dark:text-white mb-4">{{ $t('admin.dashboard.chart_new_users') }}</h3>
                <div class="h-64">
                  <Line v-if="chartDataLoaded" :data="newUsersChartData" :options="chartOptions" />
                  <div v-else class="h-full w-full flex flex-col justify-end space-y-2">
                     <div class="flex items-end justify-between h-full gap-2">
                        <SkeletonLoader v-for="n in 12" :key="n" width="8%" :height="`${Math.random() * 80 + 20}%`" className="rounded-t-md" />
                     </div>
                  </div>
                </div>
              </div>

              <!-- Revenue Chart -->
              <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 transition-colors">
                <h3 class="text-lg font-bold text-slate-800 dark:text-white mb-4">{{ $t('admin.dashboard.chart_revenue') }}</h3>
                <div class="h-64">
                  <Line v-if="chartDataLoaded" :data="revenueChartData" :options="chartOptions" />
                  <div v-else class="h-full w-full flex flex-col justify-end space-y-2">
                      <SkeletonLoader width="100%" height="80%" className="rounded-lg" />
                  </div>
                </div>
              </div>
            </div>

            <!-- Stats & Tables Split Layout -->
             <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                 <!-- Main Table Area -->
                 <div class="lg:col-span-2 space-y-6">
                     <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden transition-colors">
                         <div class="p-6 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center transition-colors">
                             <div>
                                 <h2 class="text-lg font-bold text-slate-800 dark:text-white">{{ $t('admin.dashboard.pending_courses_title') }}</h2>
                                 <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">{{ $t('admin.dashboard.pending_courses_desc') }}</p>
                             </div>
                             <span class="px-3 py-1 bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 text-xs font-bold rounded-full border border-amber-100 dark:border-amber-800/50 transition-colors">
                                 {{ pendingCourses.length }} {{ $t('admin.dashboard.waiting') }}
                             </span>
                         </div>
                         
                         <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-slate-50/50 dark:bg-slate-700/50 text-slate-500 dark:text-slate-400 text-xs uppercase tracking-wider border-b border-slate-100 dark:border-slate-700 transition-colors">
                                        <th class="px-6 py-4 font-semibold">{{ $t('admin.dashboard.th_course') }}</th>
                                        <th class="px-6 py-4 font-semibold">{{ $t('admin.dashboard.th_instructor') }}</th>
                                        <th class="px-6 py-4 font-semibold text-right">{{ $t('admin.dashboard.th_actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-700 transition-colors">
                                    <tr v-for="course in pendingCourses" :key="course.id" class="group hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-4">
                                                <div class="h-10 w-16 bg-slate-200 dark:bg-slate-700 rounded overflow-hidden flex-shrink-0 transition-colors">
                                                    <img v-if="course.thumbnail" :src="course.thumbnail" class="w-full h-full object-cover" />
                                                    <div v-else class="w-full h-full flex items-center justify-center text-slate-400">
                                                        <ImageIcon class="w-4 h-4" />
                                                    </div>
                                                </div>
                                                <div>
                                                    <div class="font-bold text-slate-800 dark:text-white group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors">{{ course.title }}</div>
                                                    <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ course.category?.name }} • {{ formatCurrency(course.price) }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-2">
                                                 <div class="h-6 w-6 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center text-xs text-slate-600 dark:text-slate-300 font-bold transition-colors">
                                                     {{ course.instructor?.name?.[0] }}
                                                 </div>
                                                 <span class="text-sm font-medium text-slate-600 dark:text-slate-300">{{ course.instructor?.name }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <button @click="approve(course.id)" class="p-2 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded-lg transition-colors" :title="$t('admin.dashboard.approve')">
                                                    <CheckCircle class="w-5 h-5" />
                                                </button>
                                                <button @click="reject(course.id)" class="p-2 hover:bg-red-50 dark:hover:bg-red-900/30 text-red-600 dark:text-red-400 rounded-lg transition-colors" :title="$t('admin.dashboard.reject')">
                                                    <XCircle class="w-5 h-5" />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="pendingCourses.length === 0">
                                        <td colspan="3" class="px-6 py-12 text-center">
                                            <div class="flex flex-col items-center justify-center text-slate-400">
                                                <CheckCircle class="w-12 h-12 mb-3 opacity-20" />
                                                <p class="text-sm font-medium">{{ $t('admin.dashboard.no_pending') }}</p>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                         </div>
                     </div>

                     <!-- Top Courses Card -->
                     <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 transition-colors">
                       <h3 class="text-lg font-bold text-slate-800 dark:text-white mb-4">{{ $t('admin.dashboard.top_selling_title') }}</h3>
                       <div class="space-y-3">
                         <div v-for="course in analytics?.top_courses?.slice(0, 5)" :key="course.id" class="flex items-center justify-between py-2 border-b border-slate-100 dark:border-slate-700 last:border-0 transition-colors">
                           <div>
                             <div class="font-medium text-slate-800 dark:text-white">{{ course.title }}</div>
                             <div class="text-xs text-slate-500 dark:text-slate-400">{{ formatCurrency(course.price) }}</div>
                           </div>
                           <span class="px-2 py-1 bg-purple-100 dark:bg-purple-900/40 text-purple-700 dark:text-purple-300 text-xs font-medium rounded-full transition-colors">
                             {{ course.enrollments_count }} {{ $t('admin.dashboard.enrolled') }}
                           </span>
                         </div>
                         <div v-if="!analytics?.top_courses?.length" class="text-sm text-slate-400 italic py-4 text-center">{{ $t('admin.dashboard.no_courses') }}</div>
                       </div>
                     </div>
                 </div>

                 <!-- Right Column -->
                 <div class="space-y-6">
                     <div class="bg-gradient-to-br from-purple-600 to-indigo-700 rounded-xl p-6 text-white shadow-lg">
                         <h3 class="text-lg font-bold mb-2">{{ $t('admin.dashboard.system_status') }}</h3>
                         <div class="flex items-center gap-2 text-purple-100 text-sm mb-6">
                             <div :class="['w-2 h-2 rounded-full animate-pulse', stats?.system_health?.database ? 'bg-emerald-400' : 'bg-red-400']"></div>
                             {{ stats?.system_health?.database ? $t('admin.dashboard.all_systems_operational') : $t('admin.dashboard.database_error') }}
                         </div>
                         <div class="space-y-3">
                              <div class="flex justify-between text-xs opacity-80 mb-1">
                                  <span>{{ $t('admin.dashboard.disk_usage') }}</span>
                                  <span>{{ diskUsage }}%</span>
                              </div>
                              <div class="h-1.5 bg-purple-900/30 rounded-full overflow-hidden">
                                  <div class="h-full bg-emerald-400 rounded-full" :style="{ width: diskUsage + '%' }"></div>
                              </div>
                              
                              <div class="flex justify-between text-xs opacity-80 mb-1 mt-4">
                                  <span>{{ $t('admin.dashboard.server_load') }}</span>
                                  <span>{{ stats?.system_health?.server_load || 0 }}</span>
                              </div>
                              <div class="h-1.5 bg-purple-900/30 rounded-full overflow-hidden">
                                  <div class="h-full bg-amber-400 w-1/4 rounded-full"></div>
                              </div>
                         </div>
                     </div>

                     <!-- Quick Stats -->
                     <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 transition-colors">
                       <h3 class="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-wide mb-4">{{ $t('admin.dashboard.platform_stats') }}</h3>
                       <div class="space-y-4">
                         <div class="flex justify-between items-center">
                           <span class="text-sm text-slate-600 dark:text-slate-400">{{ $t('admin.dashboard.avg_completion') }}</span>
                           <span class="font-bold text-slate-800 dark:text-white">{{ (analytics?.avg_completion_rate || 0).toFixed(1) }}%</span>
                         </div>
                         <div class="flex justify-between items-center">
                           <span class="text-sm text-slate-600 dark:text-slate-400">{{ $t('admin.dashboard.total_instructors') }}</span>
                           <span class="font-bold text-slate-800 dark:text-white">{{ stats?.total_instructors || 0 }}</span>
                         </div>
                         <div class="flex justify-between items-center">
                           <span class="text-sm text-slate-600 dark:text-slate-400">{{ $t('admin.dashboard.pending_instructors') }}</span>
                           <span class="font-bold text-slate-800 dark:text-white">{{ stats?.pending_instructors || 0 }}</span>
                         </div>
                         <div class="flex justify-between items-center">
                           <span class="text-sm text-slate-600 dark:text-slate-400">{{ $t('admin.dashboard.total_enrollments') }}</span>
                           <span class="font-bold text-slate-800 dark:text-white">{{ stats?.total_enrollments || 0 }}</span>
                         </div>
                       </div>
                     </div>

                     <!-- Recent Activity -->
                     <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 transition-colors">
                         <h3 class="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-wide mb-4">{{ $t('admin.dashboard.recent_signups') }}</h3>
                         <ul class="space-y-4">
                             <li v-for="user in stats?.recent_activity?.users" :key="user.id" class="flex items-center gap-3">
                                 <div class="h-8 w-8 rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-xs font-bold text-slate-500 dark:text-slate-300 transition-colors">
                                     {{ user.name[0] }}
                                 </div>
                                 <div class="text-sm">
                                     <div class="font-medium text-slate-800 dark:text-white">{{ user.name }}</div>
                                     <div class="text-xs text-slate-400">{{ $t('admin.dashboard.joined') }} {{ new Date(user.created_at).toLocaleDateString() }}</div>
                                 </div>
                             </li>
                             <li v-if="!stats?.recent_activity?.users?.length" class="text-sm text-slate-400 italic">{{ $t('admin.dashboard.no_activity') }}</li>
                         </ul>
                     </div>
                 </div>
             </div>
        </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { useAuthStore } from '../stores/auth';
import { useI18n } from 'vue-i18n';
const { t } = useI18n();

import AdminSidebar from '../components/admin/AdminSidebar.vue';
import AdminHeader from '../components/admin/AdminHeader.vue';
import StatCard from '../components/admin/StatCard.vue';
import SkeletonLoader from '../components/common/SkeletonLoader.vue';
import DateRangePicker from '../components/admin/DateRangePicker.vue';
import { Users, BookOpen, DollarSign, Clock, CheckCircle, XCircle, Image as ImageIcon, Download, ChevronDown } from 'lucide-vue-next';
import { Line } from 'vue-chartjs';
import { Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, Filler } from 'chart.js';
import { confirmAction, showSuccess, showError, promptInput } from '../utils/sweetalert';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, Filler);

const auth = useAuthStore();
const router = useRouter();

const stats = ref(null);
const analytics = ref(null);
const pendingCourses = ref([]);
const chartDataLoaded = ref(false);
const exportDropdownOpen = ref(false);

// Date range state
const selectedDays = ref(30);
const dateRange = ref({ start_date: '', end_date: '' });
const compareEnabled = ref(false);

const newUsersChartData = ref({
  labels: [],
  datasets: [{
    label: 'New Users',
    data: [],
    borderColor: '#8b5cf6',
    backgroundColor: 'rgba(139, 92, 246, 0.1)',
    fill: true,
    tension: 0.4
  }]
});

const revenueChartData = ref({
  labels: [],
  datasets: [{
    label: 'Revenue ($)',
    data: [],
    borderColor: '#10b981',
    backgroundColor: 'rgba(16, 185, 129, 0.1)',
    fill: true,
    tension: 0.4
  }]
});

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: { legend: { display: false } },
  scales: {
    x: { grid: { display: false } },
    y: { beginAtZero: true }
  }
};

const diskUsage = computed(() => {
  if (!stats.value?.system_health) return 0;
  const { disk_free, disk_total } = stats.value.system_health;
  if (!disk_total) return 0;
  return Math.round((1 - disk_free / disk_total) * 100);
});

const formatCurrency = (val) => {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(val);
};

const getChangeClass = (current, previous) => {
  if (current > previous) return 'text-green-600';
  if (current < previous) return 'text-red-600';
  return 'text-slate-500';
};

const getChangeText = (current, previous) => {
  if (!previous) return 'N/A';
  const change = ((current - previous) / previous) * 100;
  const arrow = change >= 0 ? '↑' : '↓';
  return `${arrow} ${Math.abs(change).toFixed(1)}% ${t('admin.dashboard.vs_previous')}`;
};

const handleRangeChange = (range) => {
  dateRange.value = range;
  loadAnalytics();
};

const loadAnalytics = async () => {
  chartDataLoaded.value = false;
  try {
    const params = new URLSearchParams();
    if (dateRange.value.start_date) params.append('start_date', dateRange.value.start_date);
    if (dateRange.value.end_date) params.append('end_date', dateRange.value.end_date);
    if (compareEnabled.value) params.append('compare', 'true');
    
    const res = await axios.get(`/api/admin/analytics?${params}`);
    analytics.value = res.data;

    // Populate charts
    if (res.data.new_users_daily) {
      newUsersChartData.value.labels = res.data.new_users_daily.map(d => d.date);
      newUsersChartData.value.datasets[0].data = res.data.new_users_daily.map(d => d.count);
    }
    if (res.data.revenue_trend) {
      revenueChartData.value.labels = res.data.revenue_trend.map(d => d.date);
      revenueChartData.value.datasets[0].data = res.data.revenue_trend.map(d => parseFloat(d.total) || 0);
    }
    chartDataLoaded.value = true;
  } catch (e) {
    console.error(e);
  }
};

const loadDashboardData = async () => {
  try {
    const [statsRes, pendingRes] = await Promise.all([
      axios.get('/api/admin/stats'),
      axios.get('/api/admin/courses/pending')
    ]);
    
    stats.value = statsRes.data;
    pendingCourses.value = pendingRes.data;
    
    // Load analytics with default date range
    await loadAnalytics();
  } catch (e) {
    console.error(e);
  }
};

const exportData = (type) => {
  exportDropdownOpen.value = false;
  const params = new URLSearchParams({ type });
  if (dateRange.value.start_date) params.append('start_date', dateRange.value.start_date);
  if (dateRange.value.end_date) params.append('end_date', dateRange.value.end_date);
  window.open(`/api/admin/export/analytics?${params}`, '_blank');
};

// Close dropdown when clicking outside
const closeDropdown = (e) => {
  if (!e.target.closest('.relative')) exportDropdownOpen.value = false;
};

const approve = async (id) => {
    const result = await confirmAction({ title: 'Approve Course?', text: 'This will publish the course.', confirmButtonText: 'Approve' });
    if (!result.isConfirmed) return;
    try {
        await axios.post(`/api/admin/courses/${id}/approve`, { action: 'approve' });
        pendingCourses.value = pendingCourses.value.filter(c => c.id !== id);
        if (stats.value) stats.value.pending_courses--;
        showSuccess('Course approved');
    } catch (e) {
        showError('Failed to approve course');
    }
};

const reject = async (id) => {
    const result = await promptInput({ title: 'Reject Course', inputPlaceholder: 'Enter reason for rejection...' });
    if (!result.isConfirmed) return;
    try {
        await axios.post(`/api/admin/courses/${id}/approve`, { action: 'reject', reason: result.value });
        pendingCourses.value = pendingCourses.value.filter(c => c.id !== id);
        if (stats.value) stats.value.pending_courses--;
        showSuccess('Course rejected');
    } catch (e) {
         showError('Failed to reject course');
    }
};

const handleLogout = async () => {
    await auth.logout();
    router.push('/login');
};

onMounted(() => {
  loadDashboardData();
  document.addEventListener('click', closeDropdown);
});

onUnmounted(() => {
  document.removeEventListener('click', closeDropdown);
});
</script>
