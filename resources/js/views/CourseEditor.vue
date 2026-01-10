<template>
  <div class="min-h-screen bg-gray-50">
    <Navbar />
    
    <div v-if="loading" class="flex justify-center items-center h-64">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-purple-600"></div>
    </div>

    <div v-else class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
          <router-link to="/instructor" class="hover:text-purple-600">Instructor Dashboard</router-link>
          <span>/</span>
          <span>Edit Course</span>
        </div>
        <div class="flex justify-between items-start">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ course.title }}</h1>
            <p class="text-gray-500 mt-1">Curriculum Manager</p>
          </div>
          <div class="flex gap-3">
             <router-link :to="`/course/${course.slug}`" target="_blank" class="px-4 py-2 text-purple-600 font-medium hover:bg-purple-50 rounded-lg transition-colors">
              Preview Course
            </router-link>
            <button class="px-4 py-2 bg-purple-600 text-white font-medium rounded-lg hover:bg-purple-700 transition-colors">
              Save Changes
            </button>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Sidebar: Sections List -->
        <div class="lg:col-span-1 space-y-6">
          <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <div class="flex justify-between items-center mb-4">
              <h2 class="font-bold text-gray-900">Sections</h2>
              <button @click="addSection" class="text-sm text-purple-600 font-semibold hover:text-purple-700">
                + Add Section
              </button>
            </div>
            
            <draggable 
              v-model="sections" 
              item-key="id"
              handle=".drag-handle"
              @end="reorderSections"
              class="space-y-3"
            >
              <template #item="{ element: section }">
                <div 
                  class="group p-3 rounded-lg border transition-all cursor-pointer"
                  :class="selectedSection?.id === section.id ? 'border-purple-500 bg-purple-50' : 'border-gray-200 hover:border-purple-200'"
                  @click="selectSection(section)"
                >
                  <div class="flex items-center gap-3">
                    <div class="drag-handle cursor-move text-gray-400 hover:text-gray-600">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16" />
                      </svg>
                    </div>
                    
                    <div class="flex-1 min-w-0" v-if="editingSectionId !== section.id">
                      <h3 class="font-medium text-gray-900 truncate">{{ section.title }}</h3>
                      <p class="text-xs text-gray-500">{{ section.lectures?.length || 0 }} lectures</p>
                    </div>
                    <input 
                      v-else
                      v-model="section.title"
                      @blur="updateSection(section)"
                      @keyup.enter="updateSection(section)"
                      ref="sectionInput"
                      class="flex-1 px-2 py-1 text-sm border rounded focus:ring-2 focus:ring-purple-500 outline-none"
                    >

                    <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                      <button @click.stop="editSection(section)" class="p-1 text-gray-400 hover:text-blue-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                        </svg>
                      </button>
                      <button @click.stop="deleteSection(section)" class="p-1 text-gray-400 hover:text-red-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                      </button>
                    </div>
                  </div>
                </div>
              </template>
            </draggable>
          </div>
        </div>

        <!-- Main Content: Lecture Editor -->
        <div class="lg:col-span-2">
          <div v-if="selectedSection" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex justify-between items-center mb-6">
              <div>
                <h2 class="text-xl font-bold text-gray-900">{{ selectedSection.title }}</h2>
                <p class="text-sm text-gray-500">Manage lectures and content</p>
              </div>
              <button @click="showAddLecture = true" class="px-4 py-2 bg-purple-50 text-purple-700 font-medium rounded-lg hover:bg-purple-100 transition-colors">
                + Add Content
              </button>
            </div>

            <draggable 
              v-model="lectures" 
              item-key="id"
              handle=".lecture-drag"
              @end="reorderLectures"
              class="space-y-4"
            >
              <template #item="{ element: lecture }">
                <div class="border border-gray-200 rounded-lg p-4 bg-gray-50 hover:bg-white transition-colors">
                  <div class="flex items-start gap-4">
                    <div class="lecture-drag mt-1 cursor-move text-gray-400 hover:text-gray-600">
                       <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16" />
                      </svg>
                    </div>

                    <div class="flex-1 min-w-0">
                      <div class="flex justify-between items-start mb-2">
                        <div>
                           <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 text-xs font-semibold rounded bg-gray-200 text-gray-700 uppercase">{{ lecture.type }}</span>
                            <h3 class="font-semibold text-gray-900">{{ lecture.title }}</h3>
                          </div>
                        </div>
                        <div class="flex gap-2">
                           <button @click="editLecture(lecture)" class="text-sm text-blue-600 hover:underline">Edit</button>
                           <button @click="deleteLecture(lecture)" class="text-sm text-red-600 hover:underline">Delete</button>
                        </div>
                      </div>

                      <!-- Content Preview / Status -->
                      <div class="text-sm text-gray-600 pl-2 border-l-2 border-gray-300">
                        <div v-if="lecture.type === 'video'">
                          <div v-if="lecture.video_url" class="flex items-center gap-2 text-green-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Video uploaded
                          </div>
                          <span v-else class="text-amber-600">No video uploaded yet</span>
                        </div>
                        <div v-else-if="lecture.type === 'article'">
                           <p class="line-clamp-2">{{ lecture.content || 'No content added' }}</p>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </template>
            </draggable>

            <div v-if="lectures.length === 0" class="text-center py-12 text-gray-500 border-2 border-dashed border-gray-200 rounded-lg">
              No lectures in this section yet.
            </div>
          </div>

          <div v-else class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
            <div class="inline-block p-4 bg-purple-50 rounded-full mb-4">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
              </svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900">Select a section</h3>
            <p class="text-gray-500">Click on a section from the sidebar to manage its content.</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Modals -->
    <!-- Add/Edit Lecture Modal -->
    <div v-if="showAddLecture || editingLecture" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeLectureModal"></div>
      <div class="relative bg-white rounded-2xl shadow-xl max-w-2xl w-full p-6 animate-fade-in">
        <h2 class="text-2xl font-bold mb-6">{{ editingLecture ? 'Edit Lecture' : 'Add New Lecture' }}</h2>
        
        <form @submit.prevent="saveLecture">
          <div class="mb-4">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Title</label>
            <input v-model="lectureForm.title" type="text" class="input-field" required>
          </div>

          <div class="mb-4">
             <label class="block text-sm font-semibold text-gray-700 mb-2">Type</label>
             <select v-model="lectureForm.type" class="input-field">
               <option value="video">Video</option>
               <option value="article">Article</option>
               <option value="quiz">Quiz</option>
             </select>
          </div>

          <!-- Video Upload -->
          <div v-if="lectureForm.type === 'video'" class="mb-6">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Video File</label>
            <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:bg-gray-50 transition-colors">
              <input type="file" ref="videoInput" accept="video/mp4,video/quicktime" class="hidden" @change="handleFileSelect">
              <div v-if="!uploadFile && !lectureForm.currentVideoUrl">
                <button type="button" @click="$refs.videoInput.click()" class="text-purple-600 font-semibold">Upload Video</button>
                <p class="text-xs text-gray-500 mt-1">MP4 or MOV up to 100MB</p>
              </div>
              <div v-else class="flex items-center justify-between">
                <span class="text-sm font-medium text-gray-900 truncate max-w-xs">
                  {{ uploadFile ? uploadFile.name : 'Current Video' }}
                </span>
                <button type="button" @click="clearFile" class="text-red-500 text-sm hover:underline">Remove</button>
              </div>
            </div>
            <div v-if="uploadProgress > 0 && uploadProgress < 100" class="mt-2">
              <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                <div class="h-full bg-purple-600 transition-all duration-300" :style="{ width: uploadProgress + '%' }"></div>
              </div>
              <p class="text-xs text-gray-500 mt-1">Uploading... {{ uploadProgress }}%</p>
            </div>
          </div>

          <!-- Article Content -->
          <div v-if="lectureForm.type === 'article'" class="mb-6">
             <label class="block text-sm font-semibold text-gray-700 mb-2">Content</label>
             <textarea v-model="lectureForm.content" rows="6" class="input-field" placeholder="Write your article content here..."></textarea>
          </div>

          <div class="flex justify-end gap-3">
            <button type="button" @click="closeLectureModal" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Cancel</button>
            <button type="submit" class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 font-medium" :disabled="uploading">
              {{ uploading ? 'Uploading...' : 'Save Lecture' }}
            </button>
          </div>
        </form>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted, computed, nextTick } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';
import draggable from 'vuedraggable';
import Navbar from '../components/Navbar.vue';

const route = useRoute();
const courseId = route.params.id; // Actually slug or ID depending on router, prefer ID for manage
// Wait, router usually passes ID for edit, let's assume router config will pass ID
// If backend uses course model binding, we might need ID.
// Let's use `course` prop if available, or fetch by ID.

const loading = ref(true);
const course = ref({});
const sections = ref([]);
const lectures = ref([]); // Lectures for selected section
const selectedSection = ref(null);

// Section Editing
const editingSectionId = ref(null);
const sectionInput = ref(null);

// Lecture Modal
const showAddLecture = ref(false);
const editingLecture = ref(null);
const lectureForm = ref({ title: '', type: 'video', content: '', currentVideoUrl: null });
const uploadFile = ref(null);
const uploadProgress = ref(0);
const uploading = ref(false);

const videoInput = ref(null);

// Initialize
onMounted(async () => {
  await fetchCourseData();
});

const fetchCourseData = async () => {
    try {
        // Assuming we have an endpoint like /api/instructor/courses/{id}/curriculum
        // Or we use existing endpoints.
        // Let's assume we use standard REST:
        // GET /api/courses/{id} -> get course details
        // GET /api/courses/{id}/sections -> get sections
        
        // Wait, Route param might be ID. 
        // Let's fetch course first
        const courseRes = await axios.get(`/api/courses/${courseId}`); 
        // Note: public endpoint usually, but we need instructor specific check?
        // Actually /api/instructor/courses returns list, maybe /api/instructor/courses/{id} exists?
        // Let's try /api/courses/{id} for now, it should work if we own it.
        
        course.value = courseRes.data;

        // Fetch sections
        const sectionsRes = await axios.get(`/api/courses/${courseId}/sections`);
        sections.value = sectionsRes.data;

        loading.value = false;
        
        if (sections.value.length > 0) {
            selectSection(sections.value[0]);
        }
    } catch (e) {
        console.error("Failed to load course data", e);
        loading.value = false;
    }
};

// Section Methods
const selectSection = async (section) => {
    selectedSection.value = section;
    // Section typically includes lectures, or we fetch them?
    // Based on `SectionController`, index returns sections.
    // If backend returns lectures nested, use that.
    // Otherwise fetch lectures.
    if (section.lectures) {
        lectures.value = section.lectures;
    } else {
        // Fetch specific lectures if not included?
        // Assuming included for now based on standard Larave Resources
        // But if not, we might need `with=lectures`
    }
    
    // Refresh lectures just in case
    // const res = await axios.get(`/api/sections/${section.id}/lectures`); // Hypothetical
    // lectures.value = res.data;
    
    // For now assuming `sections` includes `lectures`.
};

const addSection = async () => {
    try {
        const title = prompt("Enter section title:");
        if (!title) return;
        
        const res = await axios.post(`/api/courses/${course.value.id}/sections`, {
            title,
            course_id: course.value.id,
            order: sections.value.length + 1
        });
        sections.value.push(res.data);
        if (!selectedSection.value) selectSection(res.data);
    } catch (e) {
        alert("Failed to create section");
    }
};

const editSection = (section) => {
    editingSectionId.value = section.id;
    nextTick(() => {
        if(sectionInput.value) sectionInput.value[0]?.focus();
    });
};

const updateSection = async (section) => {
    if (!editingSectionId.value) return;
    try {
        await axios.put(`/api/courses/${course.value.id}/sections/${section.id}`, {
            title: section.title
        });
        editingSectionId.value = null;
    } catch (e) {
        alert("Failed to update section");
    }
};

const deleteSection = async (section) => {
    if (!confirm("Delete this section and all its lectures?")) return;
    try {
        await axios.delete(`/api/courses/${course.value.id}/sections/${section.id}`);
        sections.value = sections.value.filter(s => s.id !== section.id);
        if (selectedSection.value?.id === section.id) {
            selectedSection.value = null;
            lectures.value = [];
        }
    } catch (e) {
        alert("Failed to delete section");
    }
};

const reorderSections = async () => {
    try {
        const orderData = sections.value.map((s, index) => ({
            id: s.id,
            order: index + 1
        }));
        await axios.put(`/api/courses/${course.value.id}/sections/reorder`, { sections: orderData });
    } catch (e) {
        console.error("Failed to reorder sections", e);
    }
};

// Lecture Methods
const handleFileSelect = (event) => {
    uploadFile.value = event.target.files[0];
};

const clearFile = () => {
    uploadFile.value = null;
    if (videoInput.value) videoInput.value.value = '';
};

const closeLectureModal = () => {
    showAddLecture.value = false;
    editingLecture.value = null;
    lectureForm.value = { title: '', type: 'video', content: '', currentVideoUrl: null };
    clearFile();
    uploadProgress.value = 0;
};

const editLecture = (lecture) => {
    editingLecture.value = lecture;
    lectureForm.value = { 
        title: lecture.title, 
        type: lecture.type, 
        content: lecture.content,
        currentVideoUrl: lecture.video_url
    };
    showAddLecture.value = true;
};

const saveLecture = async () => {
    if (!selectedSection.value) return;
    
    uploading.value = true;
    const formData = new FormData();
    formData.append('title', lectureForm.value.title);
    formData.append('type', lectureForm.value.type);
    if (lectureForm.value.content) formData.append('content', lectureForm.value.content);
    
    // Add file if exists
    if (uploadFile.value) {
        formData.append('video', uploadFile.value);
    }

    try {
        let res;
        const config = {
            headers: { 'Content-Type': 'multipart/form-data' },
            onUploadProgress: (progressEvent) => {
                const percentCompleted = Math.round((progressEvent.loaded * 100) / progressEvent.total);
                uploadProgress.value = percentCompleted;
            }
        };

        if (editingLecture.value) {
             // Update
             // Note: PHP needs _method=PUT for multipart/form-data
             formData.append('_method', 'PUT');
             res = await axios.post(
                 `/api/courses/${course.value.id}/sections/${selectedSection.value.id}/lectures/${editingLecture.value.id}`,
                 formData,
                 config
             );
             
             // Update local list
             const idx = lectures.value.findIndex(l => l.id === editingLecture.value.id);
             if (idx !== -1) lectures.value[idx] = res.data.data || res.data; // Resource vs plain json
        } else {
             // Create
             res = await axios.post(
                 `/api/courses/${course.value.id}/sections/${selectedSection.value.id}/lectures`,
                 formData,
                 config
             );
             lectures.value.push(res.data.data || res.data);
        }
        
        closeLectureModal();
    } catch (e) {
        console.error("Failed to save lecture", e);
        alert("Failed to save lecture: " + (e.response?.data?.message || e.message));
    } finally {
        uploading.value = false;
    }
};

const deleteLecture = async (lecture) => {
    if (!confirm("Delete this lecture?")) return;
    try {
        await axios.delete(`/api/courses/${course.value.id}/sections/${selectedSection.value.id}/lectures/${lecture.id}`);
        lectures.value = lectures.value.filter(l => l.id !== lecture.id);
    } catch (e) {
        alert("Failed to delete lecture");
    }
};

const reorderLectures = async () => {
     // Implement lecture reordering logic if backend supports it
     // Current LectureController doesn't seem to have a bulk reorder endpoint explicitly visible in previous `api.php` view,
     // but we can try updating `order` field one by one or add a reorder endpoint.
     // For now, let's skip backend sync for lecture reorder to avoid errors, or implement it later.
     console.log("Lecture reordering frontend only for now");
};

</script>

<style scoped>
.input-field {
  width: 100%;
  padding: 0.75rem 1rem;
  border: 1px solid #d1d5db;
  border-radius: 0.5rem;
  outline: none;
  transition: all 0.2s;
}

.input-field:focus {
  border-color: #9333ea;
  box-shadow: 0 0 0 2px rgba(147, 51, 234, 0.1);
}

.animate-fade-in {
  animation: fadeIn 0.2s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: scale(0.95); }
  to { opacity: 1; transform: scale(1); }
}
</style>
