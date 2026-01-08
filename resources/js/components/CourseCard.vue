<template>
  <div class="group flex flex-col bg-white border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition-shadow duration-200 cursor-pointer" @click="$router.push(`/course/${course.slug || course.id}`)">
    <!-- Thumbnail -->
    <div class="aspect-video bg-gray-200 relative overflow-hidden">
        <img v-if="placeholderUrl" :src="placeholderUrl" class="object-cover w-full h-full" alt="Course Thumbnail">
        <div v-else class="flex items-center justify-center w-full h-full text-gray-400">
           No Image
        </div>
    </div>
    
    <!-- Content -->
    <div class="p-4 flex flex-col flex-1">
        <h3 class="font-bold text-gray-900 line-clamp-2 mb-1 group-hover:text-purple-700">{{ course.title }}</h3>
        <p class="text-xs text-gray-500 mb-2">{{ course.instructor?.name }}</p>
        
        <!-- Rating -->
        <div class="flex items-center space-x-1 mb-2">
            <span class="text-sm font-bold text-yellow-600">{{ course.rating_avg.toFixed(1) }}</span>
            <div class="flex text-yellow-400 text-xs">
                 ★★★★★
            </div>
            <span class="text-xs text-gray-400">({{ course.enrollment_count }})</span>
        </div>
        
        <div class="mt-auto">
            <div class="flex items-center space-x-2">
                <span class="font-bold text-gray-900">${{ course.price }}</span>
                <span v-if="course.discount_price" class="text-sm text-gray-400 line-through">${{ course.discount_price }}</span>
            </div>
        </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    course: {
        type: Object,
        required: true
    }
});

const placeholderUrl = computed(() => {
    return props.course.thumbnail || `https://placehold.co/600x400?text=${encodeURIComponent(props.course.title)}`;
});
</script>
