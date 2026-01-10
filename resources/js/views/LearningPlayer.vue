<template>
    <div class="flex flex-col h-screen overflow-hidden bg-gray-900 text-white">
        <!-- Top Bar -->
        <header class="h-14 bg-gray-800 flex items-center px-4 border-b border-gray-700 shadow-md z-10 flex-shrink-0">
            <router-link to="/dashboard" class="mr-4 text-gray-400 hover:text-white">
                &larr; Back to Dashboard
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
                        @ended="onVideoEnded"
                        :src="currentLecture.video_url"
                    ></video>
                    <div v-else class="flex flex-col items-center justify-center h-full text-gray-500">
                        <p>Video not available</p>
                    </div>
                 </div>
                 <div v-else class="p-8 prose prose-invert mx-auto">
                     <h2>{{ currentLecture?.title }}</h2>
                     <div v-html="currentLecture?.content"></div>
                     <div class="mt-8 flex gap-4">
                        <button @click="markComplete" class="bg-purple-600 text-white px-6 py-2 rounded font-bold hover:bg-purple-700">
                            {{ isCompleted(currentLecture?.id) ? 'Mark Incomplete' : 'Mark Complete' }}
                        </button>
                        <button v-if="nextLecture" @click="goToLecture(nextLecture.id)" class="bg-gray-700 text-white px-6 py-2 rounded font-bold hover:bg-gray-600">
                            Next Lecture &rarr;
                        </button>
                     </div>
                 </div>
                 
                 <!-- Video Navigation Overlay (Optional, or below video) -->
                 <div v-if="currentLecture?.type === 'video'" class="absolute bottom-4 right-4 flex gap-2 z-20">
                    <button v-if="prevLecture" @click="goToLecture(prevLecture.id)" class="bg-gray-800/80 text-white px-4 py-2 rounded hover:bg-gray-700 backdrop-blur-sm">
                        &larr; Previous
                    </button>
                    <button v-if="nextLecture" @click="goToLecture(nextLecture.id)" class="bg-purple-600/90 text-white px-4 py-2 rounded hover:bg-purple-700 backdrop-blur-sm shadow-lg">
                        Next &rarr;
                    </button>
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
                                :to="`/learn/course/${course?.slug || courseSlug}/lecture/${lecture.id}`"
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

const props = defineProps(['courseSlug', 'lectureId']);
const route = useRoute();
const router = useRouter(); // Import router if needed
const courseStore = useCourseStore();
const learningStore = useLearningStore();

const course = computed(() => courseStore.currentCourse);
const currentLectureId = computed(() => parseInt(props.lectureId) || course.value?.sections?.[0]?.lectures?.[0]?.id);

const currentLecture = computed(() => {
    if (!course.value || !course.value.sections) return null;
    for (const section of course.value.sections) {
        const found = section.lectures.find(l => l.id === currentLectureId.value);
        if (found) return found;
    }
    return null;
});

const progressPercent = computed(() => {
    if (!course.value?.sections) return 0;
    
    let totalLectures = 0;
    course.value.sections.forEach(s => totalLectures += s.lectures.length);
    
    if (totalLectures === 0) return 0;
    
    return Math.round((learningStore.currentProgress.length / totalLectures) * 100);
});

const allLectures = computed(() => {
    if (!course.value?.sections) return [];
    return course.value.sections.flatMap(s => s.lectures);
});

const nextLecture = computed(() => {
    const list = allLectures.value;
    const idx = list.findIndex(l => l.id === currentLectureId.value);
    return (idx !== -1 && idx < list.length - 1) ? list[idx + 1] : null;
});

const prevLecture = computed(() => {
    const list = allLectures.value;
    const idx = list.findIndex(l => l.id === currentLectureId.value);
    return (idx !== -1 && idx > 0) ? list[idx - 1] : null;
});

const isCompleted = (lectureId) => learningStore.currentProgress.includes(lectureId);

const goToLecture = (lectureId) => {
    router.push(`/learn/course/${props.courseSlug}/lecture/${lectureId}`);
};

const onVideoEnded = async () => {
    await markComplete();
    if (nextLecture.value) {
        // Optional: Add a small delay or user preference check
        goToLecture(nextLecture.value.id);
    }
};

onMounted(async () => {
    try {
        // Fetch using slug
        await courseStore.fetchCourseDetail(props.courseSlug);
        // Note: Learning store might need course ID for specific tracking if not using slug, 
        // but usually we need the course object first to get the ID if the store requires ID.
        // If learningStore.fetchProgress expects ID, we should wait for course to load.
        
        if (course.value) {
            await learningStore.fetchProgress(course.value.id);
        }
        
        // Redirect to first lecture if no lecture ID
        if (!props.lectureId && course.value?.sections?.[0]?.lectures?.[0]) {
             // Ideally replace URL
             router.replace(`/learn/course/${props.courseSlug}/lecture/${course.value.sections[0].lectures[0].id}`);
        }
    } catch (e) {
        console.error("Error loading course content", e);
    }
});

const markComplete = async () => {
    await learningStore.markComplete(currentLectureId.value, true);
};
</script>
