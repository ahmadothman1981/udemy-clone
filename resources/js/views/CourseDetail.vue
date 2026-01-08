<template>
    <div>
        <Navbar />
        <div v-if="courseStore.loading" class="text-center py-20 text-gray-500">
            Loading course details...
        </div>
        <div v-else-if="course" class="relative">
            <!-- Dark Header Section -->
            <div class="bg-[#1c1d1f] text-white pt-8 pb-12">
                <div class="max-w-7xl mx-auto px-4 lg:flex relative">
                   
                    <!-- Left Content (Header Info) -->
                    <div class="lg:w-2/3 lg:pr-8">
                         <!-- Breadcrumbs -->
                         <div class="text-[#cec0fc] text-sm font-bold mb-4 flex items-center space-x-2">
                             <span>{{ course.category?.name || 'Category' }}</span>
                             <span class="text-xs">›</span>
                             <span>{{ course.level?.name || 'General' }}</span> 
                         </div>

                        <h1 class="text-4xl font-bold mb-4 leading-tight">{{ course.title }}</h1>
                        <p class="text-lg text-gray-200 mb-6 max-w-4xl">{{ course.subtitle }}</p>

                        <div class="flex flex-wrap items-center gap-4 mb-4 text-sm">
                            <span class="bg-[#eceb98] text-[#3d3c0a] px-2 py-1 rounded-sm font-bold text-xs">Bestseller</span>
                            <div class="flex items-center text-[#f69c08] space-x-1">
                                <span class="font-bold text-base">{{ course.rating_avg.toFixed(1) }}</span>
                                <div class="flex text-xs">
                                     <span v-for="n in 5" :key="n">{{ n <= Math.round(course.rating_avg) ? '★' : '☆' }}</span>
                                </div>
                            </div>
                            <span class="text-[#c0c4fc] underline">({{ Math.floor(Math.random() * 5000) + 100 }} ratings)</span>
                            <span class="text-white">{{ course.enrollment_count.toLocaleString() }} students</span>
                        </div>

                        <div class="mb-4 text-sm">
                            Created by <span class="text-[#c0c4fc] underline cursor-pointer">{{ course.instructor?.name }}</span>
                        </div>

                        <div class="flex items-center space-x-4 text-sm text-gray-200">
                             <div class="flex items-center space-x-1">
                                 <span>Last updated {{ new Date(course.updated_at).toLocaleDateString('en-US', { month: '2-digit', year: 'numeric' }) }}</span>
                             </div>
                             <div class="flex items-center space-x-1">
                                 <span>🌐</span>
                                 <span>{{ course.language }}</span>
                             </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Absolute Sidebar Container (Desktop) -->
            <!-- We use a wrapper to center it relative to the page, but position absolute to overlay -->
            <div class="hidden lg:block absolute top-8 right-0 left-0 pointer-events-none">
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
                             <div class="absolute bottom-4 left-0 right-0 text-center text-white font-bold mb-2">Preview this course</div>
                        </div>

                        <div class="p-6">
                            <!-- Tabs (Visual Only for now) -->
                            <div class="flex border-b border-gray-200 mb-4 text-center">
                                <div class="flex-1 py-2 border-b-2 border-black font-bold cursor-pointer">Personal</div>
                                <div class="flex-1 py-2 text-gray-600 hover:text-gray-900 cursor-pointer">Teams</div>
                            </div>

                            <div class="flex items-baseline space-x-2 mb-4">
                                <span class="text-3xl font-bold text-gray-900">£{{ course.price }}</span>
                                <span class="text-gray-500 line-through text-sm">£{{ (course.price * 1.25).toFixed(2) }}</span>
                                <span class="text-gray-500 text-sm">29% off</span>
                            </div>

                            <button @click="addToCart" class="w-full bg-[#a435f0] text-white font-bold py-3 text-base mb-2 hover:bg-[#8710d8] transition">
                                Add to cart
                            </button>
                            <button @click="buyNow" class="w-full bg-white border border-black text-black font-bold py-3 text-base mb-4 hover:bg-gray-50 transition">
                                Buy now
                            </button>
                            
                             <div class="flex justify-center items-center mt-2">
                                <button @click="toggleWishlist" class="text-sm font-bold flex items-center hover:text-red-600" :class="wishlistStore.hasItem(course.id) ? 'text-red-500' : 'text-gray-800'">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" :fill="wishlistStore.hasItem(course.id) ? 'currentColor' : 'none'" viewBox="0 0 24 24" stroke="currentColor">
                                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                    </svg>
                                    {{ wishlistStore.hasItem(course.id) ? 'Wishlisted' : 'Add to Wishlist' }}
                                </button>
                            </div>

                            <div class="text-center text-xs text-gray-500 mb-4">
                                30-Day Money-Back Guarantee
                            </div>
                            
                            <div class="text-xs text-gray-900 space-y-2">
                                <div class="font-bold mb-1">This course includes:</div>
                                <div class="flex items-center"><span class="w-5">📺</span> {{ course.estimated_hours || 12 }} hours on-demand video</div>
                                <div class="flex items-center"><span class="w-5">📄</span> 5 articles</div>
                                <div class="flex items-center"><span class="w-5">⬇️</span> 10 downloadable resources</div>
                                <div class="flex items-center"><span class="w-5">♾️</span> Full lifetime access</div>
                                <div class="flex items-center"><span class="w-5">📱</span> Access on mobile and TV</div>
                                <div class="flex items-center"><span class="w-5">🏆</span> Certificate of completion</div>
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
                         <h2 class="text-xl font-bold mb-4 text-gray-900">What you'll learn</h2>
                         <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm text-gray-700">
                             <div class="flex"><span class="mr-2">✓</span> Understand Agentic AI fundamentals</div>
                             <div class="flex"><span class="mr-2">✓</span> Build autonomous AI agents</div>
                             <div class="flex"><span class="mr-2">✓</span> Leverage LLMs for business automation</div>
                             <div class="flex"><span class="mr-2">✓</span> Master prompt engineering strategies</div>
                         </div>
                     </div>

                    <h2 class="text-2xl font-bold mb-4 text-gray-900">Course content</h2>
                    <div class="border border-gray-200 rounded-sm mb-8 text-sm">
                         <!-- Stats Header -->
                         <div class="bg-gray-50 p-3 border-b border-gray-200 flex justify-between text-gray-600">
                             <span>{{ course.sections?.length || 0 }} sections • {{ course.sections?.reduce((acc, s) => acc + (s.lectures?.length || 0), 0) }} lectures • 12h 42m total length</span>
                             <span class="text-[#a435f0] font-bold cursor-pointer">Expand all sections</span>
                         </div>
                         <!-- Sections -->
                        <div v-for="section in course.sections" :key="section.id" class="border-b border-gray-200 last:border-0">
                            <div class="bg-gray-50 p-4 flex justify-between items-center cursor-pointer hover:bg-gray-100">
                                <span class="font-bold flex items-center">
                                    <span class="mr-2 text-xs">▼</span> {{ section.title }}
                                </span>
                                <span class="text-xs text-gray-600">{{ section.lectures?.length || 0 }} lectures • 45m</span>
                            </div>
                            <!-- Lectures would go here if expanded -->
                        </div>
                    </div>

                    <h2 class="text-2xl font-bold mb-4 text-gray-900">Description</h2>
                    <div class="prose max-w-none text-gray-800 text-sm leading-relaxed mb-8" v-html="course.description"></div>
                    
                    <!-- Social Features -->
                    <ReviewsSection :courseId="course.id" />
                    <QnASection v-if="course.is_enrolled" :courseId="course.id" />
                </div>
            </div>
        </div>
        
        <div v-else class="text-center py-20 text-red-500">
            Course not found.
        </div>
    </div>
</template>

<script setup>
import { onMounted, computed, defineAsyncComponent } from 'vue';
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
</script>
