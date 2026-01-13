<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled via policy in controller
    }

    public function rules(): array
    {
        return [
            'question_text' => 'required|string|max:1000',
            'options' => 'required|array|min:2|max:10',
            'options.*' => 'required|string|max:500',
            'correct_answer' => 'required|string|max:500',
            'points' => 'integer|min:1|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'question_text.required' => 'Question text is required.',
            'options.min' => 'At least 2 options are required.',
            'options.max' => 'Maximum 10 options allowed.',
            'correct_answer.required' => 'You must specify the correct answer.',
        ];
    }
}
