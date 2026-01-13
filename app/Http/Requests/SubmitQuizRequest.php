<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitQuizRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled in controller
    }

    public function rules(): array
    {
        return [
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|integer|exists:questions,id',
            'answers.*.answer' => 'required|string',
            'started_at' => 'nullable|date',
        ];
    }

    public function messages(): array
    {
        return [
            'answers.required' => 'You must provide answers to submit the quiz.',
            'answers.*.question_id.exists' => 'One or more questions are invalid.',
            'answers.*.answer.required' => 'All questions must be answered.',
        ];
    }
}
