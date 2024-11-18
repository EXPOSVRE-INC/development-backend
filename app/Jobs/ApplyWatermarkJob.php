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
use Illuminate\Support\Facades\Storage;

class ApplyWatermarkJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $mediaId;
    protected $userName;

    public function __construct($mediaId, $userName)
    {
        $this->mediaId = $mediaId;
        $this->userName = $userName ?? 'EXPOSVRE';
    }

    public function handle()
    {
        // Fetch media by UUID
        $media = Media::where('uuid', $this->mediaId)->first();
        if (!$media) {
            Log::error('Media not found for UUID: ' . $this->mediaId);
            return;
        }

        // Get file path from S3 (assuming file is stored on 's3' disk)
        $disk = Storage::disk('s3');
        $inputVideoPath = $media->getPath();  // Get path from Media model, assuming it returns S3 path
        $inputVideo = $disk->url($inputVideoPath);  // Get the full URL to the file on S3
        $watermarkPath = public_path('exp.png'); // Local watermark image

        // Temporary output path for watermarked video on local storage before uploading back to S3
        $tempOutput = storage_path('app/public/' . $media->id . '/watermarked_' . basename($inputVideoPath));

        // FFmpeg executable path
        $ffmpegPath = '/usr/bin/ffmpeg';

        // FFmpeg command to apply watermark and text overlay
        $command = "$ffmpegPath -i \"$inputVideo\" -i \"$watermarkPath\" -filter_complex "
            . "\"[1:v]scale=160:35[wm]; [0:v][wm]overlay=x=10:y=H-h-50[base]; "
            . "[base]drawtext=text='$this->userName':x=13:y=H-text_h-30:fontsize=H*0.05:fontcolor=#e83e8c\" "
            . "-c:a copy \"$tempOutput\"";

        $process = Process::fromShellCommandline($command);
        $process->setTimeout(null);

        try {
            // Run the process
            $process->run();
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            if (file_exists($tempOutput)) {
                // Overwrite the existing file on S3 with the watermarked video
                $s3FolderPath = 'post-uploads/' . $media->id;
                $watermarkedFileName = basename($inputVideoPath);

                // Upload the watermarked video to the same location on S3 (this will overwrite the existing file)
                $disk->put($s3FolderPath . $watermarkedFileName, fopen($tempOutput, 'r+'), 'public');

                // Optionally, delete the local temporary file
                unlink($tempOutput);

                // Update the media record with the new watermarked file URL


                Log::info('Successfully applied watermark and replaced the existing file for media ID: ' . $this->mediaId);
            } else {
                Log::error('Failed to apply watermark for media ID: ' . $this->mediaId);
            }
        } catch (ProcessFailedException $e) {
            Log::error('FFmpeg process failed for media ' . $this->mediaId . ': ' . $e->getMessage());
            if (file_exists($tempOutput)) unlink($tempOutput);
        } catch (\Exception $e) {
            Log::error('Exception during watermark processing: ' . $e->getMessage());
            if (file_exists($tempOutput)) unlink($tempOutput);
        }
    }
}
