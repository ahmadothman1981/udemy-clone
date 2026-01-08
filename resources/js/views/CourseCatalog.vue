<template>
    <div>
        <Navbar />
        <div class="max-w-7xl mx-auto px-4 py-8">
            <h1 class="text-2xl font-bold mb-6">Course Catalog</h1>
            <div v-if="courseStore.loading" class="text-center py-10">Loading...</div>
            <div v-else>
                <div v-if="courseStore.courses.length === 0" class="text-center py-20">
                    <h3 class="text-lg font-bold text-gray-700">No courses found</h3>
                    <p class="text-gray-500">Try adjusting your search terms.</p>
                </div>
                <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <CourseCard 
                        v-for="course in courseStore.courses" 
                        :key="course.id" 
                        :course="course" 
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted, watch } from 'vue';
import { useRoute } from 'vue-router';
import { useCourseStore } from '../stores/course';
import Navbar from '../components/Navbar.vue';
import CourseCard from '../components/CourseCard.vue';

const courseStore = useCourseStore();
const route = useRoute();

const loadCourses = () => {
    courseStore.fetchCourses(route.query);
};

onMounted(() => {
    loadCourses();
});

watch(() => route.query, () => {
    loadCourses();
});
</script>
