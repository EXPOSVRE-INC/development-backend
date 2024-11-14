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

}
