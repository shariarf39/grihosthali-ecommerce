<?php

namespace Database\Seeders;

/**
 * Builds web-ready brand files from the client's square logo (white wordmark + yellow leaves on teal):
 *  - logo.png        teal wordmark on transparent (for white backgrounds)
 *  - logo-white.png  white wordmark on transparent (for dark backgrounds)
 *  - favicon.png / icon-192.png  square app icons
 * Usage: BrandAssets::build($sourceJpg, $outputDir)
 */
class BrandAssets
{
    private const BG = [8, 70, 69];       // #084645 logo background
    private const TEAL = [8, 70, 69];      // wordmark colour on light backgrounds
    private const YELLOW = [255, 202, 74]; // #ffca4a leaves & dots

    public static function build(string $source, string $dir): array
    {
        $src = imagecreatefromjpeg($source);
        [$x1, $y1, $x2, $y2] = self::contentBox($src);
        $pad = 10;
        $x1 = max(0, $x1 - $pad); $y1 = max(0, $y1 - $pad);
        $x2 = min(imagesx($src) - 1, $x2 + $pad); $y2 = min(imagesy($src) - 1, $y2 + $pad);

        $files = [
            'logo.png' => self::cutout($src, $x1, $y1, $x2, $y2, self::TEAL),
            'logo-white.png' => self::cutout($src, $x1, $y1, $x2, $y2, [255, 255, 255]),
        ];
        foreach ($files as $name => $img) {
            imagepng($img, "{$dir}/{$name}", 9);
            imagedestroy($img);
        }

        // App icons: the "গৃ" + leaves part of the mark on a rounded teal tile.
        foreach (['favicon.png' => 64, 'icon-192.png' => 192] as $name => $size) {
            imagepng(self::icon($src, $size), "{$dir}/{$name}", 9);
        }
        imagedestroy($src);

        return array_merge(array_keys($files), ['favicon.png', 'icon-192.png']);
    }

    /** Bounding box of everything that is not the teal background. */
    private static function contentBox($im): array
    {
        $minx = PHP_INT_MAX; $miny = PHP_INT_MAX; $maxx = 0; $maxy = 0;
        for ($y = 0; $y < imagesy($im); $y++) {
            for ($x = 0; $x < imagesx($im); $x++) {
                [$r, $g, $b] = self::rgb($im, $x, $y);
                if (abs($r - self::BG[0]) + abs($g - self::BG[1]) + abs($b - self::BG[2]) > 60) {
                    $minx = min($minx, $x); $maxx = max($maxx, $x);
                    $miny = min($miny, $y); $maxy = max($maxy, $y);
                }
            }
        }

        return [$minx, $miny, $maxx, $maxy];
    }

    /**
     * Remove the teal background, keeping anti-aliased edges as alpha.
     * White strokes are recoloured to $ink; yellow strokes stay yellow.
     */
    private static function cutout($src, int $x1, int $y1, int $x2, int $y2, array $ink)
    {
        $w = $x2 - $x1 + 1; $h = $y2 - $y1 + 1;
        $out = imagecreatetruecolor($w, $h);
        imagealphablending($out, false);
        imagesavealpha($out, true);
        imagefill($out, 0, 0, imagecolorallocatealpha($out, 0, 0, 0, 127));

        [$br, $bg, $bb] = self::BG;
        for ($y = 0; $y < $h; $y++) {
            for ($x = 0; $x < $w; $x++) {
                [$r, $g, $b] = self::rgb($src, $x1 + $x, $y1 + $y);
                // Yellow pixels have much more red than blue; white ones are balanced.
                $isYellow = ($r - $b) > 70 && $r > 60;
                if ($isYellow) {
                    $target = self::YELLOW;
                    $alpha = min(1, max(0, ($r - $br) / (self::YELLOW[0] - $br)));
                    $color = $target;
                } else {
                    // Coverage of white over teal, measured on the blue channel (largest teal→white gap).
                    $alpha = min(1, max(0, (($r - $br) / (255 - $br) + ($b - $bb) / (255 - $bb)) / 2));
                    $color = $ink;
                }
                if ($alpha < 0.04) {
                    continue;
                }
                $a = (int) round(127 - $alpha * 127);
                imagesetpixel($out, $x, $y, imagecolorallocatealpha($out, $color[0], $color[1], $color[2], $a));
            }
        }

        return $out;
    }

    private static function icon($src, int $size)
    {
        // Crop around the first letter and its leaves (left part of the wordmark).
        $crop = imagecreatetruecolor(360, 360);
        imagecopy($crop, $src, 0, 0, 105, 350, 360, 360); // stops before the tagline (x ≥ 480)

        $out = imagecreatetruecolor($size, $size);
        imagealphablending($out, false);
        imagesavealpha($out, true);
        imagefill($out, 0, 0, imagecolorallocatealpha($out, 0, 0, 0, 127));
        imagealphablending($out, true);

        // Rounded teal tile
        $teal = imagecolorallocate($out, self::BG[0], self::BG[1], self::BG[2]);
        $rad = (int) round($size * 0.22);
        imagefilledrectangle($out, $rad, 0, $size - $rad - 1, $size - 1, $teal);
        imagefilledrectangle($out, 0, $rad, $size - 1, $size - $rad - 1, $teal);
        foreach ([[$rad, $rad], [$size - $rad - 1, $rad], [$rad, $size - $rad - 1], [$size - $rad - 1, $size - $rad - 1]] as [$cx, $cy]) {
            imagefilledellipse($out, $cx, $cy, $rad * 2, $rad * 2, $teal);
        }
        $inset = (int) round($size * 0.08);
        imagecopyresampled($out, $crop, $inset, $inset, 0, 0, $size - 2 * $inset, $size - 2 * $inset, 360, 360);
        imagedestroy($crop);

        return $out;
    }

    private static function rgb($im, int $x, int $y): array
    {
        $v = imagecolorat($im, $x, $y);

        return [($v >> 16) & 255, ($v >> 8) & 255, $v & 255];
    }
}
