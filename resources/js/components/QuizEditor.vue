<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="$emit('close')"></div>
    <div class="relative bg-white rounded-2xl shadow-xl max-w-4xl w-full h-[90vh] flex flex-col animate-fade-in overflow-hidden">
      <!-- Header -->
      <div class="p-6 border-b flex justify-between items-center bg-gray-50">
        <div>
          <h2 class="text-2xl font-bold text-gray-900">Quiz Editor</h2>
          <p class="text-sm text-gray-500">Manage questions and settings</p>
        </div>
        <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600 p-2 rounded-full hover:bg-gray-200">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Main Content -->
      <div class="flex-1 overflow-y-auto p-6 bg-gray-50">
        <div v-if="loading" class="flex justify-center py-12">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-purple-600"></div>
        </div>
        
        <div v-else class="space-y-8">
          <!-- Quiz Settings -->
          <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Quiz Settings</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
               <div>
                  <label class="block text-sm font-semibold text-gray-700 mb-2">Quiz Title</label>
                  <input v-model="quizForm.title" type="text" class="input-field" placeholder="e.g. Final Assessment">
               </div>
               <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Pass %</label>
                    <input v-model="quizForm.pass_percentage" type="number" min="0" max="100" class="input-field">
                  </div>
                  <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Time Limit (min)</label>
                    <input v-model="quizForm.time_limit" type="number" min="0" class="input-field" placeholder="Optional">
                  </div>
               </div>
            </div>
            <div class="mt-4 flex justify-end">
               <button @click="saveSettings" class="btn-primary" :disabled="savingSettings">
                 {{ savingSettings ? 'Saving...' : 'Save Settings' }}
               </button>
            </div>
          </div>

          <!-- Questions List -->
          <div class="space-y-4">
            <div class="flex justify-between items-center">
              <h3 class="text-lg font-bold text-gray-900">Questions ({{ questions.length }})</h3>
              <button @click="openQuestionModal()" class="btn-secondary">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Question
              </button>
            </div>

            <div v-if="questions.length === 0" class="bg-white rounded-xl border-2 border-dashed border-gray-200 p-12 text-center text-gray-500">
               No questions yet. Add one to get started.
            </div>

            <div v-else class="space-y-4">
               <div v-for="(q, index) in questions" :key="q.id" class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
                  <div class="flex justify-between items-start">
                    <div class="flex-1">
                       <span class="text-xs font-bold text-purple-600 mb-1 block">Question {{ index + 1 }}</span>
                       <h4 class="font-medium text-gray-900 mb-2">{{ q.question_text }}</h4>
                       <div class="space-y-1">
                          <div v-for="(opt, i) in q.options" :key="i" class="text-sm flex items-center gap-2">
                             <div class="w-2 h-2 rounded-full" :class="opt === q.correct_answer ? 'bg-green-500' : 'bg-gray-300'"></div>
                             <span :class="opt === q.correct_answer ? 'font-semibold text-green-700' : 'text-gray-600'">{{ opt }}</span>
                             <span v-if="opt === q.correct_answer" class="text-xs text-green-600 bg-green-50 px-1.5 py-0.5 rounded">Correct</span>
                          </div>
                       </div>
                    </div>
                    <div class="flex gap-2">
                       <button @click="openQuestionModal(q)" class="text-blue-600 text-sm hover:underline font-medium">Edit</button>
                       <button @click="deleteQuestion(q.id)" class="text-red-500 text-sm hover:underline font-medium">Delete</button>
                       <span class="text-sm text-gray-400 font-medium ml-2 border-l pl-2">{{ q.points }} pts</span>
                    </div>
                  </div>
               </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Add Question Modal -->
    <div v-if="showQuestionModal" class="absolute inset-0 z-[60] flex items-center justify-center p-4 bg-black/20 backdrop-blur-[1px]">
        <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full p-6 animate-fade-in border border-gray-200" @click.stop>
            <h3 class="text-xl font-bold mb-4">Add Question</h3>
            
            <div class="space-y-4">
                <div>
                   <label class="block text-sm font-semibold text-gray-700 mb-1">Question Text</label>
                   <textarea v-model="questionForm.question_text" rows="2" class="input-field" placeholder="What is...?"></textarea>
                </div>
                
                <div>
                   <label class="block text-sm font-semibold text-gray-700 mb-1">Options</label>
                   <div class="space-y-2">
                      <div v-for="(opt, idx) in questionForm.options" :key="idx" class="flex gap-2">
                         <input v-model="questionForm.options[idx]" type="text" class="input-field py-1" :placeholder="`Option ${idx+1}`">
                         <div class="flex items-center">
                            <input type="radio" :value="questionForm.options[idx]" v-model="questionForm.correct_answer" name="correct_ans" class="w-4 h-4 text-purple-600 cursor-pointer" title="Mark as correct">
                         </div>
                         <button @click="removeOption(idx)" class="text-red-400 hover:text-red-600" v-if="questionForm.options.length > 2">x</button>
                      </div>
                   </div>
                   <button @click="addOption" class="text-sm text-purple-600 font-semibold mt-2 hover:underline">+ Add Option</button>
                </div>

                <div>
                   <label class="block text-sm font-semibold text-gray-700 mb-1">Points</label>
                   <input v-model="questionForm.points" type="number" min="1" class="input-field w-24">
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3 border-t pt-4">
               <button @click="showQuestionModal = false" class="px-4 py-2 text-gray-600 hover:text-gray-900">Cancel</button>
               <button @click="saveQuestion" class="btn-primary" :disabled="savingQuestion || !isQuestionValid">
                 {{ savingQuestion ? 'Saving...' : (editingQuestionId ? 'Update Question' : 'Add Question') }}
               </button>
            </div>
        </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
  lecture: { type: Object, required: true },
  courseId: { type: [String, Number], required: true }
});

const emit = defineEmits(['close']);

const loading = ref(true);
const quiz = ref(null);
const questions = ref([]);
const quizForm = ref({ title: '', pass_percentage: 70, time_limit: null });

const savingSettings = ref(false);

const showQuestionModal = ref(false);
const savingQuestion = ref(false);
const editingQuestionId = ref(null); // Track editing question
const questionForm = ref({
    question_text: '',
    options: ['', '', '', ''],
    correct_answer: '',
    points: 1
});

// Fetch quiz data
const fetchQuiz = async () => {
   try {
     // First try to fetch existing quiz for this lecture
     // If lecture has quiz relationship loaded?
     // Actually usually access via /quizzes endpoint.
     // But we only have lecture object.
     // Let's assume if lecture.type is quiz, there might be a quiz associated.
     
     // Check if lecture has quiz_id or relations
     if (props.lecture.quiz) {
         await loadQuizDetails(props.lecture.quiz.id);
     } else {
         // Create a new quiz automatically if not exists?
         // Or check if backend has it.
         // Let's try creating/getting.
         // Since we don't have a direct 'get quiz by lecture' endpoint standardised,
         // We might need to rely on lecture.quiz being populated.
         
         // If generic lecture passed, maybe we need to create one first.
         const res = await axios.post(`/api/courses/${props.courseId}/lectures/${props.lecture.id}/quiz`, {
             title: props.lecture.title + ' Quiz',
             pass_percentage: 70
         });
         quiz.value = res.data;
         // Refresh lecture or just use this quiz id
         questions.value = []; // New quiz
         quizForm.value = { ...quiz.value };
     }
     loading.value = false;
   } catch (e) {
     // If 409 or already exists but not in props?
     // Assuming new quiz creation flow for now.
     console.error("Quiz fetch error", e);
     loading.value = false;
   }
};

const loadQuizDetails = async (quizId) => {
    try {
        const res = await axios.get(`/api/courses/${props.courseId}/quizzes/${quizId}`);
        quiz.value = res.data.quiz;
        // Check if questions included (modified controller to include for instructor)
        questions.value = quiz.value.questions || [];
        quizForm.value = {
            title: quiz.value.title,
            pass_percentage: quiz.value.pass_percentage,
            time_limit: quiz.value.time_limit
        };
    } catch (e) {
        console.error("Failed to load quiz details", e);
    }
};

onMounted(() => {
    fetchQuiz();
});

const saveSettings = async () => {
    if (!quiz.value) return;
    savingSettings.value = true;
    try {
        await axios.put(`/api/courses/${props.courseId}/quizzes/${quiz.value.id}`, {
            title: quizForm.value.title,
            pass_percentage: quizForm.value.pass_percentage,
            time_limit: quizForm.value.time_limit
        });
        alert("Quiz settings updated successfully!");
    } catch (e) {
        console.error("Failed to save settings", e);
        alert("Failed to save settings: " + (e.response?.data?.message || e.message));
    } finally {
        savingSettings.value = false;
    }
};

const openQuestionModal = (question = null) => {
    if (question) {
        editingQuestionId.value = question.id;
        // Deep copy to break reactivity and allow cancel
        questionForm.value = JSON.parse(JSON.stringify({
            question_text: question.question_text,
            options: question.options,
            correct_answer: question.correct_answer,
            points: question.points
        }));
    } else {
        editingQuestionId.value = null;
        questionForm.value = {
            question_text: '',
            options: ['', '', '', ''],
            correct_answer: '',
            points: 5
        };
    }
    showQuestionModal.value = true;
};

const addOption = () => questionForm.value.options.push('');
const removeOption = (idx) => {
    const val = questionForm.value.options[idx];
    questionForm.value.options.splice(idx, 1);
    if (questionForm.value.correct_answer === val) questionForm.value.correct_answer = '';
};

const isQuestionValid = computed(() => {
    return questionForm.value.question_text && 
           questionForm.value.options.filter(o => o).length >= 2 &&
           questionForm.value.correct_answer;
});

const saveQuestion = async () => {
    if (!quiz.value) return;
    savingQuestion.value = true;
    try {
        const payload = {
             ...questionForm.value,
            options: questionForm.value.options.filter(o => o) // remove empty
        };

        if (editingQuestionId.value) {
             // Update existing
             const res = await axios.put(`/api/courses/${props.courseId}/quizzes/${quiz.value.id}/questions/${editingQuestionId.value}`, payload);
             
             // Update in local list
             const index = questions.value.findIndex(q => q.id === editingQuestionId.value);
             if (index !== -1) {
                 questions.value[index] = res.data;
             }
        } else {
             // Create new
             const res = await axios.post(`/api/courses/${props.courseId}/quizzes/${quiz.value.id}/questions`, payload);
             questions.value.push(res.data);
        }
        showQuestionModal.value = false;
    } catch (e) {
        alert("Failed to save question: " + (e.response?.data?.message || e.message));
    } finally {
        savingQuestion.value = false;
    }
};

const deleteQuestion = async (questionId) => {
    if(!confirm("Are you sure you want to delete this question?")) return;
    try {
        await axios.delete(`/api/courses/${props.courseId}/quizzes/${quiz.value.id}/questions/${questionId}`);
        questions.value = questions.value.filter(q => q.id !== questionId);
    } catch (e) {
         alert("Failed to delete question");
    }
};

</script>

<style scoped>
.input-field {
  width: 100%;
  padding: 0.5rem 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 0.5rem;
  outline: none;
  transition: all 0.2s;
}
.input-field:focus {
  border-color: #9333ea;
  box-shadow: 0 0 0 2px rgba(147, 51, 234, 0.1);
}
.btn-primary {
  padding: 0.5rem 1rem;
  background-color: #9333ea;
  color: white;
  font-weight: 500;
  border-radius: 0.5rem;
  transition: background-color 0.2s;
}
.btn-primary:hover:not(:disabled) {
  background-color: #7e22ce;
}
.btn-primary:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}
.btn-secondary {
  padding: 0.5rem 1rem;
  background-color: #f3e8ff;
  color: #7e22ce;
  font-weight: 600;
  border-radius: 0.5rem;
  display: flex;
  align-items: center;
  transition: background-color 0.2s;
}
.btn-secondary:hover {
  background-color: #e9d5ff;
}
.animate-fade-in {
  animation: fadeIn 0.2s ease-out;
}
@keyframes fadeIn {
  from { opacity: 0; transform: scale(0.98); }
  to { opacity: 1; transform: scale(1); }
}
</style>
