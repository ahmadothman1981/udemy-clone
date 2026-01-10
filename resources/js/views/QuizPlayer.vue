<template>
  <div class="min-h-screen bg-gray-50">
    <Navbar />
    
    <div class="max-w-4xl mx-auto px-4 py-8">
      <!-- Loading State -->
      <div v-if="quizStore.loading" class="flex justify-center py-20">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-purple-600"></div>
      </div>

      <!-- Error State -->
      <div v-else-if="quizStore.error" class="text-center py-20">
        <div class="text-red-500 text-6xl mb-4">⚠️</div>
        <h2 class="text-xl font-bold text-gray-700 mb-2">{{ quizStore.error }}</h2>
        <router-link :to="`/course/${courseId}`" class="text-purple-600 hover:underline">
          Back to Course
        </router-link>
      </div>

      <!-- Quiz Content -->
      <div v-else-if="quizStore.currentQuiz">
        <!-- Quiz Header -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
          <div class="flex items-center justify-between mb-4">
            <div>
              <h1 class="text-2xl font-bold text-gray-900">{{ quizStore.currentQuiz.title }}</h1>
              <p class="text-gray-500 mt-1">
                {{ quizStore.totalQuestions }} questions • 
                Pass: {{ quizStore.currentQuiz.pass_percentage }}%
                <span v-if="quizStore.currentQuiz.time_limit">
                  • {{ quizStore.currentQuiz.time_limit }} min
                </span>
              </p>
            </div>
            <div v-if="quizStore.hasPassed" class="bg-green-100 text-green-800 px-4 py-2 rounded-full font-semibold">
              ✓ Passed
            </div>
          </div>

          <!-- Progress Bar -->
          <div class="w-full bg-gray-200 rounded-full h-2">
            <div 
              class="bg-purple-600 h-2 rounded-full transition-all duration-300"
              :style="{ width: `${(quizStore.answeredCount / quizStore.totalQuestions) * 100}%` }"
            ></div>
          </div>
          <p class="text-sm text-gray-500 mt-2">
            {{ quizStore.answeredCount }} of {{ quizStore.totalQuestions }} answered
          </p>
        </div>

        <!-- Results Display (after submission) -->
        <div v-if="quizStore.result" class="mb-6">
          <div 
            class="rounded-2xl p-6 text-center"
            :class="quizStore.result.passed ? 'bg-green-50 border-2 border-green-200' : 'bg-amber-50 border-2 border-amber-200'"
          >
            <div class="text-5xl mb-4">{{ quizStore.result.passed ? '🎉' : '📚' }}</div>
            <h2 class="text-2xl font-bold" :class="quizStore.result.passed ? 'text-green-800' : 'text-amber-800'">
              {{ quizStore.result.message }}
            </h2>
            <div class="mt-4 text-4xl font-bold" :class="quizStore.result.passed ? 'text-green-600' : 'text-amber-600'">
              {{ quizStore.result.percentage }}%
            </div>
            <p class="text-gray-600 mt-2">
              {{ quizStore.result.score }} / {{ quizStore.result.total_points }} points
            </p>
            
            <div class="flex gap-4 justify-center mt-6">
              <button 
                @click="retakeQuiz"
                class="px-6 py-3 bg-purple-600 text-white font-semibold rounded-xl hover:bg-purple-700 transition-colors"
              >
                {{ quizStore.result.passed ? 'Try Again' : 'Retry Quiz' }}
              </button>
              <router-link 
                :to="`/learn/${courseId}`"
                class="px-6 py-3 bg-gray-200 text-gray-700 font-semibold rounded-xl hover:bg-gray-300 transition-colors"
              >
                Back to Course
              </router-link>
            </div>
          </div>

          <!-- Detailed Results -->
          <div class="mt-6 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
              <h3 class="font-bold text-gray-900">Answer Review</h3>
            </div>
            <div class="divide-y divide-gray-100">
              <div 
                v-for="(result, index) in quizStore.result.results" 
                :key="result.question_id"
                class="p-4"
                :class="result.is_correct ? 'bg-green-50' : 'bg-red-50'"
              >
                <div class="flex items-start gap-3">
                  <div 
                    class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center font-bold"
                    :class="result.is_correct ? 'bg-green-500 text-white' : 'bg-red-500 text-white'"
                  >
                    {{ result.is_correct ? '✓' : '✗' }}
                  </div>
                  <div class="flex-1">
                    <p class="font-medium text-gray-900">{{ index + 1 }}. {{ result.question_text }}</p>
                    <p class="text-sm mt-1" :class="result.is_correct ? 'text-green-700' : 'text-red-700'">
                      Your answer: {{ result.user_answer }}
                    </p>
                    <p v-if="!result.is_correct" class="text-sm text-green-700 mt-1">
                      Correct answer: {{ result.correct_answer }}
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Questions List (before submission) -->
        <div v-else class="space-y-6">
          <div 
            v-for="(question, index) in quizStore.questions" 
            :key="question.id"
            class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6"
          >
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
              {{ index + 1 }}. {{ question.question_text }}
            </h3>
            
            <div class="space-y-3">
              <label 
                v-for="option in question.options" 
                :key="option"
                class="flex items-center p-4 border-2 rounded-xl cursor-pointer transition-all"
                :class="quizStore.userAnswers[question.id] === option 
                  ? 'border-purple-500 bg-purple-50' 
                  : 'border-gray-200 hover:border-gray-300'"
              >
                <input 
                  type="radio" 
                  :name="`question-${question.id}`"
                  :value="option"
                  :checked="quizStore.userAnswers[question.id] === option"
                  @change="selectAnswer(question.id, option)"
                  class="sr-only"
                >
                <div 
                  class="w-5 h-5 rounded-full border-2 mr-3 flex items-center justify-center"
                  :class="quizStore.userAnswers[question.id] === option 
                    ? 'border-purple-500 bg-purple-500' 
                    : 'border-gray-300'"
                >
                  <div v-if="quizStore.userAnswers[question.id] === option" class="w-2 h-2 rounded-full bg-white"></div>
                </div>
                <span class="text-gray-700">{{ option }}</span>
              </label>
            </div>
          </div>

          <!-- Submit Button -->
          <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <button 
              @click="submitQuiz"
              :disabled="!quizStore.allAnswered || quizStore.submitting"
              class="w-full py-4 px-6 bg-gradient-to-r from-purple-600 to-indigo-600 text-white font-bold text-lg rounded-xl hover:from-purple-700 hover:to-indigo-700 transition-all shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span v-if="quizStore.submitting" class="flex items-center justify-center gap-2">
                <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Submitting...
              </span>
              <span v-else>Submit Quiz</span>
            </button>
            
            <p v-if="!quizStore.allAnswered" class="text-center text-amber-600 text-sm mt-3">
              ⚠️ Please answer all questions before submitting
            </p>
          </div>
        </div>

        <!-- Previous Attempts -->
        <div v-if="quizStore.attempts.length > 0 && !quizStore.result" class="mt-8">
          <h3 class="text-lg font-bold text-gray-900 mb-4">Previous Attempts</h3>
          <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Date</th>
                  <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Score</th>
                  <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Result</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                <tr v-for="attempt in quizStore.attempts.slice(0, 5)" :key="attempt.id">
                  <td class="px-4 py-3 text-sm text-gray-600">
                    {{ new Date(attempt.created_at).toLocaleDateString() }}
                  </td>
                  <td class="px-4 py-3 text-sm font-semibold">
                    {{ attempt.score }}/{{ attempt.total_points }} ({{ attempt.percentage || Math.round((attempt.score / attempt.total_points) * 100) }}%)
                  </td>
                  <td class="px-4 py-3">
                    <span 
                      class="px-2 py-1 rounded-full text-xs font-semibold"
                      :class="attempt.passed ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                    >
                      {{ attempt.passed ? 'Passed' : 'Failed' }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, onUnmounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useQuizStore } from '../stores/quiz';
import Navbar from '../components/Navbar.vue';

const route = useRoute();
const router = useRouter();
const quizStore = useQuizStore();

const courseId = route.params.courseId;
const quizId = route.params.quizId;

onMounted(() => {
  quizStore.fetchQuiz(courseId, quizId);
});

onUnmounted(() => {
  quizStore.clearQuiz();
});

const selectAnswer = (questionId, answer) => {
  quizStore.setAnswer(questionId, answer);
};

const submitQuiz = async () => {
  await quizStore.submitQuiz(courseId, quizId);
};

const retakeQuiz = () => {
  quizStore.resetQuiz();
};
</script>
