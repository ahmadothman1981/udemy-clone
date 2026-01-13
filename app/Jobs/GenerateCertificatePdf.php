<?php

namespace App\Jobs;

use App\Models\Certificate;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class GenerateCertificatePdf implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $certificate;

    /**
     * Create a new job instance.
     */
    public function __construct(Certificate $certificate)
    {
        $this->certificate = $certificate;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Eager load relationships needed for the view
        $this->certificate->load(['user', 'course']);

        $pdf = Pdf::loadView('certificates.template', [
            'certificate' => $this->certificate,
            'user' => $this->certificate->user,
            'course' => $this->certificate->course,
        ]);

        $content = $pdf->output();

        // Create a unique path
        $filename = 'certificates/' . $this->certificate->certificate_number . '.pdf';

        // Save to default disk (usually 'local' or 's3' or 'public')
        // Let's use 'local' private storage for security, or 'public' if we want easy access.
        // Given typically certificates are private or signed-url accessed, 'local' (storage/app) is good.
        // We'll use the default disk or explicitly 'local'.
        // Let's use 'local' to store in storage/app/certificates
        Storage::put($filename, $content);

        // Update certificate with path
        $this->certificate->update([
            'pdf_path' => $filename
        ]);
    }
}
