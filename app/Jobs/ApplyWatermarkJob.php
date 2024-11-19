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
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Aws\S3\S3Client;
use Aws\Exception\AwsException;

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
        // AWS S3 client setup
        $s3Client = new S3Client([
            'version' => 'latest',
            'region'  => env('AWS_DEFAULT_REGION'),
            'credentials' => [
                'key'    => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
            ],
        ]);

        // Fetch media by UUID
        $media = Media::where('uuid', $this->mediaId)->first();
        if (!$media) {
            Log::error('Media not found for UUID: ' . $this->mediaId);
            return;
        }

        $s3FilePath = $media->getPath();
        $cleanFilePath = Str::replaceFirst("post-uploads/", "", $s3FilePath);

        // Create a temporary directory for processing
        $tempDir = storage_path('app\\temp\\' . Str::random(16));
        $localInputPath = $tempDir . DIRECTORY_SEPARATOR . basename($s3FilePath);

        // Ensure the directory exists
        if (!File::exists($tempDir)) {
            File::makeDirectory($tempDir, 0755, true); // 0755 permissions, recursive creation
        }

        // Attempt to download the file from S3
        try {
            // Check if the file exists in S3
            $result = $s3Client->headObject([
                'Bucket' => env('AWS_BUCKET'),
                'Key'    => $s3FilePath,
            ]);

            // File exists, download it
            $fileContents = $s3Client->getObject([
                'Bucket' => env('AWS_BUCKET'),
                'Key'    => $s3FilePath,
            ])['Body']->getContents();

            // Save the file to local storage
            File::put($localInputPath, $fileContents);
            echo "File downloaded successfully to: " . $localInputPath;
        } catch (AwsException $e) {
            Log::error('Error downloading from S3: ' . $e->getMessage());
            throw new \Exception("Error downloading source video file: {$s3FilePath}");
        }

        // Ensure watermark and thumbnail exists
        $watermarkPath = public_path('exp.png'); // Replace with your actual watermark path
        $pathInfo = pathinfo($s3FilePath);
        $newS3FilePath = $pathInfo['dirname'] . '/watermarked_' . $pathInfo['filename'] . '.' . $pathInfo['extension'];
        // Set output path
        $localOutputPath = $tempDir . '/watermarked_' . basename($s3FilePath);

        // FFmpeg executable path (update this path if necessary)
        $ffmpegPath = '/usr/bin/ffmpeg';

        // FFmpeg command to apply watermark and text overlay
            $command = "$ffmpegPath -i \"$localInputPath\" -i \"$watermarkPath\" -filter_complex "
            . "\"[1:v]scale=160:35[wm]; [0:v][wm]overlay=x=10:y=H-h-50[base]; "
            . "[base]drawtext=text='$this->userName':x=13:y=H-text_h-30:fontsize=H*0.05:fontcolor=#e83e8c\" "
            . "-c:a copy \"$localOutputPath\"";

        // Run the FFmpeg command
        $process = Process::fromShellCommandline($command);
        $process->setTimeout(null);
        $process->run();

        // Check for errors
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        // Ensure output file exists
        if (!file_exists($localOutputPath)) {
            throw new \Exception("Failed to create watermarked video");
        }

        // Upload back to S3, overwriting original
        try {
            $s3Client->putObject([
                'Bucket' => env('AWS_BUCKET'),
                'Key'    => $newS3FilePath, // Overwrite original file
                'SourceFile' => $localOutputPath, // Path to the updated file
                'ACL' => 'public-read', // Set file permissions
            ]);
            Log::info("Successfully applied watermark for media ID: {$this->mediaId}");
        } catch (AwsException $e) {
            Log::error("Failed to upload watermarked video to S3: " . $e->getMessage());
            throw new \Exception("Failed to upload watermarked video to S3.");
        }

        // Clean up temporary files
        $this->cleanup($tempDir);
    }

    protected function cleanup($tempDir)
    {
        if (file_exists($tempDir)) {
            $files = glob($tempDir . '/*');
            foreach ($files as $file) {
                unlink($file);
            }
            rmdir($tempDir);
        }
    }
}
