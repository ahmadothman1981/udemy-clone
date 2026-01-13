<template>
  <div class="bg-slate-50 min-h-screen flex font-inter">
    <!-- Sidebar -->
    <AdminSidebar @logout="handleLogout" />

    <!-- Main ContentWrapper -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        
        <!-- Top Header (Simplified) -->
        <header class="bg-white border-b border-slate-200 h-16 flex items-center justify-between px-8 sticky top-0 z-30">
            <h1 class="text-xl font-bold text-slate-800">
                Dashboard
            </h1>
            <div class="flex items-center gap-4">
                 <div class="flex flex-col items-end mr-2">
                     <span class="text-sm font-bold text-slate-700">{{ auth.user?.name }}</span>
                     <span class="text-xs text-slate-500 uppercase tracking-wider">Administrator</span>
                 </div>
                 <div class="h-10 w-10 rounded-full bg-slate-200 flex items-center justify-center text-slate-600 font-bold border border-slate-300">
                     {{ auth.user?.name?.[0] || 'A' }}
                 </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 overflow-auto p-8">
            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
                <StatCard title="Total Users" :value="stats?.total_users || 0" type="blue">
                    <template #icon><Users class="w-6 h-6" /></template>
                </StatCard>
                <StatCard title="Total Courses" :value="stats?.total_courses || 0" type="purple">
                     <template #icon><BookOpen class="w-6 h-6" /></template>
                </StatCard>
                <StatCard title="Total Revenue" :value="formatCurrency(stats?.total_revenue || 0)" type="success">
                     <template #icon><DollarSign class="w-6 h-6" /></template>
                </StatCard>
                <StatCard title="Pending Approval" :value="stats?.pending_courses || 0" type="warning">
                     <template #icon><Clock class="w-6 h-6" /></template>
                </StatCard>
            </div>

            <!-- Stats & Tables Split Layout -->
             <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                 <!-- Main Table Area -->
                 <div class="lg:col-span-2 space-y-6">
                     <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                         <div class="p-6 border-b border-slate-100 flex justify-between items-center">
                             <div>
                                 <h2 class="text-lg font-bold text-slate-800">Pending Courses</h2>
                                 <p class="text-sm text-slate-500 mt-1">Review and approve new course submissions</p>
                             </div>
                             <span class="px-3 py-1 bg-amber-50 text-amber-700 text-xs font-bold rounded-full border border-amber-100">
                                 {{ pendingCourses.length }} Waiting
                             </span>
                         </div>
                         
                         <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-slate-50/50 text-slate-500 text-xs uppercase tracking-wider border-b border-slate-100">
                                        <th class="px-6 py-4 font-semibold">Course Details</th>
                                        <th class="px-6 py-4 font-semibold">Instructor</th>
                                        <th class="px-6 py-4 font-semibold text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <tr v-for="course in pendingCourses" :key="course.id" class="group hover:bg-slate-50 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-4">
                                                <div class="h-10 w-16 bg-slate-200 rounded overflow-hidden flex-shrink-0">
                                                    <img v-if="course.thumbnail" :src="course.thumbnail" class="w-full h-full object-cover" />
                                                    <div v-else class="w-full h-full flex items-center justify-center text-slate-400">
                                                        <ImageIcon class="w-4 h-4" />
                                                    </div>
                                                </div>
                                                <div>
                                                    <div class="font-bold text-slate-800 group-hover:text-purple-600 transition-colors">{{ course.title }}</div>
                                                    <div class="text-xs text-slate-500 mt-0.5">{{ course.category?.name }} • {{ formatCurrency(course.price) }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-2">
                                                 <div class="h-6 w-6 rounded-full bg-slate-200 flex items-center justify-center text-xs text-slate-600 font-bold">
                                                     {{ course.instructor?.name?.[0] }}
                                                 </div>
                                                 <span class="text-sm font-medium text-slate-600">{{ course.instructor?.name }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                                <button @click="approve(course.id)" class="p-2 hover:bg-emerald-50 text-emerald-600 rounded-lg transition-colors" title="Approve">
                                                    <CheckCircle class="w-5 h-5" />
                                                </button>
                                                <button @click="reject(course.id)" class="p-2 hover:bg-red-50 text-red-600 rounded-lg transition-colors" title="Reject">
                                                    <XCircle class="w-5 h-5" />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="pendingCourses.length === 0">
                                        <td colspan="3" class="px-6 py-12 text-center">
                                            <div class="flex flex-col items-center justify-center text-slate-400">
                                                <CheckCircle class="w-12 h-12 mb-3 opacity-20" />
                                                <p class="text-sm font-medium">All caught up! No pending courses.</p>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                         </div>
                     </div>
                 </div>

                 <!-- Right Column (Quick Actions / Updates) -->
                 <div class="space-y-6">
                     <div class="bg-gradient-to-br from-purple-600 to-indigo-700 rounded-xl p-6 text-white shadow-lg">
                         <h3 class="text-lg font-bold mb-2">System Status</h3>
                         <div class="flex items-center gap-2 text-purple-100 text-sm mb-6">
                             <div :class="['w-2 h-2 rounded-full animate-pulse', stats?.system_health?.database ? 'bg-emerald-400' : 'bg-red-400']"></div>
                             {{ stats?.system_health?.database ? 'All systems operational' : 'Database Error' }}
                         </div>
                         <div class="space-y-3">
                              <div class="flex justify-between text-xs opacity-80 mb-1">
                                  <span>Disk Usage</span>
                                  <span>{{ stats ? Math.round((1 - (stats.system_health.disk_free / stats.system_health.disk_total)) * 100) : 0 }}%</span>
                              </div>
                              <div class="h-1.5 bg-purple-900/30 rounded-full overflow-hidden">
                                  <div class="h-full bg-emerald-400 rounded-full" :style="{ width: (stats ? Math.round((1 - (stats.system_health.disk_free / stats.system_health.disk_total)) * 100) : 0) + '%' }"></div>
                              </div>
                              
                              <div class="flex justify-between text-xs opacity-80 mb-1 mt-4">
                                  <span>Server Load</span>
                                  <span>{{ stats?.system_health?.server_load || 0 }}</span>
                              </div>
                              <div class="h-1.5 bg-purple-900/30 rounded-full overflow-hidden">
                                  <div class="h-full bg-amber-400 w-1/4 rounded-full"></div>
                              </div>
                         </div>
                     </div>

                     <!-- Recent Activity -->
                     <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                         <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wide mb-4">Recent Signups</h3>
                         <ul class="space-y-4">
                             <li v-for="user in stats?.recent_activity?.users" :key="user.id" class="flex items-center gap-3">
                                 <div class="h-8 w-8 rounded-full bg-slate-100 flex items-center justify-center text-xs font-bold text-slate-500">
                                     {{ user.name[0] }}
                                 </div>
                                 <div class="text-sm">
                                     <div class="font-medium text-slate-800">{{ user.name }}</div>
                                     <div class="text-xs text-slate-400">Joined {{ new Date(user.created_at).toLocaleDateString() }}</div>
                                 </div>
                             </li>
                             <li v-if="!stats?.recent_activity?.users?.length" class="text-sm text-slate-400 italic">No recent activity</li>
                         </ul>
                     </div>
                 </div>
             </div>
        </main>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import { useAuthStore } from '../stores/auth';
import AdminSidebar from '../components/admin/AdminSidebar.vue';
import StatCard from '../components/admin/StatCard.vue';
import { Users, BookOpen, DollarSign, Clock, CheckCircle, XCircle, Image as ImageIcon } from 'lucide-vue-next';

const auth = useAuthStore();
const router = useRouter();

const stats = ref(null);
const pendingCourses = ref([]);

onMounted(async () => {
    try {
        const statsRes = await axios.get('/api/admin/stats');
        stats.value = statsRes.data;
        
        const pendingRes = await axios.get('/api/admin/courses/pending');
        pendingCourses.value = pendingRes.data;
    } catch (e) {
        console.error(e);
    }
});

const formatCurrency = (val) => {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(val);
};

const approve = async (id) => {
    if (!confirm('Approve this course?')) return;
    try {
        await axios.post(`/api/admin/courses/${id}/approve`, { action: 'approve' });
        pendingCourses.value = pendingCourses.value.filter(c => c.id !== id);
        // Optimistic update stats (simplified)
        if (stats.value) stats.value.pending_courses--;
    } catch (e) {
        alert('Failed to approve course');
    }
};

const reject = async (id) => {
    const reason = prompt('Reason for rejection:');
    if (!reason) return;
    try {
        await axios.post(`/api/admin/courses/${id}/approve`, { action: 'reject', reason });
        pendingCourses.value = pendingCourses.value.filter(c => c.id !== id);
        if (stats.value) stats.value.pending_courses--;
    } catch (e) {
         alert('Failed to reject course');
    }
};

const handleLogout = () => {
    auth.logout();
    router.push('/login');
};
</script>
