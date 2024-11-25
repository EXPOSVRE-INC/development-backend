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
        Log::info('handle function called');

        $inputVideo = str_replace('\\', '/', $media->getPath());
        Log::info('input video file' . $inputVideo);
        $newAudio = $this->audioFile;
        Log::info('newAudio--------------' . $newAudio);

        $originalVideoBackup = storage_path('app/public/' . $media->id . '/original_' . basename($inputVideo));
        Log::info('originalVideoBackup' . $originalVideoBackup);

        if (!file_exists($originalVideoBackup)) {
        Log::info('heyyyyyy');

            if (!copy($inputVideo, $originalVideoBackup)) {
                Log::error('Failed to create backup of original video for media: ' . $this->mediaId);
                return;
            }
        }

        $tempOutput = storage_path('app/public/' . $media->id . '/temp_' . basename($inputVideo));

        if ($newAudio) {
            // Add or replace audio
            $ffmpegCommand = [
                '/usr/bin/ffmpeg',
                '-i',
                $inputVideo,
                '-i',
                $newAudio,
                '-filter_complex',
                '[1:a]apad',
                '-c:v',
                'copy',
                '-c:a',
                'aac',
                '-shortest',
                $tempOutput,
            ];
        } else {
            // Restore original video from backup (remove added audio)
            if (file_exists($originalVideoBackup)) {

                  Log::info('originalVideoBackup  yesssssssssssssss' . $originalVideoBackup);

                if (!copy($originalVideoBackup, $inputVideo)) {
                    Log::error('Failed to restore original video for media: ' . $this->mediaId);
                    return;
                }
                Log::info('Successfully restored original video for media: ' . $this->mediaId);
                return $inputVideo;
            } else {
                Log::error('Original video backup not found for media: ' . $this->mediaId);
                return;
            }
        }

        $process = new Process($ffmpegCommand);
        $process->setTimeout(null);

        try {
            $process->run();

            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            if (file_exists($tempOutput)) {
                unlink($inputVideo);

                if (rename($tempOutput, $inputVideo)) {
                    Log::info('Successfully processed video and replaced original file: ' . $this->mediaId);
                    return $inputVideo;
                } else {
                    Log::error('Failed to rename temp file to original path for media: ' . $this->mediaId);
                }
            } else {
                Log::error('Temp output file not created for media: ' . $this->mediaId);
            }
        } catch (ProcessFailedException $e) {
            Log::error('FFmpeg process failed for media ' . $this->mediaId . ': ' . $e->getMessage());
            if (file_exists($tempOutput)) {
                unlink($tempOutput);
            }
        } catch (\Exception $e) {
            Log::error('Exception during video processing: ' . $e->getMessage());
            if (file_exists($tempOutput)) {
                unlink($tempOutput);
            }
        }
    }
}
