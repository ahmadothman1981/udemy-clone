<?php

namespace App\Jobs;

use App\Models\Lecture;
use FFMpeg\Format\Video\X264;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;
use Illuminate\Support\Facades\Log;

class ProcessLectureVideo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $lecture;

    /**
     * Create a new job instance.
     */
    public function __construct(Lecture $lecture)
    {
        $this->lecture = $lecture;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->lecture->update(['processing_state' => 'processing']);

        $lowBitrate = (new X264)->setKiloBitrate(250);
        $midBitrate = (new X264)->setKiloBitrate(500);
        $highBitrate = (new X264)->setKiloBitrate(1000);

        // Input disk: The uploaded file is currently in 'public' or S3.
        // For simplicity, we assume we read from public (where UploadController puts it)
        // or ensure LectureController updated video_path correctly.
        $inputDisk = config('filesystems.default') === 's3' ? 's3' : 'public';

        // Output disk: Always 'lectures' (private)
        $outputDisk = 'lectures';

        // HLS Path
        $hlsPath = 'lectures/' . $this->lecture->section->course_id . '/' . $this->lecture->id . '/playlist.m3u8';

        try {
            FFMpeg::fromDisk($inputDisk)
                ->open($this->lecture->video_path)
                ->exportForHLS()
                ->toDisk($outputDisk) // Use private disk for Output
                ->addFormat($lowBitrate)
                ->addFormat($midBitrate)
                ->addFormat($highBitrate)
                ->save($hlsPath);

            $this->lecture->update([
                'hls_path' => $hlsPath,
                'processing_state' => 'completed',
            ]);
        } catch (\Exception $e) {
            Log::error('Video processing failed: ' . $e->getMessage());
            $this->lecture->update(['processing_state' => 'failed']);
            throw $e;
        }
    }
}
