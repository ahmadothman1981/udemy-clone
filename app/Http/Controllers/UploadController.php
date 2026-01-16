<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Pion\Laravel\ChunkUpload\Exceptions\UploadMissingFileException;
use Pion\Laravel\ChunkUpload\Handler\HandlerFactory;
use Pion\Laravel\ChunkUpload\Receiver\FileReceiver;

class UploadController extends Controller
{
    /**
     * Handles the file upload. 
     * Uses Resumable.js on frontend, so we expect chunks.
     */
    public function upload(Request $request)
    {
        // 1. Create the receiver
        $receiver = new FileReceiver('file', $request, HandlerFactory::classFromRequest($request));

        // 2. Check if the upload is success
        if ($receiver->isUploaded() === false) {
            throw new UploadMissingFileException();
        }

        // 3. Receive the file
        $save = $receiver->receive();

        // 4. Check if the upload has finished (in chunk mode it will be true only on the last chunk)
        if ($save->isFinished()) {
            return $this->saveFile($save->getFile());
        }

        // 5. Current chunk saved successfully
        /** @var \Pion\Laravel\ChunkUpload\Handler\AbstractHandler $handler */
        $handler = $save->handler();

        return response()->json([
            "done" => $handler->getPercentageDone(),
            'status' => true
        ]);
    }

    /**
     * Saves the file to the temporary storage path where the text editor expects it.
     */
    protected function saveFile(UploadedFile $file)
    {
        // We'll store it in 'public' disk so frontend can access it if needed, or better yet
        // keep it private until attached to a lecture. 
        // For existing logic compat, let's store in a 'tmp' folder in the configured video disk.

        $fileName = $this->createFilename($file);

        // Disk should match what LectureController uses or be accessible. 
        // Use 'local' (private) to prevent direct public access to temp files.
        $disk = Storage::disk('local');

        // Validate Extension & Mime Type
        $allowedExtensions = ['mp4', 'mov', 'avi', 'wmv', 'flv', 'mkv', 'webm'];
        $allowedMimeTypes = [
            'video/mp4', 
            'video/quicktime', 
            'video/x-msvideo', 
            'video/x-ms-wmv', 
            'video/x-flv', 
            'video/x-matroska', 
            'video/webm'
        ];

        $extension = strtolower($file->getClientOriginalExtension());
        $mimeType = $file->getMimeType();

        if (!in_array($extension, $allowedExtensions) || !in_array($mimeType, $allowedMimeTypes)) {
            abort(422, 'Invalid video format');
        }

        // Save the file
        $path = $disk->putFileAs('tmp', $file, $fileName);

        // Delete the chunk file from the chunks folder (pion stores it in storage/app/chunks by default)
        // Ensure we don't return the path if we failed to save (although putFileAs usually throws or returns false)
        if ($path) {
            @unlink($file->getPathname());
        }

        return response()->json([
            'path' => $path, // Relative path for storage, e.g. "tmp/xyz.mp4"
            'name' => $fileName,
            'mime_type' => $mimeType,
        ]);
    }

    protected function createFilename(UploadedFile $file)
    {
        $extension = $file->getClientOriginalExtension();
        // Use UUID for secure filename
        return Str::uuid() . '.' . $extension;
    }
}
