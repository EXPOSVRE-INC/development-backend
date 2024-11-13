<?php

namespace App\Helpers;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Log;


class MediaHelper
{
    /**
     * Mute audio in a media file using FFmpeg.
     *
     * @param  string  $inputPath  The path to the original media file.
     * @param  string  $outputPath The path to save the muted version of the file.
     * @return bool  Returns true if the file was successfully processed, false otherwise.
     */
    public static function muteMediaAudio($inputPath, $outputPath)
    {
        $ffmpegCommand = [
            '/usr/bin/ffmpeg',
            '-i',
            $inputPath,
            '-an', // Remove audio
            '-c:v',
            'copy', // Copy video stream without re-encoding
            $outputPath
        ];

        $process = new Process($ffmpegCommand);
        $process->setTimeout(null);

        try {
            $process->mustRun();

            return self::replaceOriginalFile($inputPath, $outputPath);
        } catch (ProcessFailedException $e) {
            Log::error("FFmpeg failed for media at {$inputPath}: {$e->getMessage()}");
            self::cleanupFile($outputPath);
            return false;
        }
    }

    /**
     * Replace the original file with the processed file.
     *
     * @param  string  $originalPath  The original media file path.
     * @param  string  $newPath      The path to the new muted media file.
     * @return bool  Returns true if the replacement was successful, false otherwise.
     */
    private static function replaceOriginalFile($originalPath, $newPath)
    {
        self::cleanupFile($originalPath);
        return rename($newPath, $originalPath);
    }

    /**
     * Delete a file if it exists.
     *
     * @param  string  $path  The file path to delete.
     * @return void
     */
    private static function cleanupFile($path)
    {
        if (file_exists($path)) {
            unlink($path);
        }
    }


    public static function applyWatermark($media)
    {
        $watermarkPath = public_path('exp.png');
        return self::applyVideoWatermark($media, $watermarkPath);
    }

    public static function applyVideoWatermark($media, $watermarkPath)
    {
        if (!$media) {
            Log::error('Media not found for UUID: ' . $media->id);
            return;
        }

        $inputVideo = str_replace('\\', '/', $media->getPath());
        $user = auth('api')->user();
        $userName = $user->username;

        // Create a temporary output path for the watermarked video
        $tempOutput = storage_path('app/public/' . $media->id . '/watermarked_' . basename($inputVideo));

        $ffmpegPath = '"/usr/bin/ffmpeg"'; // Path to FFmpeg

        // Command to apply watermark and username text
        $command = "$ffmpegPath -i \"$inputVideo\" -i \"$watermarkPath\" -filter_complex "
            . "\"[1:v]scale=160:35[wm]; " // Resize watermark
            . "[0:v][wm]overlay=x=10:y=(H-h)/2[base]; "
            . "[base]drawtext=text='$userName':x=10:y=(H-text_h)/2+40:fontsize=H*0.04:fontcolor=magenta\" " // Add username below watermark
            . "-c:a copy \"$tempOutput\""; // Copy the audio stream without re-encoding

        // Run the FFmpeg command
        $process = Process::fromShellCommandline($command);
        $process->setTimeout(null); // No timeout

        try {
            $process->run();

            // Check if FFmpeg was successful
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            // Retry logic for accessing the source file if it is in use
            $maxRetries = 3; // Max number of retries
            $retryCount = 0;
            $fileLocked = true;
            while ($fileLocked && $retryCount < $maxRetries) {
                $fileLocked = !self::isFileAvailable($inputVideo); // Check if file is locked
                if ($fileLocked) {
                    // Wait with a backoff (exponential or fixed delay)
                    $delay = pow(2, $retryCount); // Exponential backoff (2, 4, 8 seconds)
                    sleep($delay);
                    $retryCount++;
                }
            }

            // If file is still locked after retries, log error and return
            if ($fileLocked) {
                Log::error('File is still locked after multiple attempts: ' . $inputVideo);
                return;
            }

            // Once the file is available, proceed with moving the temp file to the original location
            if (file_exists($tempOutput)) {
                // First, copy the temp file to the desired location
                if (copy($tempOutput, $inputVideo)) {
                    Log::info('Successfully applied watermark and text overlay for media ID: ' . $media->id);
                    // Clean up: Remove the temporary file after moving
                    unlink($tempOutput);
                } else {
                    Log::error('Failed to copy temp watermarked file to original path for media: ' . $media->id);
                }
            }

        } catch (ProcessFailedException $e) {
            Log::error('FFmpeg process failed for media ' . $media->id . ': ' . $e->getMessage());

            // Cleanup: Delete the temporary output file if it exists
            if (file_exists($tempOutput)) {
                unlink($tempOutput);
            }

        } catch (\Exception $e) {
            Log::error('Exception during watermark processing: ' . $e->getMessage());

            // Cleanup: Delete the temporary output file if it exists
            if (file_exists($tempOutput)) {
                unlink($tempOutput);
            }
        }
    }

    public static function isFileAvailable($filePath)
    {
        // Try to open the file in read-write mode to check if it's available
        $file = @fopen($filePath, 'r+');
        if ($file) {
            fclose($file);
            return true;
        }
        return false;
    }

}
