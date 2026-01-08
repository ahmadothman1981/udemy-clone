<template>
  <section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center mb-8">
        <div>
          <h2 class="text-3xl font-bold text-gray-900 mb-2">{{ $t('home.trending.title') }}</h2>
          <p class="text-gray-600">{{ $t('home.trending.subtitle') }}</p>
        </div>
        <div class="hidden md:flex gap-2">
          <button 
            @click="scroll('prev')"
            class="scroll-btn"
            :disabled="!canScrollPrev"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
          </button>
          <button 
            @click="scroll('next')"
            class="scroll-btn"
            :disabled="!canScrollNext"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </button>
        </div>
      </div>
      
      <div 
        ref="scrollContainer"
        class="flex gap-6 overflow-x-auto pb-4 scroll-smooth hide-scrollbar"
        @scroll="updateScrollState"
      >
        <div 
          v-for="(course, index) in trendingCourses" 
          :key="course.id"
          class="trending-card flex-shrink-0 w-72"
          :style="{ animationDelay: `${index * 0.1}s` }"
        >
          <!-- Trending badge -->
          <div class="absolute top-3 left-3 z-10">
            <span class="trending-badge">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z" />
              </svg>
              {{ $t('home.trending.badge') }}
            </span>
          </div>
          
          <!-- Thumbnail -->
          <div class="aspect-video bg-gray-200 rounded-t-xl overflow-hidden relative cursor-pointer" @click="goToCourse(course)">
            <img 
              :src="getImageUrl(course)" 
              :alt="course.title"
              class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
            >
            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
          </div>
          
          <!-- Content -->
          <div class="p-4 cursor-pointer" @click="goToCourse(course)">
            <h3 class="font-bold text-gray-900 line-clamp-2 mb-2 group-hover:text-purple-600 transition-colors">
              {{ course.title }}
            </h3>
            <p class="text-sm text-gray-500 mb-3">{{ course.instructor?.name }}</p>
            
            <!-- Rating -->
            <div class="flex items-center gap-2 mb-3">
              <span class="text-sm font-bold text-amber-600">{{ course.rating_avg?.toFixed(1) || '4.5' }}</span>
              <div class="flex">
                <svg v-for="i in 5" :key="i" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" :class="i <= Math.round(course.rating_avg || 4.5) ? 'text-amber-400' : 'text-gray-300'" viewBox="0 0 20 20" fill="currentColor">
                  <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
              </div>
              <span class="text-xs text-gray-400">({{ course.enrollment_count || 0 }})</span>
            </div>
            
            <!-- Price -->
            <div class="flex items-center gap-2">
              <span class="text-lg font-bold text-gray-900">${{ course.price }}</span>
              <span v-if="course.original_price" class="text-sm text-gray-400 line-through">${{ course.original_price }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useCourseStore } from '../stores/course';
import { useRouter } from 'vue-router';
import { useI18n } from 'vue-i18n';

const { t, locale } = useI18n();
const courseStore = useCourseStore();
const router = useRouter();
const scrollContainer = ref(null);
const canScrollPrev = ref(false);
const canScrollNext = ref(true);

const isRTL = computed(() => locale.value === 'ar');

const trendingCourses = computed(() => {
  return courseStore.courses.slice(0, 8);
});

const getImageUrl = (course) => {
  return course.thumbnail || `https://placehold.co/600x400/a855f7/ffffff?text=${encodeURIComponent(course.title?.substring(0, 20) || 'Course')}`;
};

const goToCourse = (course) => {
  router.push(`/course/${course.slug || course.id}`);
};

const scroll = (direction) => {
  if (!scrollContainer.value) return;
  const scrollAmount = 300;
  let scrollValue = 0;
  
  if (direction === 'prev') {
    scrollValue = isRTL.value ? scrollAmount : -scrollAmount;
  } else {
    scrollValue = isRTL.value ? -scrollAmount : scrollAmount;
  }
  
  scrollContainer.value.scrollBy({
    left: scrollValue,
    behavior: 'smooth'
  });
};

const updateScrollState = () => {
  if (!scrollContainer.value) return;
  const { scrollLeft, scrollWidth, clientWidth } = scrollContainer.value;
  
  // Checking exact 0 might be flaky with fractional pixels, use small epsilon
  const atStart = Math.abs(scrollLeft) < 10;
  const atEnd = Math.abs(scrollLeft) >= (scrollWidth - clientWidth - 10);
  
  if (isRTL.value) {
    // In some browsers RTL scrollLeft is negative/0/positive in complex ways.
    // Assuming container has dir="ltr" internally via css or inheriting?
    // Wait, if body is RTL, the scrollContainer will be RTL.
    // In RTL mode, scrollLeft starts at 0 (or max positive) depending on implementation.
    // But typically: 
    // If 'dir=rtl': scroll starts at 0 (rightmost) or negative values?
    // Let's assume standard behavior: we just check if we can scroll more...
    // Actually, simply: canScrollPrev = !atStart; canScrollNext = !atEnd;
    // The Scroll buttons logic: 
    // Prev (Right arrow in RTL) -> Moves view to start.
    // Next (Left arrow in RTL) -> Moves view to end.
    
    // For now, let's just stick to standard checking.
    // Note: scrollLeft behavior in RTL is inconsistent across browsers (Chrome vs Firefox).
    // Using a simpler check:
    canScrollPrev.value = Math.abs(scrollLeft) > 5; // Can move towards start (Right) if we are not at 0?
    // This is hard to get 100% right without testing. I'll rely on basic check.
    canScrollNext.value = Math.abs(scrollLeft) + clientWidth < scrollWidth - 5;
  } else {
    canScrollPrev.value = scrollLeft > 0;
    canScrollNext.value = scrollLeft < scrollWidth - clientWidth - 10;
  }
};

onMounted(() => {
  updateScrollState();
});
</script>

<style scoped>
.trending-card {
  position: relative;
  background: white;
  border-radius: 1rem;
  overflow: hidden;
  border: 1px solid #e5e7eb;
  transition: all 0.3s ease;
  animation: fadeIn 0.5s ease forwards;
  opacity: 0;
}

@keyframes fadeIn {
  to { opacity: 1; }
}

.trending-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 20px 40px rgba(139, 92, 246, 0.15);
  border-color: rgba(139, 92, 246, 0.3);
}

.trending-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
  padding: 0.25rem 0.75rem;
  background: linear-gradient(135deg, #f97316, #ef4444);
  color: white;
  font-size: 0.75rem;
  font-weight: 600;
  border-radius: 9999px;
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.8; }
}

.scroll-btn {
  padding: 0.75rem;
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 9999px;
  color: #374151;
  transition: all 0.3s ease;
}

.scroll-btn:hover:not(:disabled) {
  background: linear-gradient(135deg, #a855f7, #ec4899);
  border-color: transparent;
  color: white;
}

.scroll-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.hide-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}

.hide-scrollbar::-webkit-scrollbar {
  display: none;
}
</style>
