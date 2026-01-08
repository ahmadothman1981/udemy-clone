<template>
  <div class="bg-gray-100 min-h-screen">
    <Navbar />
    <div class="flex">
        <!-- Sidebar -->
        <aside class="w-64 bg-gray-900 text-white min-h-screen px-4 py-6">
            <h2 class="font-bold text-gray-400 uppercase tracking-wide text-xs mb-6">Admin Panel</h2>
            <ul class="space-y-4">
                <li><router-link to="/admin" class="block hover:text-purple-400 font-bold">Dashboard</router-link></li>
                <li><a href="#" class="block hover:text-purple-400 opacity-50 cursor-not-allowed">Moderation (Soon)</a></li>
                <li><a href="#" class="block hover:text-purple-400 opacity-50 cursor-not-allowed">User Management (Soon)</a></li>
                <li><a href="#" class="block hover:text-purple-400 opacity-50 cursor-not-allowed">Settings (Soon)</a></li>
                <li class="pt-4 border-t border-gray-700">
                    <button @click="handleLogout" class="block w-full text-left hover:text-red-400">Logout</button>
                </li>
            </ul>
        </aside>

        <!-- Content -->
        <main class="flex-1 p-8">
            <h1 class="text-3xl font-bold mb-8">Admin Dashboard</h1>

            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8" v-if="stats">
                <div class="bg-white p-6 rounded shadow-sm">
                    <div class="text-sm text-gray-500">Total Users</div>
                    <div class="text-2xl font-bold">{{ stats.total_users }}</div>
                </div>
                <div class="bg-white p-6 rounded shadow-sm">
                    <div class="text-sm text-gray-500">Total Courses</div>
                    <div class="text-2xl font-bold">{{ stats.total_courses }}</div>
                </div>
                <div class="bg-white p-6 rounded shadow-sm">
                    <div class="text-sm text-gray-500">Revenue</div>
                    <div class="text-2xl font-bold">${{ stats.total_revenue }}</div>
                </div>
                 <div class="bg-white p-6 rounded shadow-sm border-l-4 border-yellow-400">
                    <div class="text-sm text-gray-500">Pending Approval</div>
                    <div class="text-2xl font-bold">{{ stats.pending_courses }}</div>
                </div>
            </div>
            
            <!-- Pending Courses -->
            <h2 class="text-xl font-bold mb-4">Pending Courses</h2>
            <div class="bg-white rounded shadow-sm overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Course</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Instructor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <tr v-for="course in pendingCourses" :key="course.id">
                           <td class="px-6 py-4">
                               <div class="font-medium text-gray-900">{{ course.title }}</div>
                               <div class="text-xs text-gray-500">Created {{ new Date(course.created_at).toLocaleDateString() }}</div>
                           </td>
                           <td class="px-6 py-4 text-sm">{{ course.instructor?.name }}</td>
                           <td class="px-6 py-4 text-sm">{{ course.category?.name }}</td>
                           <td class="px-6 py-4 text-right space-x-2">
                               <button @click="approve(course.id)" class="text-green-600 hover:text-green-900 font-bold text-xs uppercase">Approve</button>
                               <button @click="reject(course.id)" class="text-red-600 hover:text-red-900 font-bold text-xs uppercase">Reject</button>
                           </td>
                        </tr>
                         <tr v-if="pendingCourses.length === 0">
                             <td colspan="4" class="px-6 py-4 text-center text-gray-500 italic">No courses pending approval.</td>
                         </tr>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import Navbar from '../components/Navbar.vue';
import { useAuthStore } from '../stores/auth';

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

const approve = async (id) => {
    if (!confirm('Approve this course?')) return;
    await axios.post(`/api/admin/courses/${id}/approve`, { action: 'approve' });
    pendingCourses.value = pendingCourses.value.filter(c => c.id !== id);
};

const reject = async (id) => {
    const reason = prompt('Reason for rejection:');
    if (!reason) return;
    await axios.post(`/api/admin/courses/${id}/approve`, { action: 'reject', reason });
    pendingCourses.value = pendingCourses.value.filter(c => c.id !== id);
};

const handleLogout = () => {
    auth.logout();
    router.push('/login');
};
</script>
