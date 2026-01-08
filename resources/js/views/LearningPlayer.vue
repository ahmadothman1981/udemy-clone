<template>
    <div class="flex flex-col h-screen overflow-hidden bg-gray-900 text-white">
        <!-- Top Bar -->
        <header class="h-14 bg-gray-800 flex items-center px-4 border-b border-gray-700 shadow-md z-10 flex-shrink-0">
            <router-link :to="`/course/${course?.slug || courseId}`" class="mr-4 text-gray-400 hover:text-white">
                &larr; Back
            </router-link>
            <h1 class="text-sm font-bold truncate flex-1">{{ currentLecture?.title || 'Loading...' }}</h1>
             <div class="ml-4">
                 <span class="text-xs text-gray-400">Progress: {{ progressPercent }}%</span>
             </div>
        </header>

        <div class="flex flex-1 overflow-hidden">
             <!-- Main Content Area -->
            <main class="flex-1 overflow-y-auto bg-black flex items-center justify-center relative">
                 <div v-if="currentLecture?.type === 'video'" class="w-full h-full max-w-5xl aspect-video mx-auto">
                     <video 
                        v-if="currentLecture.video_url"
                        controls 
                        autoplay
                        class="w-full h-full"
                        @ended="markComplete"
                        :src="currentLecture.video_url"
                    ></video>
                    <div v-else class="flex flex-col items-center justify-center h-full text-gray-500">
                        <p>Video not available</p>
                    </div>
                 </div>
                 <div v-else class="p-8 prose prose-invert mx-auto">
                     <h2>{{ currentLecture?.title }}</h2>
                     <div v-html="currentLecture?.content"></div>
                     <button @click="markComplete" class="mt-8 bg-purple-600 px-6 py-2 rounded">Mark Complete</button>
                 </div>
            </main>

            <!-- Sidebar -->
            <aside class="w-80 bg-gray-800 border-l border-gray-700 overflow-y-auto flex-shrink-0">
                 <div class="p-4 border-b border-gray-700 font-bold">Course Content</div>
                 <div v-for="section in course?.sections" :key="section.id">
                     <div class="bg-gray-700 px-4 py-2 text-sm font-bold border-b border-gray-600">
                         {{ section.title }}
                     </div>
                     <ul>
                         <li v-for="lecture in section.lectures" :key="lecture.id">
                             <router-link 
                                :to="`/learn/course/${course?.slug || courseId}/lecture/${lecture.id}`"
                                class="block px-4 py-3 text-sm hover:bg-gray-700 border-b border-gray-700 flex items-start"
                                :class="{ 'bg-gray-900 border-l-4 border-l-purple-500': lecture.id == currentLectureId }"
                             >
                                 <input 
                                    type="checkbox" 
                                    :checked="learningStore.currentProgress.includes(lecture.id)" 
                                    class="mr-3 mt-1" 
                                    @click.stop
                                    disabled
                                >
                                 <div>
                                     <div>{{ lecture.title }}</div>
                                     <div class="text-xs text-gray-500 mt-1">{{ lecture.duration_minutes }} min</div>
                                 </div>
                             </router-link>
                         </li>
                     </ul>
                 </div>
            </aside>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useCourseStore } from '../stores/course';
import { useLearningStore } from '../stores/learning';

const props = defineProps(['courseId', 'lectureId']);
const route = useRoute();
const router = useRouter(); // Import router if needed
const courseStore = useCourseStore();
const learningStore = useLearningStore();

const course = computed(() => courseStore.currentCourse);
const currentLectureId = computed(() => parseInt(props.lectureId) || course.value?.sections?.[0]?.lectures?.[0]?.id);

const currentLecture = computed(() => {
    if (!course.value) return null;
    for (const section of course.value.sections) {
        const found = section.lectures.find(l => l.id === currentLectureId.value);
        if (found) return found;
    }
    return null;
});

const progressPercent = computed(() => {
    // simplified calc
    return 0; 
});

onMounted(async () => {
    await courseStore.fetchCourseDetail(props.courseId);
    await learningStore.fetchProgress(props.courseId);
    
    // Redirect to first lecture if no lecture ID
    if (!props.lectureId && course.value?.sections?.[0]?.lectures?.[0]) {
         // This logic inside script setup with props sometimes tricky with router lifecycle
    }
});

const markComplete = async () => {
    await learningStore.markComplete(currentLectureId.value, true);
};
</script>
