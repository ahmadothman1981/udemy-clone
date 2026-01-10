import { describe, it, expect, beforeEach, vi } from 'vitest';
import { setActivePinia, createPinia } from 'pinia';
import { useQuizStore } from '../quiz';

// Mock axios
vi.mock('axios', () => ({
    default: {
        get: vi.fn(),
        post: vi.fn(),
    },
}));

import axios from 'axios';

describe('Quiz Store', () => {
    beforeEach(() => {
        setActivePinia(createPinia());
        vi.clearAllMocks();
    });

    it('initializes with default state', () => {
        const store = useQuizStore();

        expect(store.currentQuiz).toBeNull();
        expect(store.questions).toEqual([]);
        expect(store.userAnswers).toEqual({});
        expect(store.result).toBeNull();
        expect(store.loading).toBe(false);
    });

    it('sets answer correctly', () => {
        const store = useQuizStore();

        store.setAnswer(1, 'Option A');
        store.setAnswer(2, 'Option B');

        expect(store.userAnswers[1]).toBe('Option A');
        expect(store.userAnswers[2]).toBe('Option B');
        expect(store.answeredCount).toBe(2);
    });

    it('calculates allAnswered correctly', () => {
        const store = useQuizStore();
        store.questions = [
            { id: 1, question_text: 'Q1' },
            { id: 2, question_text: 'Q2' },
        ];

        expect(store.allAnswered).toBe(false);

        store.setAnswer(1, 'A');
        expect(store.allAnswered).toBe(false);

        store.setAnswer(2, 'B');
        expect(store.allAnswered).toBe(true);
    });

    it('resets quiz state', () => {
        const store = useQuizStore();
        store.userAnswers = { 1: 'A', 2: 'B' };
        store.result = { passed: true };
        store.error = 'Some error';

        store.resetQuiz();

        expect(store.userAnswers).toEqual({});
        expect(store.result).toBeNull();
        expect(store.error).toBeNull();
    });

    it('clears quiz completely', () => {
        const store = useQuizStore();
        store.currentQuiz = { id: 1, title: 'Test' };
        store.questions = [{ id: 1 }];
        store.attempts = [{ id: 1 }];

        store.clearQuiz();

        expect(store.currentQuiz).toBeNull();
        expect(store.questions).toEqual([]);
        expect(store.attempts).toEqual([]);
    });

    it('fetches quiz successfully', async () => {
        const mockQuizData = {
            quiz: { id: 1, title: 'Test Quiz', questions: [{ id: 1 }] },
            attempts: [],
            best_score: null,
            passed: false,
        };

        axios.get.mockResolvedValueOnce({ data: mockQuizData });

        const store = useQuizStore();
        await store.fetchQuiz(1, 1);

        expect(store.currentQuiz).toEqual(mockQuizData.quiz);
        expect(store.questions).toEqual(mockQuizData.quiz.questions);
        expect(store.loading).toBe(false);
    });

    it('handles fetch error', async () => {
        axios.get.mockRejectedValueOnce({
            response: { data: { message: 'Not enrolled' } }
        });

        const store = useQuizStore();
        await store.fetchQuiz(1, 1);

        expect(store.error).toBe('Not enrolled');
        expect(store.loading).toBe(false);
    });
});
