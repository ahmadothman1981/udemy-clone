<template>
  <div class="mt-8">
    <h3 class="text-xl font-bold mb-4">Reviews</h3>
    
    <!-- Write Review Form -->
    <div v-if="canReview" class="bg-gray-50 p-4 rounded-lg mb-6">
       <h4 class="font-bold mb-2">Write a Review</h4>
       <div class="flex space-x-2 mb-2">
           <button v-for="star in 5" :key="star" @click="rating = star" class="text-2xl focus:outline-none" :class="star <= rating ? 'text-yellow-400' : 'text-gray-300'">
               ★
           </button>
       </div>
       <textarea v-model="content" class="w-full border border-gray-300 rounded p-2 mb-2" rows="3" placeholder="Tell us about your experience..."></textarea>
       <button @click="submitReview" class="bg-purple-600 text-white px-4 py-2 rounded font-bold disabled:opacity-50" :disabled="!content || !rating">
           Post Review
       </button>
    </div>

    <!-- Review List -->
    <div v-if="loading">Loading reviews...</div>
    <div v-else class="space-y-6">
        <div v-for="review in reviews" :key="review.id" class="border-b border-gray-100 pb-6">
            <div class="flex items-center space-x-3 mb-2">
                <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center font-bold text-gray-600">
                    {{ review.user.name[0] }}
                </div>
                <div>
                     <div class="font-bold">{{ review.user.name }}</div>
                     <div class="flex text-yellow-400 text-sm">
                         <span v-for="n in 5" :key="n">{{ n <= review.rating ? '★' : '☆' }}</span>
                     </div>
                </div>
                <div class="ml-auto text-xs text-gray-400">
                    {{ new Date(review.created_at).toLocaleDateString() }}
                </div>
            </div>
            <p class="text-gray-700">{{ review.content }}</p>
        </div>
        <div v-if="reviews.length === 0" class="text-gray-500 italic">No reviews yet.</div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useAuthStore } from '../stores/auth';

const props = defineProps(['courseId']);
const auth = useAuthStore();
const reviews = ref([]);
const loading = ref(false);
const rating = ref(0);
const content = ref('');

// Simplified check. Realworld: check if enrolled via API or store
const canReview = ref(!!auth.isAuthenticated);

onMounted(() => {
    fetchReviews();
});

const fetchReviews = async () => {
    loading.value = true;
    try {
        const res = await axios.get(`/api/courses/${props.courseId}/reviews`);
        reviews.value = res.data.data;
    } finally {
        loading.value = false;
    }
};

const submitReview = async () => {
    try {
        await axios.post(`/api/courses/${props.courseId}/reviews`, {
            rating: rating.value,
            content: content.value
        });
        content.value = '';
        rating.value = 0;
        fetchReviews();
    } catch (e) {
        alert(e.response?.data?.message || 'Failed to post review');
    }
};
</script>
