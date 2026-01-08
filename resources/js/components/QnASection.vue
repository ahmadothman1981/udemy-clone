<template>
  <div class="mt-8">
    <h3 class="text-xl font-bold mb-4">Q&A</h3>
    
    <!-- Ask Form -->
    <div class="mb-6">
        <input v-model="title" class="w-full border border-gray-300 rounded p-2 mb-2" placeholder="Question Title">
        <textarea v-model="content" class="w-full border border-gray-300 rounded p-2 mb-2" rows="2" placeholder="Describe your question..."></textarea>
        <button @click="askQuestion" class="bg-gray-800 text-white px-4 py-2 rounded font-bold text-sm" :disabled="!title || !content">
            Ask Question
        </button>
    </div>

    <!-- Questions List -->
    <div class="space-y-4">
        <div v-for="q in questions" :key="q.id" class="border border-gray-200 rounded p-4">
            <h4 class="font-bold cursor-pointer" @click="q.expanded = !q.expanded">{{ q.title }}</h4>
            <div v-if="q.expanded" class="mt-2">
                <p class="text-gray-700 mb-4">{{ q.content }}</p>
                <div class="text-xs text-gray-500 mb-2">Asked by {{ q.user.name }}</div>
                
                <!-- Answers -->
                <div class="bg-gray-50 p-3 rounded space-y-3 mb-3" v-if="q.answers.length">
                    <div v-for="ans in q.answers" :key="ans.id" class="text-sm">
                        <span class="font-bold">{{ ans.user.name }}:</span> {{ ans.content }}
                    </div>
                </div>
                
                <!-- Reply -->
                <div class="flex">
                    <input v-model="q.newReply" class="flex-1 border border-gray-300 rounded-l p-1 text-sm" placeholder="Write a reply...">
                    <button @click="reply(q)" class="bg-purple-600 text-white px-3 py-1 rounded-r text-sm">Reply</button>
                </div>
            </div>
             <div v-else class="text-sm text-gray-500 mt-1">
                 {{ q.answers.length }} replies
             </div>
        </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps(['courseId']);
const questions = ref([]);
const title = ref('');
const content = ref('');

onMounted(() => fetchQuestions());

const fetchQuestions = async () => {
    const res = await axios.get(`/api/courses/${props.courseId}/questions`);
    questions.value = res.data.data.map(q => ({...q, expanded: false, newReply: ''}));
};

const askQuestion = async () => {
    await axios.post(`/api/courses/${props.courseId}/questions`, {
        title: title.value,
        content: content.value
    });
    title.value = '';
    content.value = '';
    fetchQuestions();
};

const reply = async (question) => {
    if(!question.newReply) return;
    await axios.post(`/api/courses/${props.courseId}/questions/${question.id}/answers`, {
        content: question.newReply
    });
    question.newReply = '';
    fetchQuestions(); // naive refresh, ideally just push to local array
};
</script>
