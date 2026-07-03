<?php

namespace App\Jobs;

use App\Models\Video;
use App\Traits\HasTenantStorage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessVideoToHLS implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, HasTenantStorage;

    public $tries = 3;
    public $timeout = 3600;

    public function __construct(public Video $video)
    {
    }

    public function handle()
    {
        try {
            // Update status to processing
            $this->video->update(['status' => 'processing']);

            Log::info('Starting HLS conversion for video', ['video_id' => $this->video->id]);

            // Get input path (from disk storage)
            $inputPath = Storage::disk('local')->path($this->video->original_path);

            if (!file_exists($inputPath)) {
                Log::error('Video file not found', ['video_id' => $this->video->id, 'path' => $inputPath]);
                $this->video->update(['status' => 'failed', 'error_message' => 'Video file not found at ' . $inputPath]);
                return;
            }

            // Get tenant_id from course chain
            $tenantId = $this->video->lesson->section->course->tenant_id;

            // Create output directory for HLS segments on public disk
            $hlsDir = self::tenantPath('videos/hls/' . $this->video->id, $tenantId);
            $outputDir = Storage::disk('public')->path($hlsDir);
            if (!file_exists($outputDir)) {
                mkdir($outputDir, 0755, true);
            }

            // FFmpeg command to convert to HLS
            // This creates a master.m3u8 file and segment-*.ts files
            $ffmpeg = config('app.ffmpeg_path', '/opt/homebrew/bin/ffmpeg');
            $command = sprintf(
                '"%s" -i "%s" ' .
                '-c:v libx264 -crf 23 -preset fast ' .
                '-c:a aac -b:a 128k ' .
                '-f hls ' .
                '-hls_time 10 ' .
                '-hls_list_size 0 ' .
                '-hls_segment_filename "%s/segment-%%d.ts" ' .
                '"%s/master.m3u8"',
                $ffmpeg,
                $inputPath,
                $outputDir,
                $outputDir
            );

            Log::info('Running FFmpeg command', ['video_id' => $this->video->id]);

            // Execute conversion (using Laravel Process helper)
            $result = Process::timeout(3600)->run($command);

            if ($result->failed()) {
                Log::error('FFmpeg conversion failed', [
                    'video_id' => $this->video->id,
                    'error' => $result->errorOutput(),
                    'output' => $result->output()
                ]);
                $this->video->update(['status' => 'failed', 'error_message' => $result->errorOutput()]);
                return;
            }

            // Verify the master.m3u8 was created
            if (!file_exists($outputDir . '/master.m3u8')) {
                Log::error('Master playlist not created', ['video_id' => $this->video->id, 'dir' => $outputDir]);
                $this->video->update(['status' => 'failed', 'error_message' => 'Master playlist not created']);
                return;
            }

            // Get video duration using ffprobe
            $ffprobe = config('app.ffprobe_path', '/opt/homebrew/bin/ffprobe');
            $durationCommand = sprintf('"%s" -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1:noprint_filename=1 "%s"', $ffprobe, $inputPath);
            $durationResult = Process::timeout(60)->run($durationCommand);
            $duration = (int)$durationResult->output();

            // Update video model with HLS directory path (without filename — getHlsUrlAttribute appends /master.m3u8)
            $hlsPath = self::tenantPath('videos/hls/' . $this->video->id, $tenantId);

            $this->video->update([
                'status' => 'ready',
                'hls_path' => $hlsPath,
                'duration_seconds' => $duration > 0 ? $duration : null,
            ]);

            Log::info('Video successfully converted to HLS', [
                'video_id' => $this->video->id,
                'hls_path' => $hlsPath,
                'duration_seconds' => $duration
            ]);

        } catch (\Exception $e) {
            Log::error('Video processing exception', [
                'video_id' => $this->video->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->video->update(['status' => 'failed']);
        }
    }

    /**
     * Called if job fails after max retries
     */
    public function failed(\Exception $exception)
    {
        Log::error('Video processing job failed permanently', [
            'video_id' => $this->video->id,
            'exception' => $exception->getMessage()
        ]);

        $this->video->update(['status' => 'failed']);
    }
}
