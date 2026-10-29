<?php

namespace Database\Seeders;

/**
 * Generates placeholder packaging illustrations (SVG) in the Grihosthali brand style:
 * cream background, deep-green label band, gold accents, burlap-topped jars.
 * They are stand-ins until real product photos are uploaded from the admin panel.
 */
class ProductArt
{
    private const GREEN = '#1f4d36';
    private const GOLD = '#c9a24a';
    private const CREAM = '#f6efdc';
    private const KRAFT = '#c9a46b';
    private const BN_FONT = "'Noto Serif Bengali','Nirmala UI','Vrinda','Hind Siliguri',serif";
    private const EN_FONT = "'Poppins','Segoe UI',Arial,sans-serif";

    public static function product(string $type, string $label, string $color, string $sub = ''): string
    {
        $art = self::shape($type, $label, $color, $sub);

        return self::wrap(600, 600, self::background(600, 600) . $art);
    }

    public static function banner(string $type, string $color, string $bg1, string $bg2, string $label, string $sub = ''): string
    {
        $art = self::shape($type, $label, $color, $sub);
        $leaves = self::leaf(1150, 70, 1.6, 0.18) . self::leaf(700, 420, 1.2, 0.12) . self::leaf(80, 430, 1.4, 0.1);

        return self::wrap(1280, 480,
            "<defs><linearGradient id='bg' x1='0' y1='0' x2='1' y2='1'><stop offset='0' stop-color='{$bg1}'/><stop offset='1' stop-color='{$bg2}'/></linearGradient>"
            . "<radialGradient id='glow' cx='.5' cy='.5' r='.5'><stop offset='0' stop-color='#fff' stop-opacity='.35'/><stop offset='1' stop-color='#fff' stop-opacity='0'/></radialGradient></defs>"
            . "<rect width='1280' height='480' fill='url(#bg)'/>"
            . "<circle cx='960' cy='250' r='300' fill='url(#glow)'/>"
            . "<circle cx='960' cy='250' r='210' fill='none' stroke='" . self::GOLD . "' stroke-opacity='.35' stroke-width='2' stroke-dasharray='4 8'/>"
            . $leaves
            . "<g transform='translate(690,-20) scale(.86)'>{$art}</g>"
            . "<g transform='translate(1070,140) scale(.42)'>" . self::shape('jar', '', '#e8a317') . '</g>'
        );
    }

    public static function brand(string $name, string $tag): string
    {
        $name = e($name);
        $tag = e($tag);

        return self::wrap(320, 140,
            "<rect width='320' height='140' fill='#fff'/>"
            . "<g transform='translate(160,34)'>" . self::sprig(0, 0, .9) . '</g>'
            . "<text x='160' y='88' text-anchor='middle' font-family=\"" . self::EN_FONT . "\" font-size='26' font-weight='700' fill='" . self::GREEN . "'>{$name}</text>"
            . "<text x='160' y='114' text-anchor='middle' font-family=\"" . self::EN_FONT . "\" font-size='12' letter-spacing='3' fill='" . self::GOLD . "'>{$tag}</text>"
        );
    }

    public static function placeholder(): string
    {
        return self::wrap(600, 600, self::background(600, 600)
            . "<g transform='translate(300,270)'>" . self::sprig(0, 0, 3) . '</g>'
            . "<text x='300' y='380' text-anchor='middle' font-family=\"" . self::BN_FONT . "\" font-size='44' font-weight='700' fill='" . self::GREEN . "' opacity='.5'>গৃহস্থালী</text>");
    }

    public static function favicon(): string
    {
        return "<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'><rect width='64' height='64' rx='14' fill='" . self::GREEN . "'/>"
            . "<g transform='translate(32,30)'>" . self::sprig(0, 0, 1.6, '#fff', self::GOLD) . '</g></svg>';
    }

    /* ------------------------------------------------------------------ */

    private static function wrap(int $w, int $h, string $inner): string
    {
        return "<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 {$w} {$h}' width='{$w}' height='{$h}'>{$inner}</svg>";
    }

    private static function background(int $w, int $h): string
    {
        return "<defs><radialGradient id='bgr' cx='.5' cy='.45' r='.7'><stop offset='0' stop-color='#fffdf7'/><stop offset='1' stop-color='#f1e8d2'/></radialGradient></defs>"
            . "<rect width='{$w}' height='{$h}' fill='url(#bgr)'/>"
            . self::leaf(70, 80, 1, .15) . self::leaf(540, 520, 1.2, .12) . self::leaf(530, 90, .8, .1);
    }

    private static function leaf(float $x, float $y, float $s, float $op): string
    {
        return "<g transform='translate({$x},{$y}) scale({$s}) rotate(-30)' opacity='{$op}'>"
            . "<path d='M0,-40 C25,-20 25,20 0,40 C-25,20 -25,-20 0,-40Z' fill='" . self::GREEN . "'/>"
            . "<path d='M0,-36 L0,36' stroke='#fff' stroke-width='2'/></g>";
    }

    private static function sprig(float $x, float $y, float $s, string $c = self::GREEN, string $c2 = self::GREEN): string
    {
        return "<g transform='translate({$x},{$y}) scale({$s})'>"
            . "<path d='M0,10 L0,-6' stroke='{$c}' stroke-width='2.4' stroke-linecap='round'/>"
            . "<path d='M0,-4 C-10,-6 -13,-14 -11,-20 C-4,-18 0,-12 0,-4Z' fill='{$c}'/>"
            . "<path d='M0,-4 C10,-6 13,-14 11,-20 C4,-18 0,-12 0,-4Z' fill='{$c}'/>"
            . "<path d='M0,-8 C-4,-14 -3,-20 0,-24 C3,-20 4,-14 0,-8Z' fill='{$c2}'/></g>";
    }

    private static function shade(string $hex, float $f): string
    {
        $hex = ltrim($hex, '#');
        $rgb = array_map('hexdec', str_split($hex, 2));
        $rgb = array_map(fn ($v) => max(0, min(255, (int) round($f < 1 ? $v * $f : $v + (255 - $v) * ($f - 1)))), $rgb);

        return sprintf('#%02x%02x%02x', ...$rgb);
    }

    /** Brand label (cream top with Bangla wordmark, green band with product name). */
    private static function label(float $x, float $y, float $w, float $h, string $text, string $sub): string
    {
        $cx = $x + $w / 2;
        $topH = $h * 0.46;
        $lines = self::lines($text, $w);
        // Fit the longest line inside the label (bold serif capitals are ~0.75em wide).
        $longest = max(array_map('mb_strlen', $lines));
        $fs = min(count($lines) > 1 ? 24 : 28, ($w - 30) / ($longest * 0.75));
        $bandMid = $y + $topH + ($h - $topH) / 2;
        $textY = $bandMid - (count($lines) - 1) * $fs * 0.55 - ($sub ? 8 : 0) + $fs * 0.35;

        $out = "<rect x='{$x}' y='{$y}' width='{$w}' height='{$h}' fill='" . self::CREAM . "'/>"
            . "<rect x='{$x}' y='" . ($y + $topH) . "' width='{$w}' height='" . ($h - $topH) . "' fill='" . self::GREEN . "'/>"
            . "<rect x='{$x}' y='" . ($y + $topH) . "' width='{$w}' height='3' fill='" . self::GOLD . "'/>"
            . "<rect x='{$x}' y='" . ($y + $h - 6) . "' width='{$w}' height='3' fill='" . self::GOLD . "'/>"
            . self::sprig($cx, $y + 20, .75)
            . "<text x='{$cx}' y='" . ($y + $topH * 0.68) . "' text-anchor='middle' font-family=\"" . self::BN_FONT . "\" font-size='" . min(30, $w / 7.5) . "' font-weight='700' fill='" . self::GREEN . "'>গৃহস্থালী</text>"
            . "<text x='{$cx}' y='" . ($y + $topH * 0.9) . "' text-anchor='middle' font-family=\"" . self::EN_FONT . "\" font-size='" . min(10, $w / 24) . "' letter-spacing='2' fill='#3a3a3a'>GRIHOSTHALI</text>";

        foreach ($lines as $i => $line) {
            $out .= "<text x='{$cx}' y='" . ($textY + $i * $fs * 1.1) . "' text-anchor='middle' font-family=\"Georgia,'Times New Roman',serif\" font-size='{$fs}' font-weight='700' fill='" . self::GOLD . "'>" . e($line) . '</text>';
        }
        if ($sub) {
            $out .= "<text x='{$cx}' y='" . ($textY + count($lines) * $fs * 1.1 + 2) . "' text-anchor='middle' font-family=\"" . self::EN_FONT . "\" font-size='" . min(12, $w / 20) . "' letter-spacing='1.5' fill='#e9efe9'>" . e($sub) . '</text>';
        }

        return $out;
    }

    private static function lines(string $text, float $w): array
    {
        $max = (int) floor($w / 15);
        $words = explode(' ', strtoupper($text));
        $lines = [''];
        foreach ($words as $word) {
            $cur = &$lines[count($lines) - 1];
            if ($cur === '' || mb_strlen($cur . ' ' . $word) <= $max) {
                $cur = trim($cur . ' ' . $word);
            } else {
                $lines[] = $word;
            }
            unset($cur);
        }

        return array_slice($lines, 0, 2);
    }

    private static function shape(string $type, string $label, string $color, string $sub = ''): string
    {
        $dark = self::shade($color, .78);
        $shadow = "<ellipse cx='300' cy='528' rx='160' ry='16' fill='#000' opacity='.1'/>";

        switch ($type) {
            case 'bottle':
                return $shadow
                    . "<rect x='268' y='84' width='64' height='38' rx='6' fill='" . self::GREEN . "'/>"
                    . "<rect x='268' y='112' width='64' height='6' fill='" . self::GOLD . "'/>"
                    . "<path d='M276,122 L324,122 L326,170 C326,190 392,200 392,250 L392,500 Q392,522 370,522 L230,522 Q208,522 208,500 L208,250 C208,200 274,190 274,170Z' fill='{$color}' stroke='{$dark}' stroke-width='2'/>"
                    . "<rect x='226' y='230' width='18' height='270' rx='9' fill='#fff' opacity='.28'/>"
                    . ($label !== '' ? self::label(208, 290, 184, 170, $label, $sub) : '');

            case 'pouch':
                return $shadow
                    . "<path d='M168,120 L432,120 L448,512 Q300,534 152,512Z' fill='" . self::KRAFT . "'/>"
                    . "<path d='M168,120 L432,120 L434,152 L166,152Z' fill='" . self::shade(self::KRAFT, .85) . "'/>"
                    . "<path d='M172,134 L428,134' stroke='" . self::shade(self::KRAFT, .7) . "' stroke-width='2' stroke-dasharray='6 4'/>"
                    . "<rect x='238' y='176' width='124' height='112' rx='56' fill='{$color}' stroke='" . self::shade(self::KRAFT, .75) . "' stroke-width='4'/>"
                    . "<g fill='{$dark}' opacity='.55'><circle cx='270' cy='212' r='5'/><circle cx='300' cy='240' r='6'/><circle cx='330' cy='210' r='4'/><circle cx='285' cy='262' r='5'/><circle cx='322' cy='256' r='5'/><circle cx='258' cy='240' r='4'/><circle cx='344' cy='236' r='4'/></g>"
                    . "<rect x='258' y='190' width='12' height='70' rx='6' fill='#fff' opacity='.3'/>"
                    . ($label !== '' ? self::label(160, 310, 280, 170, $label, $sub) : '');

            case 'box':
                return $shadow
                    . "<path d='M150,190 L450,190 L480,160 L180,160Z' fill='" . self::shade(self::KRAFT, 1.12) . "'/>"
                    . "<path d='M450,190 L480,160 L480,490 L450,520Z' fill='" . self::shade(self::KRAFT, .8) . "'/>"
                    . "<rect x='150' y='190' width='300' height='330' fill='" . self::KRAFT . "'/>"
                    . "<path d='M262,160 L262,120 Q300,96 338,120 L338,160' fill='none' stroke='" . self::shade(self::KRAFT, .75) . "' stroke-width='10'/>"
                    . "<circle cx='300' cy='245' r='26' fill='{$color}' stroke='" . self::shade(self::KRAFT, .7) . "' stroke-width='3'/>"
                    . "<circle cx='292' cy='238' r='7' fill='#fff' opacity='.35'/>"
                    . ($label !== '' ? self::label(150, 290, 300, 190, $label, $sub) : '');

            case 'jar':
            default:
                return $shadow
                    // glass jar with contents
                    . "<rect x='176' y='178' width='248' height='346' rx='38' fill='{$color}' stroke='" . self::shade($color, .7) . "' stroke-width='3'/>"
                    . "<rect x='194' y='162' width='212' height='34' rx='8' fill='{$dark}'/>"
                    . "<rect x='196' y='210' width='22' height='290' rx='11' fill='#fff' opacity='.3'/>"
                    // burlap cloth cover and twine
                    . "<path d='M170,186 C150,150 178,104 210,98 C240,80 360,80 390,98 C422,104 450,150 430,186 C420,200 410,176 396,196 C380,176 366,204 350,188 C334,206 318,184 300,198 C282,184 266,206 250,188 C234,204 220,176 204,196 C190,176 180,200 170,186Z' fill='#c7a777'/>"
                    . "<path d='M190,110 L410,110 M184,130 L416,130 M180,150 L420,150 M182,170 L418,170' stroke='#a88a5c' stroke-width='1.5' opacity='.6'/>"
                    . "<path d='M220,96 L216,186 M260,88 L258,196 M300,86 L300,198 M340,88 L342,196 M380,96 L384,186' stroke='#a88a5c' stroke-width='1.5' opacity='.5'/>"
                    . "<rect x='184' y='170' width='232' height='10' rx='5' fill='#8b6b3e'/>"
                    . "<path d='M410,176 C430,190 440,214 432,236' stroke='#8b6b3e' stroke-width='4' fill='none'/>"
                    . "<rect x='414' y='232' width='40' height='58' rx='4' fill='" . self::KRAFT . "' transform='rotate(12 434 260)'/>"
                    . ($label !== '' ? self::label(176, 280, 248, 180, $label, $sub) : '');
        }
    }
}
