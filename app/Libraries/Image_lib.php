<?php

namespace App\Libraries;

class Image_lib
{
    public function stripEXIF(string $filepath, array $fields_to_remove = []): bool
    {
        if (!file_exists($filepath)) {
            return false;
        }

        $mimetype = mime_content_type($filepath);
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp', 'image/bmp', 'image/tiff'];

        if (!in_array($mimetype, $allowed_types)) {
            return false;
        }

        if ($mimetype === 'image/jpeg' || $mimetype === 'image/jpg') {
            return $this->stripExifJpeg($filepath, $fields_to_remove);
        }

        if ($mimetype === 'image/png') {
            return $this->stripExifPng($filepath);
        }

        if ($mimetype === 'image/gif') {
            return $this->stripExifGif($filepath);
        }

        if ($mimetype === 'image/webp') {
            return $this->stripExifWebp($filepath);
        }

        return true;
    }

    private function stripExifJpeg(string $filepath, array $fields_to_remove = []): bool
    {
        if (!function_exists('exif_read_data')) {
            return $this->stripExifFallback($filepath);
        }

        $image_info = @getimagesize($filepath);
        if ($image_info === false) {
            return false;
        }

        $image = @imagecreatefromjpeg($filepath);
        if ($image === false) {
            return false;
        }

        $result = imagejpeg($image, $filepath, 100);
        imagedestroy($image);

        return $result;
    }

    private function stripExifPng(string $filepath): bool
    {
        $image = @imagecreatefrompng($filepath);
        if ($image === false) {
            return false;
        }

        imagealphablending($image, true);
        imagesavealpha($image, true);

        $result = imagepng($image, $filepath, 9);
        imagedestroy($image);

        return $result;
    }

    private function stripExifGif(string $filepath): bool
    {
        $image = @imagecreatefromgif($filepath);
        if ($image === false) {
            return false;
        }

        $result = imagegif($image, $filepath);
        imagedestroy($image);

        return $result;
    }

    private function stripExifWebp(string $filepath): bool
    {
        if (!function_exists('imagecreatefromwebp')) {
            return false;
        }

        $image = @imagecreatefromwebp($filepath);
        if ($image === false) {
            return false;
        }

        $result = imagewebp($image, $filepath, 100);
        imagedestroy($image);

        return $result;
    }

    private function stripExifFallback(string $filepath): bool
    {
        $content = file_get_contents($filepath);
        if ($content === false) {
            return false;
        }

        $markers = [];
        $offset = 0;
        
        while ($offset < strlen($content)) {
            if ($offset + 4 > strlen($content)) {
                break;
            }
            
            $marker = ord($content[$offset + 1]);
            
            if (ord($content[$offset]) !== 0xFF) {
                break;
            }
            
            if ($marker >= 0xE0 && $marker <= 0xEF) {
                $marker_len = ord($content[$offset + 2]) * 256 + ord($content[$offset + 3]);
                $markers[] = [$offset, $marker_len + 2];
                $offset += $marker_len + 2;
            } elseif ($marker === 0xD8 || $marker === 0xD9) {
                $offset += 2;
            } elseif ($marker === 0x00 || $marker === 0xD0 || $marker === 0xD1 || $marker === 0xD2 || $marker === 0xD3 || $marker === 0xD4 || $marker === 0xD5 || $marker === 0xD6 || $marker === 0xD7) {
                $offset += 2;
            } elseif ($marker === 0x01) {
                $offset += 2;
            } else {
                if ($offset + 4 > strlen($content)) {
                    break;
                }
                $marker_len = ord($content[$offset + 2]) * 256 + ord($content[$offset + 3]);
                $offset += $marker_len + 2;
            }
        }

        if (empty($markers)) {
            return true;
        }

        $new_content = $content;
        foreach (array_reverse($markers) as $marker_info) {
            $new_content = substr_replace($new_content, '', $marker_info[0], $marker_info[1]);
        }

        return file_put_contents($filepath, $new_content) !== false;
    }
}