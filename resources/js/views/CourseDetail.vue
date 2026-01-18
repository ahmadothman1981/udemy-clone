<template>
    <div>
        <Navbar />
        <div v-if="courseStore.loading" class="text-center py-20 text-gray-500">
            {{ $t('course_page.loading') }}
        </div>
        <div v-else-if="course" class="relative">
            <!-- Dark Header Section -->
            <div class="bg-[#1c1d1f] text-white pt-8 pb-12">
                <div class="max-w-7xl mx-auto px-4 lg:flex relative">
                   
                    <!-- Left Content (Header Info) -->
                    <div class="lg:w-2/3 lg:pr-8">
                         <!-- Breadcrumbs -->
                         <div class="text-[#cec0fc] text-sm font-bold mb-4 flex items-center space-x-2">
                             <span>{{ course.category?.name || $t('course_page.category') }}</span>
                             <span class="text-xs">›</span>
                             <span>{{ course.level?.name || $t('course_page.general') }}</span> 
                         </div>

                        <h1 class="text-4xl font-bold mb-4 leading-tight">{{ course.title }}</h1>
                        <p class="text-lg text-gray-200 mb-6 max-w-4xl">{{ course.subtitle }}</p>

                        <div class="flex flex-wrap items-center gap-4 mb-4 text-sm">
                            <span class="bg-[#eceb98] text-[#3d3c0a] px-2 py-1 rounded-sm font-bold text-xs">{{ $t('course.bestseller') }}</span>
                            <div class="flex items-center text-[#f69c08] space-x-1">
                                <span class="font-bold text-base">{{ course.rating_avg.toFixed(1) }}</span>
                                <div class="flex text-xs">
                                     <span v-for="n in 5" :key="n">{{ n <= Math.round(course.rating_avg) ? '★' : '☆' }}</span>
                                </div>
                            </div>
                            <span class="text-[#c0c4fc] underline">({{ Math.floor(Math.random() * 5000) + 100 }} {{ $t('course_page.ratings') }})</span>
                            <span class="text-white">{{ course.enrollment_count.toLocaleString() }} {{ $t('course_page.students') }}</span>
                        </div>

                        <div class="mb-4 text-sm">
                            {{ $t('course_page.created_by') }} <span class="text-[#c0c4fc] underline cursor-pointer">{{ course.instructor?.name }}</span>
                        </div>

                        <div class="flex items-center space-x-4 text-sm text-gray-200">
                             <div class="flex items-center space-x-1">
                                 <span>{{ $t('course_page.last_updated') }} {{ new Date(course.updated_at).toLocaleDateString($i18n.locale === 'ar' ? 'ar-EG' : 'en-US', { month: '2-digit', year: 'numeric' }) }}</span>
                             </div>
                             <div class="flex items-center space-x-1">
                                 <span>🌐</span>
                                 <span>{{ course.language }}</span>
                             </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mobile/Tablet Purchase Section (Visible < lg) -->
            <div class="md:hidden bg-white p-6 border-b border-gray-200">
                 <div class="max-w-4xl mx-auto">
                    <!-- Video Preview -->
                    <div class="relative w-full h-48 bg-gray-900 rounded-lg overflow-hidden mb-6">
                        <img :src="course.thumbnail" class="w-full h-full object-cover opacity-90" alt="preview">
                        <div class="absolute inset-0 flex items-center justify-center">
                            <div class="bg-white rounded-full p-4 shadow-lg">
                                <div class="w-0 h-0 border-t-[10px] border-t-transparent border-l-[18px] border-l-black border-b-[10px] border-b-transparent ml-1"></div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between mb-4">
                         <div class="flex items-baseline gap-2">
                             <span class="text-3xl font-bold text-gray-900">£{{ finalPrice }}</span>
                             <span v-if="hasDiscount" class="text-gray-500 line-through text-sm">£{{ originalPrice }}</span>
                         </div>
                         <span v-if="hasDiscount" class="text-green-700 bg-green-100 px-2 py-1 rounded font-bold text-sm whitespace-nowrap">
                            {{ discountPercentage }}% off
                         </span>
                    </div>

                    <div class="flex flex-row gap-3 w-full">
                        <button @click="addToCart" class="flex-1 bg-[#a435f0] text-white font-bold py-3 text-sm sm:text-base rounded-md hover:bg-[#8710d8] transition text-center whitespace-nowrap flex items-center justify-center">
                            {{ $t('course_page.add_to_cart') }}
                        </button>
                        <button @click="buyNow" class="flex-1 bg-white border border-black text-black font-bold py-3 text-sm sm:text-base rounded-md hover:bg-gray-50 transition text-center whitespace-nowrap flex items-center justify-center">
                            {{ $t('course_page.buy_now') }}
                        </button>
                    </div>

                    <div class="text-center text-xs text-gray-500 mt-4">
                        {{ $t('course_page.money_back') }} • {{ $t('course_page.lifetime_access') }}
                    </div>
                 </div>
            </div>

            <!-- Absolute Sidebar Container (Desktop >= lg) -->
            <div class="hidden md:block absolute top-8 right-0 left-0 pointer-events-none">
                 <div class="max-w-7xl mx-auto px-4 flex justify-end">
                     <!-- The Sidebar Card Itself -->
                     <div class="w-[340px] bg-white shadow-xl pointer-events-auto border border-gray-200 z-10">
                        <!-- Video Preview -->
                        <div class="relative w-full h-48 bg-gray-900 group cursor-pointer overflow-hidden border-b border-gray-200">
                            <!-- Thumbnail -->
                             <img :src="course.thumbnail" class="w-full h-full object-cover opacity-90 group-hover:opacity-75 transition" alt="preview">
                             <!-- Play Button Overlay -->
                             <div class="absolute inset-0 flex items-center justify-center">
                                 <div class="bg-white rounded-full p-4 shadow-lg group-hover:scale-110 transition">
                                     <div class="w-0 h-0 border-t-[10px] border-t-transparent border-l-[18px] border-l-black border-b-[10px] border-b-transparent ml-1"></div>
                                 </div>
                             </div>
                             <div @click="playPreview" class="absolute inset-0 cursor-pointer"></div>
                             <div class="absolute bottom-4 left-0 right-0 text-center text-white font-bold mb-2">{{ $t('course_page.preview_course') }}</div>
                        </div>

                        <div class="p-6">
                            <!-- Tabs (Visual Only) -->
                            <div class="flex border-b border-gray-200 mb-4 text-center">
                                <div class="flex-1 py-2 border-b-2 border-black font-bold cursor-pointer">{{ $t('course_page.personal') }}</div>
                                <div class="flex-1 py-2 text-gray-600 hover:text-gray-900 cursor-pointer">{{ $t('course_page.teams') }}</div>
                            </div>

                            <div class="flex items-baseline space-x-2 mb-4">
                                <!-- Price Logic (Desktop) -->
                                <span class="text-3xl font-bold text-gray-900">£{{ finalPrice }}</span>
                                <span v-if="hasDiscount" class="text-gray-500 line-through text-sm">£{{ originalPrice }}</span>
                                <span v-if="hasDiscount" class="text-gray-500 text-sm">
                                    {{ discountPercentage }}% off
                                </span>
                            </div>

                            <div class="flex flex-row gap-2 w-full mb-4">
                                <button @click="addToCart" class="flex-1 bg-[#a435f0] text-white font-bold py-3 text-sm rounded-md hover:bg-[#8710d8] transition text-center whitespace-nowrap flex items-center justify-center">
                                    {{ $t('course_page.add_to_cart') }}
                                </button>
                                <button @click="buyNow" class="flex-1 bg-white border border-black text-black font-bold py-3 text-sm rounded-md hover:bg-gray-50 transition text-center whitespace-nowrap flex items-center justify-center">
                                    {{ $t('course_page.buy_now') }}
                                </button>
                            </div>
                            
                             <div class="flex justify-center items-center mt-2">
                                <button @click="toggleWishlist" class="text-sm font-bold flex items-center hover:text-red-600" :class="wishlistStore.hasItem(course.id) ? 'text-red-500' : 'text-gray-800'">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" :fill="wishlistStore.hasItem(course.id) ? 'currentColor' : 'none'" viewBox="0 0 24 24" stroke="currentColor">
                                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                    </svg>
                                    {{ wishlistStore.hasItem(course.id) ? $t('course_page.wishlisted') : $t('course_page.add_to_wishlist') }}
                                </button>
                            </div>

                            <div class="text-center text-xs text-gray-500 mb-4">
                                {{ $t('course_page.money_back') }}
                            </div>
                            
                            <div class="text-xs text-gray-900 space-y-2">
                                <div class="font-bold mb-1">{{ $t('course_page.includes') }}</div>
                                <div class="flex items-center"><span class="w-5">📺</span> {{ course.estimated_hours || 12 }} {{ $t('course_page.hours_video') }}</div>
                                <div class="flex items-center"><span class="w-5">📄</span> 5 {{ $t('course_page.articles') }}</div>
                                <div class="flex items-center"><span class="w-5">⬇️</span> 10 {{ $t('course_page.resources') }}</div>
                                <div class="flex items-center"><span class="w-5">♾️</span> {{ $t('course_page.full_access') }}</div>
                                <div class="flex items-center"><span class="w-5">📱</span> {{ $t('course_page.mobile_access') }}</div>
                                <div class="flex items-center"><span class="w-5">🏆</span> {{ $t('course_page.certificate') }}</div>
                            </div>
                        </div>
                     </div>
                 </div>
            </div>

            <!-- Main Content Area -->
            <div class="max-w-7xl mx-auto px-4 py-8 lg:flex">
                <div class="lg:w-2/3 lg:pr-8">
                     <!-- What you'll learn (Mock) -->
                     <div class="border border-gray-300 p-6 mb-8 mt-4">
                         <h2 class="text-xl font-bold mb-4 text-gray-900">{{ $t('course_page.what_learn') }}</h2>
                         <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm text-gray-700">
                             <div class="flex"><span class="mr-2">✓</span> Understand Agentic AI fundamentals</div>
                             <div class="flex"><span class="mr-2">✓</span> Build autonomous AI agents</div>
                             <div class="flex"><span class="mr-2">✓</span> Leverage LLMs for business automation</div>
                             <div class="flex"><span class="mr-2">✓</span> Master prompt engineering strategies</div>
                         </div>
                     </div>

                    <h2 class="text-2xl font-bold mb-4 text-gray-900">{{ $t('course_page.content') }}</h2>
                    <div class="border border-gray-200 rounded-lg mb-8 text-sm">
                         <!-- Stats Header -->
                         <div class="bg-gray-50 p-3 border-b border-gray-200 flex justify-between text-gray-600">
                             <span>{{ course.sections?.length || 0 }} {{ $t('course_page.sections') }} • {{ course.sections?.reduce((acc, s) => acc + (s.lectures?.length || 0), 0) }} {{ $t('course_page.lectures') }} • 12h 42m {{ $t('course_page.total_length') }}</span>
                             <span class="text-[#a435f0] font-bold cursor-pointer">{{ $t('course_page.expand_all') }}</span>
                         </div>
                         <!-- Sections -->
                         <!-- Sections -->
                        <div v-for="section in course.sections" :key="section.id" class="border-b border-gray-200 last:border-0">
                            <div @click="toggleSection(section.id)" class="bg-gray-50 p-4 flex justify-between items-center cursor-pointer hover:bg-gray-100 transition-colors">
                                <span class="font-bold flex items-center text-gray-800">
                                    <span class="mr-3 text-xs w-4 transition-transform duration-200" :class="{'rotate-180': isExpanded(section.id)}">▼</span>
                                    {{ section.title }}
                                </span>
                                <span class="text-xs text-gray-600">{{ section.lectures?.length || 0 }} {{ $t('course_page.lectures') }}</span>
                            </div>
                            
                            <!-- Lectures List -->
                            <div v-show="isExpanded(section.id)" class="bg-white">
                                <div v-for="lecture in section.lectures" :key="lecture.id" class="flex items-start p-3 pl-10 border-b border-gray-100 last:border-0 hover:bg-gray-50 transition-colors">
                                    <div class="mr-3 mt-1 text-gray-400">
                                        <!-- Icons based on type -->
                                        <svg v-if="lecture.type === 'video'" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <svg v-else-if="lecture.type === 'quiz'" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                        </svg>
                                        <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <div class="text-sm text-gray-700">{{ lecture.title }}</div>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            <span v-if="lecture.duration_minutes" class="text-xs text-gray-500">{{ lecture.duration_minutes }} min</span>
                                            <span v-if="lecture.preview" class="text-xs font-bold text-purple-600 cursor-pointer hover:underline">Preview</span>
                                        </div>
                                    </div>
                                </div>
                                <div v-if="(!section.lectures || section.lectures.length === 0)" class="p-4 pl-10 text-xs text-gray-400 italic">
                                    {{ $t('course_page.no_lectures') }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <h2 class="text-2xl font-bold mb-4 text-gray-900">{{ $t('course_page.description') }}</h2>
                    <div class="prose max-w-none text-gray-800 text-sm leading-relaxed mb-8" v-html="course.description"></div>
                    
                    <!-- Social Features -->
                    <ReviewsSection :courseId="course.id" />
                    <QnASection v-if="course.is_enrolled" :courseId="course.id" />
                </div>
            </div>
        </div>
        
        <div v-else class="text-center py-20 text-red-500">
            {{ $t('course_page.not_found') }}
        </div>

        <!-- Preview Video Modal -->
        <div v-if="showPreviewModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/90" @click.self="showPreviewModal = false">
            <div class="relative w-full max-w-4xl">
                <button @click="showPreviewModal = false" class="absolute -top-10 right-0 text-white hover:text-gray-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
                <video 
                    :src="course.preview_video_url" 
                    controls 
                    autoplay 
                    class="w-full rounded-lg shadow-2xl"
                >
                    Your browser does not support the video tag.
                </video>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted, computed, defineAsyncComponent, ref } from 'vue';

import { useRoute, useRouter } from 'vue-router';
import { useCourseStore } from '../stores/course';
import { useLearningStore } from '../stores/learning';
import { useAuthStore } from '../stores/auth';
import { useCartStore } from '../stores/cart';
import { useWishlistStore } from '../stores/wishlist';
import Navbar from '../components/Navbar.vue';

// Lazy load social components
const ReviewsSection = defineAsyncComponent(() => import('../components/ReviewsSection.vue'));
const QnASection = defineAsyncComponent(() => import('../components/QnASection.vue'));

const route = useRoute();
const router = useRouter();
const courseStore = useCourseStore();
const learningStore = useLearningStore();
const authStore = useAuthStore();
const cartStore = useCartStore();
const wishlistStore = useWishlistStore();

const course = computed(() => courseStore.currentCourse);

const originalPrice = computed(() => {
    return parseFloat(course.value?.price || 0);
});

const discountPriceVal = computed(() => {
    return parseFloat(course.value?.discount_price || 0);
});

const hasDiscount = computed(() => {
    const p = originalPrice.value;
    const d = discountPriceVal.value;
    return d > 0 && d < p;
});

const finalPrice = computed(() => {
    return hasDiscount.value ? discountPriceVal.value : originalPrice.value;
});

const discountPercentage = computed(() => {
    if (!hasDiscount.value) return 0;
    return Math.round(((originalPrice.value - finalPrice.value) / originalPrice.value) * 100);
});

onMounted(() => {
    courseStore.fetchCourseDetail(route.params.slug);
});

const enroll = async () => {
    await learningStore.enroll(course.value.id);
    alert('Enrolled!'); // In real app, redirect to player
    router.push(`/learn/course/${course.value.slug}`);
};

const addToCart = () => {
    cartStore.addItem(course.value);
    // Optional: show toast
};

const buyNow = () => {
    cartStore.addItem(course.value);
    router.push('/cart');
};

const toggleWishlist = () => {
    wishlistStore.toggleItem(course.value);
};

// Curriculum Expansion Logic
const expandedSections = ref(new Set());

const toggleSection = (sectionId) => {
    if (expandedSections.value.has(sectionId)) {
        expandedSections.value.delete(sectionId);
    } else {
        expandedSections.value.add(sectionId);
    }
};

const isExpanded = (sectionId) => expandedSections.value.has(sectionId);

// Preview Video Modal
const showPreviewModal = ref(false);

const playPreview = () => {
    if (course.value?.preview_video_url) {
        showPreviewModal.value = true;
    } else {
        alert('No preview video available for this course');
    }
};
</script>
