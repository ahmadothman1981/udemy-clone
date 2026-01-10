import { defineStore } from 'pinia';
import axios from 'axios';

export const useCourseStore = defineStore('course', {
    state: () => ({
        courses: [],
        categories: [],
        levels: [],
        currentCourse: null,
        loading: false,
    }),
    actions: {
        async fetchCategories() {
            try {
                const response = await axios.get('/api/categories');
                this.categories = response.data.data;
            } catch (e) {
                console.error(e);
            }
        },
        async fetchLevels() {
            try {
                const response = await axios.get('/api/levels');
                this.levels = response.data.data || response.data;
            } catch (e) {
                console.error(e);
            }
        },
        async fetchCourses(params = {}) {
            this.loading = true;
            try {
                const response = await axios.get('/api/courses', { params });
                this.courses = response.data.data; // assuming API Resource collection format
            } finally {
                this.loading = false;
            }
        },
        async fetchCourseDetail(id) {
            this.loading = true;
            try {
                const response = await axios.get(`/api/courses/${id}`);
                this.currentCourse = response.data.data;
            } finally {
                this.loading = false;
            }
        }
    }
});
