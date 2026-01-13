<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Policy check in controller
    }

    public function rules(): array
    {
        $rules = [
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:500',
            'description' => 'nullable|string|max:50000',
            'price' => 'nullable|numeric|min:0|max:999999',
            'discount_price' => 'nullable|numeric|min:0',
            'language' => 'nullable|string|max:50',
            'level_id' => 'nullable|integer|exists:course_levels,id',
            'category_id' => 'nullable|integer|exists:categories,id',
            'thumbnail' => 'nullable|string|max:500',
            'preview_video_url' => 'nullable|string|max:500',
        ];

        // For updates, make title optional
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['title'] = 'sometimes|string|max:255';
        }

        return $rules;
    }

    /**
     * Sanitize description to prevent XSS
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('description')) {
            $this->merge([
                'description' => $this->sanitizeHtml($this->description),
            ]);
        }
    }

    /**
     * Basic HTML sanitization - allows safe tags only
     */
    private function sanitizeHtml(?string $html): ?string
    {
        if (!$html) {
            return $html;
        }

        // Allow only safe HTML tags
        $allowedTags = '<p><br><strong><b><em><i><u><ul><ol><li><h1><h2><h3><h4><h5><h6><a><blockquote><pre><code>';

        $html = strip_tags($html, $allowedTags);

        // Strip all attributes from tags to prevent XSS (e.g. onclick, javascript: href)
        // This is a strict approach. For handling links safely, a library like HTMLPurifier is recommended.
        return preg_replace('/<([a-z][a-z0-9]*)[^>]*?(\/?)>/i', '<$1$2>', $html);
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Course title is required.',
            'title.max' => 'Course title cannot exceed 255 characters.',
            'price.min' => 'Price cannot be negative.',
            'discount_price.min' => 'Discount price cannot be negative.',
        ];
    }
}
