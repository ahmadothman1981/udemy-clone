<template>
    <div>
        <Navbar />
        <div class="bg-gray-900 text-white py-12 mb-8">
            <div class="max-w-7xl mx-auto px-4">
                <h1 class="text-3xl font-bold">My Learning</h1>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 pb-12">
            <div v-for="course in learningStore.enrolledCourses" :key="course.id" class="flex flex-col md:flex-row bg-white border border-gray-200 rounded-lg overflow-hidden shadow-sm hover:shadow-md transition mb-6">
                 <div class="w-full md:w-64 h-40 bg-gray-200 flex-shrink-0 overflow-hidden">
                     <img v-if="course.thumbnail" :src="course.thumbnail" :alt="course.title" class="w-full h-full object-cover">
                     <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
                         <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                         </svg>
                     </div>
                 </div>
                 <div class="p-6 flex-1 flex flex-col">
                     <h3 class="font-bold text-lg mb-2">{{ course.title }}</h3>
                     <p class="text-sm text-gray-500 mb-4">{{ course.instructor?.name }}</p>
                     
                     <div class="mt-auto">
                         <div class="w-full bg-gray-200 rounded-full h-2 mb-2">
                             <div class="bg-purple-600 h-2 rounded-full" style="width: 25%"></div>
                         </div>
                         <router-link :to="`/learn/course/${course.slug || course.id}`" class="text-purple-600 font-bold hover:underline">
                             Continue Learning
                         </router-link>
                     </div>
                 </div>
            </div>
             <div v-if="learningStore.enrolledCourses.length === 0" class="text-center py-12 text-gray-500">
                You haven't enrolled in any courses yet.
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { useLearningStore } from '../stores/learning';
import Navbar from '../components/Navbar.vue';

const learningStore = useLearningStore();

onMounted(() => {
    learningStore.fetchMyCourses();
});
</script>
