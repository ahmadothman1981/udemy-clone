<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'subtitle' => $this->subtitle,
            'description' => $this->when($request->routeIs('courses.show'), $this->description), // Full description only on details
            'thumbnail' => $this->thumbnail,
            'price' => $this->price,
            'discount_price' => $this->discount_price,
            'level' => $this->level?->name,
            'language' => $this->language,
            'rating_avg' => $this->rating_avg,
            'enrollment_count' => $this->enrollment_count,
            'instructor' => new UserResource($this->whenLoaded('instructor')),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'sections' => SectionResource::collection($this->whenLoaded('sections')), // Need SectionResource
            'is_enrolled' => $this->isEnrolledBy($request->user()),
            'progress' => $this->when(isset($this->progress), $this->progress),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
