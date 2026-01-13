<template>
    <div class="flex flex-col h-screen overflow-hidden bg-gray-900 text-white">
        <!-- Top Bar -->
        <header class="h-14 bg-gray-800 flex items-center px-4 border-b border-gray-700 shadow-md z-10 flex-shrink-0">
            <router-link to="/dashboard" class="mr-4 text-gray-400 hover:text-white">
                &larr; {{ $t('course.back_to_dashboard') }}
            </router-link>
            <h1 class="text-sm font-bold truncate flex-1">{{ currentLecture?.title || 'Loading...' }}</h1>
             <div class="ml-4">
                 <span class="text-xs text-gray-400">{{ $t('course.progress') }}: {{ progressPercent }}%</span>
             </div>
        </header>

        <div class="flex flex-1 overflow-hidden">
             <!-- Main Content Area -->
            <main class="flex-1 overflow-y-auto bg-black flex items-center justify-center relative">
                 <div v-if="currentLecture?.type === 'video'" class="w-full h-full max-w-5xl aspect-video mx-auto">
                     <div v-if="currentLecture.hls_path || currentLecture.video_url" class="unique-video-container w-full h-full relative">
                        <video 
                            ref="videoPlayer" 
                            class="video-js vjs-default-skin vjs-big-play-centered w-full h-full"
                            controls 
                            preload="auto"
                            data-setup='{}'
                        >
                            <source 
                                v-if="currentLecture.hls_path" 
                                :src="`/api/stream/${currentLecture.id}/playlist.m3u8`" 
                                type="application/x-mpegURL"
                            >
                            <source 
                                v-else 
                                :src="`/api/stream/${currentLecture.id}/${currentLecture.video_path.split('/').pop()}`" 
                                :type="getVideoType(currentLecture.video_path)"
                            >
                            <p class="vjs-no-js">
                                To view this video please enable JavaScript, and consider upgrading to a web browser that
                                <a href="https://videojs.com/html5-video-support/" target="_blank">supports HTML5 video</a>
                            </p>
                        </video>
                        <div v-if="currentLecture.processing_state === 'processing'" class="absolute inset-0 flex items-center justify-center bg-black/50 z-30">
                            <div class="text-center">
                                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-white mx-auto mb-4"></div>
                                <p>Video is processing... (You can watch the original meanwhile)</p>
                            </div>
                        </div>
                    </div>
                    <div v-else class="flex flex-col items-center justify-center h-full text-gray-500">
                        <p>{{ $t('course.video_not_available') }}</p>
                    </div>
                 </div>
                 <div v-else class="p-8 prose prose-invert mx-auto">
                     <h2>{{ currentLecture?.title }}</h2>
                     <div v-html="currentLecture?.content"></div>
                     <div class="mt-8 flex gap-4">
                        <button @click="markComplete" class="bg-purple-600 text-white px-6 py-2 rounded font-bold hover:bg-purple-700">
                            {{ isCompleted(currentLecture?.id) ? $t('course.mark_incomplete') : $t('course.mark_complete') }}
                        </button>
                        <button v-if="nextLecture" @click="goToLecture(nextLecture.id)" class="bg-gray-700 text-white px-6 py-2 rounded font-bold hover:bg-gray-600">
                            {{ $t('course.next_lecture') }} &rarr;
                        </button>
                     </div>
                 </div>
                 
                 <!-- Video Navigation Overlay (Optional, or below video) -->
                 <div v-if="currentLecture?.type === 'video'" class="absolute bottom-4 right-4 flex gap-2 z-20">
                    <button v-if="prevLecture" @click="goToLecture(prevLecture.id)" class="bg-gray-800/80 text-white px-4 py-2 rounded hover:bg-gray-700 backdrop-blur-sm">
                        &larr; {{ $t('course.previous') }}
                    </button>
                    <button v-if="nextLecture" @click="goToLecture(nextLecture.id)" class="bg-purple-600/90 text-white px-4 py-2 rounded hover:bg-purple-700 backdrop-blur-sm shadow-lg">
                        {{ $t('course.next_lecture') }} &rarr;
                    </button>
                 </div>
            </main>

            <!-- Sidebar -->
            <aside class="w-80 bg-gray-800 border-l border-gray-700 overflow-y-auto flex-shrink-0 flex flex-col">
                <!-- Tabs -->
                <div class="flex border-b border-gray-700">
                    <button @click="sidebarTab = 'content'" :class="sidebarTab === 'content' ? 'bg-gray-900 text-white' : 'text-gray-400 hover:text-white'" class="flex-1 py-3 text-sm font-bold">Content</button>
                    <button @click="sidebarTab = 'notes'" :class="sidebarTab === 'notes' ? 'bg-gray-900 text-white' : 'text-gray-400 hover:text-white'" class="flex-1 py-3 text-sm font-bold">Notes</button>
                    <button @click="sidebarTab = 'resources'" :class="sidebarTab === 'resources' ? 'bg-gray-900 text-white' : 'text-gray-400 hover:text-white'" class="flex-1 py-3 text-sm font-bold">Resources</button>
                </div>

                <!-- Content Tab -->
                <div v-if="sidebarTab === 'content'" class="flex-1 overflow-y-auto">
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
                                        :checked="isCompleted(lecture.id)" 
                                        class="mr-3 mt-1 cursor-pointer" 
                                        @click.stop="toggleLectureComplete(lecture.id)"
                                    >
                                    <div>
                                        <div>{{ lecture.title }}</div>
                                        <div class="text-xs text-gray-500 mt-1">{{ lecture.duration_minutes }} min</div>
                                    </div>
                                </router-link>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Notes Tab -->
                <div v-if="sidebarTab === 'notes'" class="flex-1 flex flex-col">
                    <div class="p-4 border-b border-gray-700">
                        <textarea 
                            v-model="newNoteContent" 
                            placeholder="Add a note..." 
                            class="w-full bg-gray-700 text-white rounded p-3 text-sm resize-none h-20 focus:ring-2 focus:ring-purple-500"
                        ></textarea>
                        <div class="flex justify-between items-center mt-2">
                            <button @click="addNoteAtCurrentTime" class="text-xs text-purple-400 hover:text-purple-300">
                                📍 Add at {{ getCurrentTimestamp() }}
                            </button>
                            <button @click="saveNote" :disabled="!newNoteContent.trim()" class="bg-purple-600 text-white px-4 py-1 rounded text-sm font-bold hover:bg-purple-700 disabled:opacity-50">
                                Save
                            </button>
                        </div>
                    </div>
                    <div class="flex-1 overflow-y-auto p-4 space-y-3">
                        <div v-for="note in notes" :key="note.id" class="bg-gray-700 rounded-lg p-3">
                            <div class="flex justify-between items-start mb-2">
                                <button v-if="note.video_timestamp" @click="seekToTimestamp(note.video_timestamp)" class="text-purple-400 text-xs hover:underline">
                                    ⏱️ {{ formatTimestamp(note.video_timestamp) }}
                                </button>
                                <span v-else class="text-gray-500 text-xs">{{ note.lecture?.title || 'General' }}</span>
                                <button @click="deleteNote(note.id)" class="text-red-400 hover:text-red-300 text-xs">Delete</button>
                            </div>
                            <p class="text-sm text-gray-200">{{ note.content }}</p>
                        </div>
                        <div v-if="notes.length === 0" class="text-center text-gray-500 py-8">
                            No notes yet. Start taking notes!
                        </div>
                    </div>
                </div>

                <!-- Resources Tab -->
                <div v-if="sidebarTab === 'resources'" class="flex-1 overflow-y-auto">
                    <div v-if="currentLectureResources.length > 0" class="p-4 space-y-2">
                        <a 
                            v-for="resource in currentLectureResources" 
                            :key="resource.id"
                            :href="`/api/lectures/${currentLectureId}/resources/${resource.id}/download`"
                            target="_blank"
                            class="flex items-center gap-3 bg-gray-700 p-3 rounded-lg hover:bg-gray-600 transition"
                        >
                            <span class="text-2xl">📄</span>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-medium truncate">{{ resource.title }}</div>
                                <div class="text-xs text-gray-400">{{ resource.file_type?.toUpperCase() }} • {{ formatFileSize(resource.file_size) }}</div>
                            </div>
                            <span class="text-purple-400">⬇️</span>
                        </a>
                    </div>
                    <div v-else class="text-center text-gray-500 py-8 px-4">
                        No downloadable resources for this lecture.
                    </div>
                </div>
            </aside>
        </div>
        </div>

        <!-- Completion Modal -->
        <div v-if="showCompletionModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
            <div class="bg-white text-gray-900 rounded-2xl max-w-md w-full p-8 text-center shadow-2xl animate-fade-in relative">
                <button @click="showCompletionModal = false" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
                <div class="w-20 h-20 bg-green-100 text-green-500 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h2 class="text-2xl font-bold mb-2">{{ $t('course.certificate.completed_title') }}</h2>
                <p class="text-gray-600 mb-8">{{ $t('course.certificate.congratulations') }}</p>
                
                <div class="space-y-3">
                    <button 
                        @click="downloadCertificate" 
                        :disabled="generatingCertificate || !certificate || downloadingCertificate"
                        class="w-full py-3 px-4 bg-purple-600 hover:bg-purple-700 text-white font-bold rounded-xl transition-colors flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <svg v-if="!generatingCertificate && !downloadingCertificate" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <svg v-else class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ generatingCertificate ? $t('course.certificate.generating') : (downloadingCertificate ? 'Downloading...' : $t('course.certificate.download')) }}
                    </button>
                </div>
            </div>
        </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, watch, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useCourseStore } from '../stores/course';
import { useLearningStore } from '../stores/learning';
import { useI18n } from 'vue-i18n';
import axios from 'axios';
import videojs from 'video.js';
import 'video.js/dist/video-js.css';

const { t } = useI18n();

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

// Helper to check completion with type safety
const isCompleted = (lectureId) => {
    return learningStore.currentProgress.some(id => parseInt(id) === parseInt(lectureId));
};

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



const toggleLectureComplete = async (lectureId) => {
    try {
        await learningStore.markComplete(lectureId, !isCompleted(lectureId));
        checkCompletion();
    } catch (error) {
        console.error('Failed to update progress', error);
    }
};

const markComplete = () => toggleLectureComplete(currentLectureId.value);

// Certificate & Completion
const showCompletionModal = ref(false);
const generatingCertificate = ref(false);
const downloadingCertificate = ref(false);
const certificate = ref(null);

const checkCompletion = () => {
    // Timeout to allow state update
    setTimeout(() => {
        if (progressPercent.value === 100) {
            showCompletionModal.value = true;
            generateCertificate();
        }
    }, 500);
};

// Video Player State
const videoPlayer = ref(null);
const player = ref(null);

const initPlayer = () => {
    if (player.value) {
        player.value.dispose();
        player.value = null;
    }

    if (videoPlayer.value && (currentLecture.value.hls_path || currentLecture.value.video_url)) {
        player.value = videojs(videoPlayer.value, {
            controls: true,
            autoplay: true,
            preload: 'auto',
            fluid: true,
            sources: currentLecture.value.hls_path 
                ? [{
                    src: `/api/stream/${currentLecture.value.id}/playlist.m3u8`,
                    type: 'application/x-mpegURL',
                    withCredentials: true 
                }]
                : [{
                    src: `/api/stream/${currentLecture.value.id}/${currentLecture.value.video_path.split('/').pop()}`,
                    type: getVideoType(currentLecture.value.video_path),
                    withCredentials: true
                }]
        });

        player.value.on('ended', onVideoEnded);
    }
};

onUnmounted(() => {
    if (player.value) {
        player.value.dispose();
    }
});

watch(currentLecture, () => {
    // Wait for DOM
    setTimeout(() => {
        initPlayer();
    }, 100);
}, { deep: true });

// Return immediately if already completed on mount
onMounted(async () => {
    try {
        await courseStore.fetchCourseDetail(props.courseSlug);
        
        if (course.value) {
            await learningStore.fetchProgress(course.value.id);
            // Check completion immediately after fetching progress
            if (progressPercent.value === 100) {
                 showCompletionModal.value = true;
                 generateCertificate();
            }
        }
        
        if (!props.lectureId && course.value?.sections?.[0]?.lectures?.[0]) {
             router.replace(`/learn/course/${props.courseSlug}/lecture/${course.value.sections[0].lectures[0].id}`);
        }
    } catch (e) {
        console.error("Error loading course content", e);
    }
});

// Watch for progress changes
watch(progressPercent, (newPercent) => {
    if (newPercent === 100) {
        showCompletionModal.value = true;
        generateCertificate();
    }
});

const generateCertificate = async () => {
    if (certificate.value) return; // Already generated
    
    try {
        generatingCertificate.value = true;
        const response = await axios.post(`/api/courses/${course.value.id}/certificate`);
        certificate.value = response.data.certificate;
    } catch (error) {
        // If already exists, we might get it back or 403, handle gracefully
        if (error.response?.data?.certificate) {
             certificate.value = error.response.data.certificate;
        } else {
             console.error('Certificate generation failed', error);
        }
    } finally {
        generatingCertificate.value = false;
    }
};

const downloadCertificate = async () => {
    if (!certificate.value) return;
    
    try {
        downloadingCertificate.value = true;
        // 1. Get signed URL
        const response = await axios.get(`/api/certificates/${certificate.value.id}/download-url`);
        const downloadUrl = response.data.url;
        
        // 2. Open URL (Standard navigation, less likely to be blocked)
        window.location.href = downloadUrl;
    } catch (error) {
        console.error('Download failed', error);
    } finally {
        downloadingCertificate.value = false;
    }
};

// ==================== NOTES & RESOURCES ====================
const sidebarTab = ref('content');
const notes = ref([]);
const newNoteContent = ref('');
const noteTimestamp = ref(null);
const currentLectureResources = ref([]);

// Load notes when course loads
watch(course, async (newCourse) => {
    if (newCourse?.id) {
        await loadNotes();
    }
}, { immediate: true });

// Load resources when lecture changes
watch(currentLectureId, async (newId) => {
    if (newId && course.value) {
        await loadResources();
    }
}, { immediate: true });

const loadNotes = async () => {
    if (!course.value) return;
    try {
        const res = await axios.get(`/api/courses/${course.value.id}/notes`);
        notes.value = res.data;
    } catch (e) {
        console.error('Failed to load notes', e);
    }
};

const loadResources = async () => {
    if (!currentLecture.value) {
        currentLectureResources.value = [];
        return;
    }
    try {
        // Find the section for this lecture
        const section = course.value.sections.find(s => s.lectures.some(l => l.id === currentLectureId.value));
        if (section) {
            const res = await axios.get(`/api/courses/${course.value.id}/sections/${section.id}/lectures/${currentLectureId.value}/resources`);
            currentLectureResources.value = res.data;
        }
    } catch (e) {
        console.error('Failed to load resources', e);
        currentLectureResources.value = [];
    }
};

const saveNote = async () => {
    if (!newNoteContent.value.trim()) return;
    try {
        const res = await axios.post(`/api/courses/${course.value.id}/notes`, {
            content: newNoteContent.value,
            lecture_id: currentLectureId.value,
            video_timestamp: noteTimestamp.value,
        });
        notes.value.unshift(res.data);
        newNoteContent.value = '';
        noteTimestamp.value = null;
    } catch (e) {
        console.error('Failed to save note', e);
    }
};

const deleteNote = async (noteId) => {
    try {
        await axios.delete(`/api/courses/${course.value.id}/notes/${noteId}`);
        notes.value = notes.value.filter(n => n.id !== noteId);
    } catch (e) {
        console.error('Failed to delete note', e);
    }
};

const addNoteAtCurrentTime = () => {
    let currentTime = 0;
    if (player.value) {
        currentTime = player.value.currentTime();
    } else {
        const video = document.querySelector('video');
        if (video) currentTime = video.currentTime;
    }
    noteTimestamp.value = Math.floor(currentTime);
};

const getCurrentTimestamp = () => {
    let currentTime = 0;
    if (player.value) {
        currentTime = player.value.currentTime();
    } else {
        const video = document.querySelector('video');
        if (video) currentTime = video.currentTime;
    }
    return formatTimestamp(Math.floor(currentTime));
};

const seekToTimestamp = (seconds) => {
    if (player.value) {
        player.value.currentTime(seconds);
        player.value.play();
    } else {
        const video = document.querySelector('video');
        if (video) {
            video.currentTime = seconds;
            video.play();
        }
    }
};

const getVideoType = (url) => {
    if (!url) return '';
    const ext = url.split('.').pop().toLowerCase();
    if (ext === 'm3u8') return 'application/x-mpegURL';
    return `video/${ext}`;
};

const formatTimestamp = (seconds) => {
    if (!seconds) return '0:00';
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${mins}:${secs.toString().padStart(2, '0')}`;
};

const formatFileSize = (bytes) => {
    if (!bytes) return '0 B';
    if (bytes >= 1048576) return (bytes / 1048576).toFixed(1) + ' MB';
    if (bytes >= 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return bytes + ' B';
};
</script>


