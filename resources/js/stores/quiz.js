import { defineStore } from 'pinia';
import axios from 'axios';

export const useQuizStore = defineStore('quiz', {
    state: () => ({
        currentQuiz: null,
        questions: [],
        attempts: [],
        currentAttempt: null,
        userAnswers: {},
        result: null,
        loading: false,
        submitting: false,
        error: null,
        startedAt: null,
    }),

    getters: {
        totalQuestions: (state) => state.questions.length,
        answeredCount: (state) => Object.keys(state.userAnswers).length,
        allAnswered: (state) => state.questions.length > 0 &&
            Object.keys(state.userAnswers).length === state.questions.length,
        hasPassed: (state) => state.attempts.some(a => a.passed),
        bestScore: (state) => Math.max(...state.attempts.map(a => a.percentage || 0), 0),
    },

    actions: {
        async fetchQuiz(courseId, quizId) {
            this.loading = true;
            this.error = null;
            this.result = null;
            this.userAnswers = {};

            try {
                const response = await axios.get(`/api/courses/${courseId}/quizzes/${quizId}`);
                this.currentQuiz = response.data.quiz;
                this.questions = response.data.quiz.questions || [];
                this.attempts = response.data.attempts || [];
                this.startedAt = new Date().toISOString();
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to load quiz';
                console.error('Quiz fetch error:', error);
            } finally {
                this.loading = false;
            }
        },

        setAnswer(questionId, answer) {
            this.userAnswers[questionId] = answer;
        },

        async submitQuiz(courseId, quizId) {
            if (!this.allAnswered) {
                this.error = 'Please answer all questions before submitting';
                return null;
            }

            this.submitting = true;
            this.error = null;

            try {
                const answers = Object.entries(this.userAnswers).map(([questionId, answer]) => ({
                    question_id: parseInt(questionId),
                    answer: answer,
                }));

                const response = await axios.post(`/api/courses/${courseId}/quizzes/${quizId}/submit`, {
                    answers,
                    started_at: this.startedAt,
                });

                this.result = response.data;

                // Add to attempts
                this.attempts.unshift({
                    id: response.data.attempt_id,
                    score: response.data.score,
                    total_points: response.data.total_points,
                    percentage: response.data.percentage,
                    passed: response.data.passed,
                    created_at: new Date().toISOString(),
                });

                return response.data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to submit quiz';
                console.error('Quiz submit error:', error);
                return null;
            } finally {
                this.submitting = false;
            }
        },

        resetQuiz() {
            this.userAnswers = {};
            this.result = null;
            this.error = null;
            this.startedAt = new Date().toISOString();
        },

        clearQuiz() {
            this.currentQuiz = null;
            this.questions = [];
            this.attempts = [];
            this.userAnswers = {};
            this.result = null;
            this.error = null;
            this.startedAt = null;
        }
    }
});
