import { defineStore } from 'pinia';

export const useCartStore = defineStore('cart', {
    state: () => ({
        items: JSON.parse(localStorage.getItem('cart_items')) || [],
    }),
    getters: {
        count: (state) => state.items.length,
        total: (state) => state.items.reduce((acc, item) => acc + parseFloat(item.price), 0),
        hasItem: (state) => (courseId) => state.items.some(item => item.id === courseId),
    },
    actions: {
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
        clear() {
            this.items = [];
            this.save();
        },
        save() {
            localStorage.setItem('cart_items', JSON.stringify(this.items));
        }
    }
});
