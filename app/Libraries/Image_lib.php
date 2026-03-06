<?php

namespace App\Libraries;

use lsolesen\pel\PelIfd;
use lsolesen\pel\PelJpeg;
use lsolesen\pel\PelTag;

class Image_lib
{
    private array $exif_to_pel_tags = [
        'Make' => PelTag::MAKE,
        'Model' => PelTag::MODEL,
        'Orientation' => PelTag::ORIENTATION,
        'Copyright' => PelTag::COPYRIGHT,
        'Software' => PelTag::SOFTWARE,
        'DateTime' => PelTag::DATE_TIME,
        'GPS' => PelTag::GPS_OFFSET,
    ];

    public function stripEXIF(string $filepath, array $fields_to_keep = []): bool
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
            return $this->stripExifJpeg($filepath, $fields_to_keep);
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

    private function stripExifJpeg(string $filepath, array $fields_to_keep = []): bool
    {
        try {
            $data = file_get_contents($filepath);
            if ($data === false) {
                return false;
            }

            $jpeg = new PelJpeg($data);

            $exif = $jpeg->getExif();
            if ($exif === null) {
                return true;
            }

            $tiff = $exif->getTiff();
            if ($tiff === null) {
                return true;
            }

            $ifd0 = $tiff->getIfd();
            if ($ifd0 !== null) {
                $this->removeExifFields($ifd0, $fields_to_keep);

                $subIfd = $ifd0->getSubIfd(PelTag::EXIF_IFD_POINTER);
                if ($subIfd !== null) {
                    $this->removeExifFields($subIfd, $fields_to_keep);
                }
            }

            $gpsIfd = $tiff->getIfd(PelTag::GPS_IFD_POINTER);
            if ($gpsIfd !== null && !in_array('GPS', $fields_to_keep)) {
                $tiff->setIfd(null, PelTag::GPS_IFD_POINTER);
            }

            $jpeg->saveFile($filepath);
            return true;
        } catch (\Exception $e) {
            return $this->stripExifFallback($filepath);
        }
    }

    private function removeExifFields(PelIfd $ifd, array $fields_to_keep): void
    {
        $tags_to_remove = array_diff(array_keys($this->exif_to_pel_tags), $fields_to_keep);

        foreach ($tags_to_remove as $field_name) {
            $pel_tag = $this->exif_to_pel_tags[$field_name];
            $entry = $ifd->getEntry($pel_tag);

            if ($entry !== null) {
                $ifd->removeEntry($pel_tag);
            }
        }
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