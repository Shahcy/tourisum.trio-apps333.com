<?php

namespace App\Support\Branding;

use App\Models\Tenant;

class BrandCss
{
    /**
     * Filament v3 يستخدم متغيرات CSS بشكل:
     *   rgb(var(--primary-600) / <alpha-value>)
     * لذلك لازم تكون القيم "R G B" (مثال: "59 130 246") وليس HEX.
     */
    public static function forTenant(?Tenant $tenant): string
    {
        if (! $tenant?->primary_color) {
            return '';
        }

        $base = $tenant->primary_color;

        $palette = self::generateRgbPalette($base);

        $css = ":root,\n.fi-theme {\n";
        foreach ($palette as $shade => $rgbTriplet) {
            $css .= "  --primary-{$shade}: {$rgbTriplet};\n";
        }
        $css .= "}\n";

        return "<style>{$css}</style>";
    }

    /**
     * Generate a 50..950 palette as RGB triplets.
     */
    private static function generateRgbPalette(string $hex): array
    {
        [$r, $g, $b] = self::hexToRgb($hex);

        // 50..950 (تقريب منطقي مثل Tailwind: تفتيح للأرقام الصغيرة وتغميق للكبير)
        $targets = [
            50  => 0.92,
            100 => 0.84,
            200 => 0.72,
            300 => 0.58,
            400 => 0.40,
            500 => 0.22,
            600 => 0.00,  // base
            700 => -0.12,
            800 => -0.24,
            900 => -0.36,
            950 => -0.48,
        ];

        $out = [];
        foreach ($targets as $shade => $delta) {
            $out[$shade] = self::adjustRgb($r, $g, $b, $delta);
        }

        return $out;
    }

    /**
     * delta > 0 => lighten towards white
     * delta < 0 => darken towards black
     */
    private static function adjustRgb(int $r, int $g, int $b, float $delta): string
    {
        if ($delta > 0) {
            $rr = (int) round($r + (255 - $r) * $delta);
            $gg = (int) round($g + (255 - $g) * $delta);
            $bb = (int) round($b + (255 - $b) * $delta);
        } else {
            $k = 1 + $delta; // delta negative => reduce
            $rr = (int) round($r * $k);
            $gg = (int) round($g * $k);
            $bb = (int) round($b * $k);
        }

        $rr = max(0, min(255, $rr));
        $gg = max(0, min(255, $gg));
        $bb = max(0, min(255, $bb));

        return "{$rr} {$gg} {$bb}";
    }

    private static function hexToRgb(string $hex): array
    {
        $hex = ltrim(trim($hex), '#');

        if (strlen($hex) === 3) {
            $hex = "{$hex[0]}{$hex[0]}{$hex[1]}{$hex[1]}{$hex[2]}{$hex[2]}";
        }

        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];
    }
}
