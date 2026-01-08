<template>
    <div>
        <Navbar />
        <div class="max-w-7xl mx-auto px-4 py-8">
            <h1 class="text-3xl font-bold mb-8">Shopping Cart</h1>

            <div v-if="cartStore.count === 0" class="text-center py-20 bg-white border border-gray-200 rounded-lg">
                <div class="mb-4 text-6xl">🛒</div>
                <h2 class="text-xl font-bold text-gray-700 mb-2">Your cart is empty</h2>
                <p class="text-gray-500 mb-6">Keep shopping to find a course!</p>
                <router-link to="/" class="bg-purple-600 text-white px-6 py-3 rounded font-bold hover:bg-purple-700 transition">
                    Keep Shopping
                </router-link>
            </div>

            <div v-else class="lg:flex gap-8">
                <!-- Cart Items -->
                <div class="flex-1">
                    <div class="text-sm font-bold text-gray-700 mb-2">{{ cartStore.count }} Courses in Cart</div>
                    <div v-for="item in cartStore.items" :key="item.id" class="flex border border-gray-200 rounded p-4 mb-4 bg-white relative">
                         <!-- Image -->
                         <div class="w-24 h-24 sm:w-32 sm:h-32 bg-gray-200 flex-shrink-0 cursor-pointer" @click="$router.push(`/course/${item.slug || item.id}`)">
                             <img :src="item.thumbnail" class="w-full h-full object-cover" alt="Course Thumbnail">
                         </div>
                         
                         <!-- Info -->
                         <div class="ml-4 flex-1">
                             <div class="flex justify-between items-start">
                                 <div>
                                     <h3 class="text-lg font-bold text-gray-900 line-clamp-2 hover:underline cursor-pointer" @click="$router.push(`/course/${item.slug || item.id}`)">
                                         {{ item.title }}
                                     </h3>
                                     <p class="text-sm text-gray-500 mt-1">By {{ item.instructor_name || 'Instructor' }}</p>
                                 </div>
                                 <div class="text-right">
                                     <div class="text-purple-600 font-bold text-lg">£{{ item.price }}</div>
                                     <div class="text-gray-400 line-through text-sm">£{{ (item.price * 1.5).toFixed(2) }}</div>
                                 </div>
                             </div>
                             
                             <div class="mt-4 flex items-center space-x-4">
                                 <button @click="removeFromCart(item.id)" class="text-xs text-red-500 font-bold hover:underline">Remove</button>
                                 <button @click="moveToWishlist(item)" class="text-xs text-blue-500 font-bold hover:underline">Move to Wishlist</button>
                             </div>
                         </div>
                    </div>
                </div>

                <!-- Checkout Sidebar -->
                 <div class="w-full lg:w-1/3 flex-shrink-0">
                     <div class="bg-white p-6 shadow-sm border border-gray-200 rounded sticky top-24">
                         <div class="text-gray-500 font-bold text-lg mb-2">Total:</div>
                         <div class="text-4xl font-bold text-gray-900 mb-2">£{{ cartStore.total.toFixed(2) }}</div>
                         <div class="text-gray-500 line-through mb-6">£{{ (cartStore.total * 1.5).toFixed(2) }}</div>
                         
                         <button @click="checkout" class="w-full bg-purple-600 text-white font-bold py-3 text-lg hover:bg-purple-700 transition mb-4">
                             Checkout
                         </button>
                         
                         <div class="border-t border-gray-200 pt-4">
                             <h4 class="font-bold text-sm mb-2">Promotions</h4>
                             <div class="flex">
                                 <input type="text" placeholder="Enter Coupon" class="flex-1 border border-gray-300 p-2 text-sm rounded-l-sm focus:outline-none focus:border-black">
                                 <button class="bg-gray-800 text-white px-4 text-sm font-bold rounded-r-sm hover:bg-gray-700">Apply</button>
                             </div>
                         </div>
                     </div>
                 </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useCartStore } from '../stores/cart';
import { useWishlistStore } from '../stores/wishlist';
import { useRouter } from 'vue-router';
import Navbar from '../components/Navbar.vue';

const router = useRouter();
const cartStore = useCartStore();
const wishlistStore = useWishlistStore();

const removeFromCart = (id) => {
    cartStore.removeItem(id);
};

const moveToWishlist = (item) => {
    wishlistStore.addItem(item);
    cartStore.removeItem(item.id);
};

const checkout = () => {
    router.push('/checkout');
};
</script>
