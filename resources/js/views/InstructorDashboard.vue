<template>
  <div>
    <Navbar />
    <div class="flex max-w-7xl mx-auto px-4 py-8 min-h-screen">
        <!-- Sidebar -->
        <aside class="w-64 flex-shrink-0 pr-8 hidden md:block">
            <h2 class="font-bold text-gray-500 uppercase tracking-wide text-sm mb-4">Instructor</h2>
            <ul class="space-y-2">
                <li><a href="#" class="block text-purple-600 font-bold">Dashboard</a></li>
                <li><router-link to="/instructor" class="block text-gray-600 hover:text-gray-900">Courses</router-link></li>
                <li><a href="#" class="block text-gray-600 hover:text-gray-900">Communication</a></li>
                <li><a href="#" class="block text-gray-600 hover:text-gray-900">Performance</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <div class="flex-1">
            <h1 class="text-2xl font-bold mb-6">Overview</h1>
            
            <div v-if="stats" class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white p-6 rounded shadow-sm border border-gray-200">
                    <div class="text-sm text-gray-500 mb-1">Total Revenue</div>
                    <div class="text-2xl font-bold text-gray-900">${{ stats.total_revenue }}</div>
                </div>
                 <div class="bg-white p-6 rounded shadow-sm border border-gray-200">
                    <div class="text-sm text-gray-500 mb-1">Total Enrollments</div>
                    <div class="text-2xl font-bold text-gray-900">{{ stats.total_students }}</div>
                </div>
                 <div class="bg-white p-6 rounded shadow-sm border border-gray-200">
                    <div class="text-sm text-gray-500 mb-1">Instructor Rating</div>
                    <div class="text-2xl font-bold text-gray-900">{{ stats.average_rating }} / 5.0</div>
                    <div class="text-xs text-gray-400 mt-1">{{ stats.total_reviews }} reviews</div>
                </div>
            </div>
            
            <h2 class="text-xl font-bold mb-4">Your Courses</h2>
            <div class="bg-white border border-gray-200 rounded">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Course</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Students</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rating</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="course in courses" :key="course.id">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ course.title }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ course.enrollments_count }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ course.rating_avg }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="course.published ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'">
                                    {{ course.published ? 'Live' : 'Draft' }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import Navbar from '../components/Navbar.vue';

const stats = ref(null);
const courses = ref([]);

onMounted(async () => {
    try {
        const statsRes = await axios.get('/api/instructor/dashboard');
        stats.value = statsRes.data;
        
        const coursesRes = await axios.get('/api/instructor/courses');
        courses.value = coursesRes.data;
    } catch (e) {
        console.error("Not an instructor or err", e);
    }
});
</script>
