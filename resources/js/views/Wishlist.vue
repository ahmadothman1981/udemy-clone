<template>
    <div>
        <Navbar />
        <div class="max-w-7xl mx-auto px-4 py-8">
            <h1 class="text-3xl font-bold mb-8">Wishlist</h1>

            <div v-if="wishlistStore.count === 0" class="text-center py-20 bg-white border border-gray-200 rounded-lg">
                <div class="mb-4 text-6xl">❤️</div>
                <h2 class="text-xl font-bold text-gray-700 mb-2">Your wishlist is empty</h2>
                <p class="text-gray-500 mb-6">Save courses to watch later!</p>
                <router-link to="/" class="bg-black text-white px-6 py-3 rounded font-bold hover:bg-gray-800 transition">
                   Browse Courses
                </router-link>
            </div>

            <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                 <div v-for="item in wishlistStore.items" :key="item.id" class="group flex flex-col bg-white border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition-shadow duration-200 cursor-pointer">
                    <!-- Thumbnail -->
                    <div class="aspect-video bg-gray-200 relative overflow-hidden" @click="$router.push(`/course/${item.slug || item.id}`)">
                        <img :src="item.thumbnail" class="object-cover w-full h-full" alt="Course Thumbnail">
                        <!-- Overlay -->
                        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-10 transition-all duration-200"></div>
                    </div>

                    <div class="p-4 flex flex-col flex-1">
                        <h3 class="font-bold text-gray-900 leading-tight mb-1 line-clamp-2 h-10 overflow-hidden" @click="$router.push(`/course/${item.slug || item.id}`)">
                            {{ item.title }}
                        </h3>
                        <div class="text-xs text-gray-500 mb-2 truncate">By {{ item.instructor_name || 'Instructor' }}</div>
                        
                        <div class="mt-auto">
                            <div class="font-bold text-gray-900">£{{ item.price }}</div>
                            <div class="mt-4 flex flex-col space-y-2">
                                <button @click="moveToCart(item)" class="text-sm border border-black w-full py-2 font-bold hover:bg-gray-50">Add to Cart</button>
                                <button @click="removeFromWishlist(item.id)" class="text-xs text-red-500 hover:underline text-center">Remove</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useWishlistStore } from '../stores/wishlist';
import { useCartStore } from '../stores/cart';
import Navbar from '../components/Navbar.vue';

const wishlistStore = useWishlistStore();
const cartStore = useCartStore();

const removeFromWishlist = (id) => {
    wishlistStore.removeItem(id);
};

const moveToCart = (item) => {
    cartStore.addItem(item);
    // Optional: remove from wishlist after adding to cart? 
    // Usually wishlist items stay unless explicitly removed, or moved.
    // Udemy moves it.
    wishlistStore.removeItem(item.id);
};
</script>
