import { defineStore } from 'pinia';
import axios from 'axios';

export const useLearningStore = defineStore('learning', {
    state: () => ({
        enrolledCourses: [], // My learning
        currentProgress: [], // for active player
    }),
    actions: {
        async enroll(courseId) {
            await axios.post(`/api/courses/${courseId}/enroll`);
            // refresh my courses
        },
        async fetchMyCourses() {
            const response = await axios.get('/api/my-courses');
            this.enrolledCourses = response.data.data;
        },
        async fetchProgress(courseId) {
            const response = await axios.get(`/api/courses/${courseId}/progress`);
            this.currentProgress = response.data.completed_lectures;
        },
        async markComplete(lectureId, completed = true) {
            await axios.post(`/api/lectures/${lectureId}/progress`, { completed });
            // update local state
            if (completed && !this.currentProgress.includes(lectureId)) {
                this.currentProgress.push(lectureId);
            } else if (!completed) {
                this.currentProgress = this.currentProgress.filter(id => id !== lectureId);
            }
        }
    }
});
