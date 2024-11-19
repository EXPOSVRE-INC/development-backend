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
        $media = Media::where('uuid', $this->mediaId)->first();
        if (!$media) {
            Log::error('Media not found for UUID: ' . $this->mediaId);
            return;
        }

        $inputVideo = str_replace('\\', '/', $media->getPath());
        $watermarkPath = public_path('exp.png');
        $tempOutput = storage_path('app/public/' . $media->id . '/watermarked_' . basename($inputVideo));
        $ffmpegPath = '/usr/bin/ffmpeg';

        $command = "$ffmpegPath -i \"$inputVideo\" -i \"$watermarkPath\" -filter_complex "
            . "\"[1:v]scale=160:35[wm]; [0:v][wm]overlay=x=10:y=H-h-50[base]; "
            . "[base]drawtext=text='$this->userName':x=13:y=H-text_h-30:fontsize=H*0.05:fontcolor=#e83e8c\" "
            . "-c:a copy \"$tempOutput\"";

        $process = Process::fromShellCommandline($command);
        $process->setTimeout(null);

        try {
            $process->run();
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }
            if (file_exists($tempOutput) && rename($tempOutput, $inputVideo)) {
                Log::info('Successfully applied watermark and text overlay for media ID: ' . $this->mediaId);
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
