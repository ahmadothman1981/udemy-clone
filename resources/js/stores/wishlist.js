import { defineStore } from 'pinia';

export const useWishlistStore = defineStore('wishlist', {
    state: () => ({
        items: JSON.parse(localStorage.getItem('wishlist_items')) || [],
    }),
    getters: {
        count: (state) => state.items.length,
        hasItem: (state) => (courseId) => state.items.some(item => item.id === courseId),
    },
    actions: {
        toggleItem(course) {
            if (this.hasItem(course.id)) {
                this.removeItem(course.id);
            } else {
                this.addItem(course);
            }
        },
        addItem(course) {
            if (!this.hasItem(course.id)) {
                this.items.push({
                    id: course.id,
                    title: course.title,
                    price: course.price,
                    thumbnail: course.thumbnail,
                    slug: course.slug
                });
                this.save();
            }
        },
        removeItem(courseId) {
            this.items = this.items.filter(item => item.id !== courseId);
            this.save();
        },
        save() {
            localStorage.setItem('wishlist_items', JSON.stringify(this.items));
        }
    }
});
