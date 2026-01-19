<?php

namespace App\Support\Branding;

use Illuminate\Support\Facades\Storage;

class LogoPaletteExtractor
{
    /**
     * يستخرج Palette بسيطة (Primary/Secondary/Accent) من ملف لوجو مخزن على disk public.
     */
    public static function extractFromPublicDiskPath(?string $path): ?array
    {
        // حماية: إذا GD غير مفعّلة لا تحاول أصلاً
        if (! \extension_loaded('gd')) {
            return null;
        }

        // حماية إضافية: إذا دوال GD الأساسية غير متاحة
        if (! \function_exists('imagecreatefromjpeg') || ! \function_exists('imagecreatetruecolor')) {
            return null;
        }

        if (! $path) {
            return null;
        }

        $disk = Storage::disk('public');

        if (! $disk->exists($path)) {
            return null;
        }

        $absolute = $disk->path($path);
        $info = @\getimagesize($absolute);

        if (! $info) {
            return null;
        }

        $mime = $info['mime'] ?? null;

        // مهم: استخدم \ قبل الدوال لتجنب أي namespace issues
        $img = match ($mime) {
            'image/png'  => @\imagecreatefrompng($absolute),
            'image/jpeg' => @\imagecreatefromjpeg($absolute),
            'image/jpg'  => @\imagecreatefromjpeg($absolute),
            'image/gif'  => @\imagecreatefromgif($absolute),
            'image/webp' => \function_exists('imagecreatefromwebp') ? @\imagecreatefromwebp($absolute) : null,
            default      => null,
        };

        if (! $img) {
            return null;
        }

        // صغّر الصورة لتقليل التكلفة
        $w = \imagesx($img);
        $h = \imagesy($img);
        $target = 60;

        $scale = \min($target / \max($w, 1), $target / \max($h, 1));
        $nw = \max(1, (int) \round($w * $scale));
        $nh = \max(1, (int) \round($h * $scale));

        $tmp = \imagecreatetruecolor($nw, $nh);
        \imagealphablending($tmp, false);
        \imagesavealpha($tmp, true);
        \imagecopyresampled($tmp, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);

        // Quantization بسيط
        $buckets = [];

        for ($y = 0; $y < $nh; $y++) {
            for ($x = 0; $x < $nw; $x++) {
                $rgba = \imagecolorat($tmp, $x, $y);

                $a = ($rgba & 0x7F000000) >> 24;
                if ($a > 90) {
                    continue;
                }

                $r = ($rgba >> 16) & 0xFF;
                $g = ($rgba >> 8) & 0xFF;
                $b = $rgba & 0xFF;

                $brightness = ($r * 0.299) + ($g * 0.587) + ($b * 0.114);
                if ($brightness > 245 || $brightness < 10) {
                    continue;
                }

                $rq = $r >> 4;
                $gq = $g >> 4;
                $bq = $b >> 4;
                $key = ($rq << 8) | ($gq << 4) | $bq;

                if (! isset($buckets[$key])) {
                    $buckets[$key] = ['count' => 0, 'r' => 0, 'g' => 0, 'b' => 0];
                }

                $buckets[$key]['count']++;
                $buckets[$key]['r'] += $r;
                $buckets[$key]['g'] += $g;
                $buckets[$key]['b'] += $b;
            }
        }

        \imagedestroy($img);
        \imagedestroy($tmp);

        if (empty($buckets)) {
            return null;
        }

        \usort($buckets, fn($a, $b) => $b['count'] <=> $a['count']);

        $top = \array_slice($buckets, 0, 12);
        $colors = [];

        foreach ($top as $item) {
            $c = \max(1, (int) $item['count']);
            $r = (int) \round($item['r'] / $c);
            $g = (int) \round($item['g'] / $c);
            $b = (int) \round($item['b'] / $c);

            $colors[] = [
                'hex' => self::rgbToHex($r, $g, $b),
            ];
        }

        $primary = $colors[0]['hex'] ?? null;
        $secondary = self::pickFarColor($colors, $primary) ?? ($colors[1]['hex'] ?? $primary);
        $accent = self::pickFarColor($colors, $secondary, [$primary]) ?? ($colors[2]['hex'] ?? $secondary);

        return [
            'primary_color' => $primary,
            'secondary_color' => $secondary,
            'accent_color' => $accent,
        ];
    }

    private static function rgbToHex(int $r, int $g, int $b): string
    {
        return \sprintf('#%02X%02X%02X', \max(0, \min(255, $r)), \max(0, \min(255, $g)), \max(0, \min(255, $b)));
    }

    private static function hexToRgb(?string $hex): ?array
    {
        if (! $hex) {
            return null;
        }

        $hex = \ltrim($hex, '#');

        if (\strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        if (\strlen($hex) !== 6) {
            return null;
        }

        return [
            \hexdec(\substr($hex, 0, 2)),
            \hexdec(\substr($hex, 2, 2)),
            \hexdec(\substr($hex, 4, 2)),
        ];
    }

    private static function dist(?string $a, ?string $b): float
    {
        $ra = self::hexToRgb($a);
        $rb = self::hexToRgb($b);

        if (! $ra || ! $rb) {
            return 0.0;
        }

        return \sqrt((($ra[0] - $rb[0]) ** 2) + (($ra[1] - $rb[1]) ** 2) + (($ra[2] - $rb[2]) ** 2));
    }

    private static function pickFarColor(array $colors, ?string $from, array $avoid = []): ?string
    {
        $best = null;
        $bestScore = -1;

        $avoidUpper = \array_map(fn($x) => \strtoupper((string) $x), $avoid);

        foreach ($colors as $c) {
            $hex = $c['hex'] ?? null;
            if (! $hex) {
                continue;
            }

            if (\in_array(\strtoupper($hex), $avoidUpper, true)) {
                continue;
            }

            $score = self::dist($hex, $from);
            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $hex;
            }
        }

        return $best;
    }
}
