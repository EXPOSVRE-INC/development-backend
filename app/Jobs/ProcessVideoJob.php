<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\MediaLibrary\MediaCollections\Models\Media;


class ProcessVideoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $mediaId;
    protected $audioFile;

    /**
     * Create a new job instance.
     *
     * @param string $mediaId
     * @param string $audioFile
     */
    public function __construct($mediaId, $audioFile)
    {
        $this->mediaId = $mediaId;
        $this->audioFile = $audioFile;
    }

    /**
     * Execute the job.
     */
    public function handle()
{
    $media = Media::where('uuid', $this->mediaId)->first();

    if (!$media) {
        Log::error('Media not found for UUID: ' . $this->mediaId);
        return;
    }

    // Correct path format for input file
    $inputVideo = str_replace('\\', '/', $media->getPath()); // Ensure the path uses forward slashes
    $newAudio = $this->audioFile;

    // Create a temporary output path under storage/app/temp
    $tempOutput = storage_path('app\temp_' . basename($inputVideo));
    Log::info($tempOutput);

    // FFmpeg command as an array
    // $ffmpegCommand = [
    //     'start', // This tells Windows to start a separate process
    //     '/B', // Run without opening a new window
    //     'C:\\Program Files\\ffmpeg\\bin\\ffmpeg.exe',
    //     '-i', $inputVideo,
    //     '-i', $newAudio,
    //     '-filter_complex', '[1:a]apad',
    //     '-c:v', 'copy',
    //     '-c:a', 'aac',
    //     '-shortest',
    //     $tempOutput
    // ];

    // $process = new Process($ffmpegCommand);
    // Log::info([$process]);

    // // $process->run();

    // $process->setTimeout(null); // Remove timeout limit for process

    // try {
    //     Log::info("heyyyyyyyyyyy");

    //     // Run the process with real-time output
    //     $process->run(function ($type, $buffer) {
    //         if (Process::ERR === $type) {
    //             Log::error('FFmpeg Error: ' . $buffer);
    //         } else {
    //             Log::info('FFmpeg Output: ' . $buffer);
    //         }
    //     });

    //     if (!$process->isSuccessful()) {
    //         throw new ProcessFailedException($process);
    //     }

    //     // Verify the temporary file exists and is valid
    //     if (!file_exists($tempOutput)) {
    //         throw new \Exception('Failed to create processed video');
    //     }

    //     // Remove the original file, if it exists, before replacing it
    //     if (file_exists($inputVideo)) {
    //         // unlink($inputVideo); // Delete original file
    //     }

    //     // Move the temp file to the original location
    //     rename($tempOutput, $inputVideo); // Move temp file to original location
    //     Log::info('Successfully processed video: ' . $this->mediaId);

    // } catch (ProcessFailedException $e) {
    //     Log::error('FFmpeg process failed for media ' . $this->mediaId . ': ' . $e->getMessage());
    //     // Clean up temporary file if it exists
    //     if (file_exists($tempOutput)) {
    //         unlink($tempOutput);
    //     }
    // } catch (\Exception $e) {
    //     Log::error('Exception during video processing: ' . $e->getMessage());
    //     // Clean up temporary file if it exists
    //     if (file_exists($tempOutput)) {
    //         unlink($tempOutput);
    //     }
    // }
}

}
