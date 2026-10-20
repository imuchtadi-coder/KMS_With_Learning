<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use ZipArchive;

class DocumentTextExtractor
{
    /**
     * Extract plain text from an uploaded file path in storage
     */
    public static function extractText(string $diskPath, string $extension): ?string
    {
        $ext = strtolower(trim($extension, '.'));

        try {
            if (!Storage::exists($diskPath)) {
                return null;
            }

            $fullPath = Storage::path($diskPath);

            switch ($ext) {
                case 'docx':
                    return self::extractFromDocx($fullPath);

                case 'pdf':
                    return self::extractFromPdf($fullPath);

                case 'txt':
                case 'csv':
                case 'md':
                case 'json':
                    return self::extractFromPlainText($fullPath);

                default:
                    return null;
            }
        } catch (\Throwable $e) {
            \Log::warning("Failed to extract text from {$diskPath}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Extract text from Microsoft Word .docx file using native ZipArchive
     */
    private static function extractFromDocx(string $fullPath): ?string
    {
        $zip = new ZipArchive();
        if ($zip->open($fullPath) !== true) {
            return null;
        }

        $xmlIndex = $zip->locateName('word/document.xml');
        if ($xmlIndex === false) {
            $zip->close();
            return null;
        }

        $content = $zip->getFromIndex($xmlIndex);
        $zip->close();

        if (!$content) {
            return null;
        }

        // Replace paragraph and break tags with spaces
        $content = str_replace(['</w:p>', '</w:br>', '<w:tab/>'], ["\n", "\n", "\t"], $content);
        $text = strip_tags($content);
        $text = preg_replace('/\s+/', ' ', $text);

        return mb_substr(trim($text), 0, 100000);
    }

    /**
     * Extract text from PDF file using native stream parsing
     */
    private static function extractFromPdf(string $fullPath): ?string
    {
        $content = @file_get_contents($fullPath);
        if (!$content) {
            return null;
        }

        $text = '';

        // Match all stream objects in PDF
        preg_match_all('/stream[\r\n]+(.*?)[\r\n]+endstream/is', $content, $matches);

        foreach ($matches[1] as $stream) {
            // Uncompress FlateDecode streams if gzuncompress is available
            $data = @gzuncompress($stream);
            if ($data === false) {
                $data = $stream;
            }

            // Extract text from BT ... ET (Begin Text ... End Text) operators
            if (preg_match_all('/BT[\r\n]+(.*?)[\r\n]+ET/is', $data, $textBlocks)) {
                foreach ($textBlocks[1] as $block) {
                    // Match (string) Tj or [(array)] TJ
                    if (preg_match_all('/\((.*?)\)\s*Tj/is', $block, $tjs)) {
                        $text .= ' ' . implode(' ', $tjs[1]);
                    }
                    if (preg_match_all('/\[(.*?)\]\s*TJ/is', $block, $tjsArray)) {
                        foreach ($tjsArray[1] as $tj) {
                            if (preg_match_all('/\((.*?)\)/is', $tj, $parts)) {
                                $text .= ' ' . implode('', $parts[1]);
                            }
                        }
                    }
                }
            }
        }

        $cleanText = preg_replace('/[^a-zA-Z0-9\s\.\,\-\_\/\:\;\(\)]/', ' ', $text);
        $cleanText = preg_replace('/\s+/', ' ', $cleanText);

        return mb_substr(trim($cleanText), 0, 100000);
    }

    /**
     * Extract plain text from TXT/CSV/MD
     */
    private static function extractFromPlainText(string $fullPath): ?string
    {
        $content = @file_get_contents($fullPath);
        if (!$content) {
            return null;
        }

        $text = preg_replace('/\s+/', ' ', $content);
        return mb_substr(trim($text), 0, 100000);
    }
}
