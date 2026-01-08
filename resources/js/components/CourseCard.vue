<template>
  <div 
    class="course-card group"
    @click="$router.push(`/course/${course.slug || course.id}`)"
  >
    <!-- Badges -->
    <div class="absolute top-3 left-3 z-10 flex flex-col gap-1">
      <span v-if="isBestseller" class="badge badge-bestseller">
        Bestseller
      </span>
      <span v-if="isNew" class="badge badge-new">
        New
      </span>
    </div>
    
    <!-- Wishlist button -->
    <button 
      class="wishlist-btn"
      @click.stop="toggleWishlist"
    >
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" :fill="isWishlisted ? 'currentColor' : 'none'" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
      </svg>
    </button>
    
    <!-- Thumbnail -->
    <div class="aspect-video bg-gray-200 relative overflow-hidden">
      <img 
        v-if="placeholderUrl" 
        :src="placeholderUrl" 
        class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" 
        alt="Course Thumbnail"
      >
      <div v-else class="flex items-center justify-center w-full h-full text-gray-400">
        No Image
      </div>
      <!-- Gradient overlay on hover -->
      <div class="absolute inset-0 bg-gradient-to-t from-purple-900/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-all duration-300"></div>
      
      <!-- Play button overlay -->
      <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300">
        <div class="w-14 h-14 rounded-full bg-white/90 flex items-center justify-center shadow-lg transform scale-75 group-hover:scale-100 transition-transform duration-300">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-600 ml-1" fill="currentColor" viewBox="0 0 24 24">
            <path d="M8 5v14l11-7z"/>
          </svg>
        </div>
      </div>
    </div>
    
    <!-- Content -->
    <div class="p-4 flex flex-col flex-1">
      <h3 class="font-bold text-gray-900 line-clamp-2 mb-1 group-hover:text-purple-600 transition-colors duration-300">
        {{ course.title }}
      </h3>
      <p class="text-xs text-gray-500 mb-2">{{ course.instructor?.name }}</p>
      
      <!-- Rating -->
      <div class="flex items-center gap-1 mb-2">
        <span class="text-sm font-bold text-amber-600">{{ (course.rating_avg || 0).toFixed(1) }}</span>
        <div class="flex">
          <svg 
            v-for="i in 5" 
            :key="i" 
            xmlns="http://www.w3.org/2000/svg" 
            class="h-3.5 w-3.5 transition-all duration-300"
            :class="i <= Math.round(course.rating_avg || 0) ? 'text-amber-400' : 'text-gray-300'"
            viewBox="0 0 20 20" 
            fill="currentColor"
          >
            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
          </svg>
        </div>
        <span class="text-xs text-gray-400">({{ course.enrollment_count || 0 }})</span>
      </div>
      
      <!-- Price -->
      <div class="mt-auto">
        <div class="flex items-center gap-2">
          <span class="text-lg font-bold text-gray-900">${{ course.price }}</span>
          <span v-if="course.discount_price" class="text-sm text-gray-400 line-through">${{ course.discount_price }}</span>
          <span v-if="discountPercent" class="text-xs font-semibold text-green-600 bg-green-100 px-2 py-0.5 rounded-full">
            {{ discountPercent }}% off
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { useWishlistStore } from '../stores/wishlist';

const props = defineProps({
  course: {
    type: Object,
    required: true
  }
});

const wishlistStore = useWishlistStore();

const placeholderUrl = computed(() => {
  return props.course.thumbnail || `https://placehold.co/600x400/a855f7/ffffff?text=${encodeURIComponent(props.course.title?.substring(0, 20) || 'Course')}`;
});

const isBestseller = computed(() => {
  return (props.course.enrollment_count || 0) > 100 || (props.course.rating_avg || 0) >= 4.5;
});

const isNew = computed(() => {
  if (!props.course.created_at) return false;
  const createdDate = new Date(props.course.created_at);
  const thirtyDaysAgo = new Date();
  thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30);
  return createdDate > thirtyDaysAgo;
});

const isWishlisted = computed(() => {
  return wishlistStore.items.some(item => item.id === props.course.id);
});

const discountPercent = computed(() => {
  if (!props.course.discount_price || !props.course.price) return null;
  const discount = ((props.course.discount_price - props.course.price) / props.course.discount_price) * 100;
  return Math.round(discount);
});

const toggleWishlist = () => {
  if (isWishlisted.value) {
    wishlistStore.removeItem(props.course.id);
  } else {
    wishlistStore.addItem(props.course);
  }
};
</script>

<style scoped>
.course-card {
  position: relative;
  display: flex;
  flex-direction: column;
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 1rem;
  overflow: hidden;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.course-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 25px 50px -12px rgba(139, 92, 246, 0.25);
  border-color: rgba(139, 92, 246, 0.3);
}

.badge {
  padding: 0.25rem 0.5rem;
  font-size: 0.65rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  border-radius: 0.25rem;
}

.badge-bestseller {
  background: linear-gradient(135deg, #fbbf24, #f59e0b);
  color: #78350f;
}

.badge-new {
  background: linear-gradient(135deg, #34d399, #10b981);
  color: white;
}

.wishlist-btn {
  position: absolute;
  top: 0.75rem;
  right: 0.75rem;
  z-index: 10;
  padding: 0.5rem;
  background: white;
  border-radius: 9999px;
  color: #9ca3af;
  opacity: 0;
  transform: scale(0.8);
  transition: all 0.3s ease;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.course-card:hover .wishlist-btn {
  opacity: 1;
  transform: scale(1);
}

.wishlist-btn:hover {
  color: #ec4899;
  transform: scale(1.1);
}
</style>
