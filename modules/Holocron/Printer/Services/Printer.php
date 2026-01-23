<?php

declare(strict_types=1);

namespace Modules\Holocron\Printer\Services;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Modules\Holocron\Printer\Model\PrintQueue;

class Printer
{
    private const int SCRIPT_TIMEOUT = 10;

    /**
     * Print content using a Laravel view template
     *
     * @param  string  $template  Laravel view template name
     * @param  array  $data  Data to pass to the view
     * @param  array  $actions  QR code actions or other metadata
     * @param  Model|null  $printable  Optional model to associate with this print queue entry
     *
     * @throws Exception
     */
    public static function print(string $template, array $data = [], array $actions = [], ?Model $printable = null): PrintQueue
    {
        try {
            if (! self::isAvailable()) {
                throw new Exception('Node service not available.');
            }

            $html = view($template, $data)->render();

            $imagePath = self::generateImage($html);

            $attributes = [
                'image' => $imagePath,
                'actions' => $actions,
            ];

            if ($printable !== null) {
                $attributes['printable_type'] = $printable->getMorphClass();
                $attributes['printable_id'] = $printable->getKey();
            }

            return PrintQueue::create($attributes);
        } catch (Exception $e) {
            Log::error('Printer service error', [
                'template' => $template,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Check if the printer service is available (Node.js and dependencies)
     */
    public static function isAvailable(): bool
    {
        $nodeCheck = Process::timeout(5)->run('node --version');
        if (! $nodeCheck->successful()) {
            return false;
        }

        return true;
    }

    /**
     * Generate PNG image from HTML using Node.js script
     *
     * @param  string  $html  HTML content to convert
     * @return string Path to generated image file
     *
     * @throws Exception
     */
    private static function generateImage(string $html): string
    {
        $timestamp = now()->format('Y-m-d_H-i-s-u');
        $hash = hash('sha256', $html);
        $filename = "print_{$timestamp}_$hash.png";
        $outputPath = storage_path("app/public/printer/$filename");

        // Ensure directory exists
        $outputDir = dirname($outputPath);
        if (! is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        $scriptPath = base_path('modules/Holocron/Printer/scripts/screenshot.js');

        $result = Process::timeout(self::SCRIPT_TIMEOUT)
            ->input($html)
            ->run("node $scriptPath stdin $outputPath");

        if (! $result->successful()) {
            $errorOutput = $result->errorOutput();
            throw new Exception("Screenshot script failed: $errorOutput");
        }

        if (! file_exists($outputPath)) {
            throw new Exception('Image file was not created');
        }

        return "printer/$filename";
    }
}
