<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LectureResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'type' => $this->type,
            'order' => $this->order,
            'preview' => $this->preview,
            // Only show content if enrolled or it's a preview? Logic handled in controller/query usually, but here we can hide if not loaded
            // Only show content if enrolled or it's a preview or owner
            'video_url' => $this->when(
                $this->preview || 
                ($request->user() && (
                    $request->user()->owns($this->section->course) || 
                    $this->section->course->isEnrolledBy($request->user())
                )), 
                $this->video_url
            ),
            'duration_minutes' => $this->duration_minutes,
        ];
    }
}
