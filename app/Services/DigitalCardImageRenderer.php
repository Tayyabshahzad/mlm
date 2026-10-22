<?php

namespace App\Services;

use App\Models\DigitalCard;
use App\Support\DigitalCardSettings;
use GdImage;

/**
 * Renders a digital card as a PNG (GD + FreeType), styled after the reference
 * card: black body with a grey world map, flowing white lines, gold chip,
 * gold lettering and the company logo top-right.
 *
 * The status printed on the image is read from the database at render time.
 * The image is only a visual — it is never used to verify a card.
 */
class DigitalCardImageRenderer
{
    private const W = 1200;
    private const H = 740;

    // Gold lettering (matches the logo gold)
    private const GOLD_LIGHT = [247, 226, 120];
    private const GOLD_DARK  = [196, 156, 40];

    private string $font;
    private string $fontBold;

    public function __construct()
    {
        $dir = base_path('vendor/dompdf/dompdf/lib/fonts/');
        $this->font     = resource_path('fonts/card-regular.ttf');
        $this->fontBold = resource_path('fonts/card-bold.ttf');

        if (!is_file($this->font)) {
            $this->font = $dir . 'DejaVuSans.ttf';
        }
        if (!is_file($this->fontBold)) {
            $this->fontBold = $dir . 'DejaVuSans-Bold.ttf';
        }
    }

    public function render(DigitalCard $card, bool $showPin = true): string
    {
        $img = imagecreatetruecolor(self::W, self::H);
        imagesavealpha($img, true);
        imagealphablending($img, true);

        $this->drawBackground($img);
        $this->drawWaves($img);
        $this->drawChip($img, 130, 235, 165, 130);
        $this->drawBrand($img);

        $grey = imagecolorallocatealpha($img, 185, 185, 185, 10);

        // Amount
        imagettftext($img, 17, 0, 350, 268, $grey, $this->font, 'Amount');
        $this->goldText($img, 44, 350, 340, $this->font, '$' . $this->formatAmount((float) $card->amount));

        // Valid thru
        imagettftext($img, 13, 0, 350, 400, $grey, $this->font, 'VALID THRU');
        $this->goldText($img, 22, 470, 402, $this->font, $card->expires_at->format('m/y'));

        // Assigned recipient (if any)
        if ($card->recipient) {
            imagettftext($img, 13, 0, 82, 470, $grey, $this->font, 'ASSIGNED TO');
            $this->goldText($img, 20, 210, 472, $this->font, mb_strtoupper(mb_substr($card->recipient->name, 0, 28)));
        }

        // Card number
        $this->goldText($img, 60, 78, 585, $this->font, $card->formattedNumber());

        // PIN + status
        imagettftext($img, 17, 0, 82, 668, $grey, $this->font, 'Pin');
        $this->goldText($img, 40, 140, 676, $this->font, $showPin ? (string) $card->pin : '••••');

        imagettftext($img, 17, 0, 760, 668, $grey, $this->font, 'Status');
        [$label, $rgb] = $this->statusDisplay($card);
        if ($rgb === null) {
            $this->goldText($img, 40, 850, 676, $this->font, $label);
        } else {
            imagettftext($img, 40, 0, 850, 676, imagecolorallocate($img, ...$rgb), $this->font, $label);
        }

        // Reference footer
        $tiny = imagecolorallocatealpha($img, 120, 120, 120, 40);
        imagettftext($img, 11, 0, 82, 718, $tiny, $this->font,
            'Card ID ' . $card->reference . '  ·  Status shown at ' . now()->format('d M Y H:i') . ' — always verify in the portal');

        $this->roundCorners($img, 38);

        ob_start();
        imagepng($img, null, 6);
        $png = ob_get_clean();
        imagedestroy($img);

        return $png;
    }

    /** @return array{0:string,1:?array} label + solid colour (null = gold gradient) */
    private function statusDisplay(DigitalCard $card): array
    {
        if (in_array($card->status, [DigitalCard::STATUS_AVAILABLE, DigitalCard::STATUS_SHARED], true)) {
            return $card->isPastExpiry() ? ['Expired', [220, 70, 70]] : ['Valid', null];
        }

        return match ($card->status) {
            DigitalCard::STATUS_RESERVED  => ['Pending', [240, 180, 60]],
            DigitalCard::STATUS_USED      => ['Used', [160, 160, 160]],
            DigitalCard::STATUS_EXPIRED   => ['Expired', [220, 70, 70]],
            DigitalCard::STATUS_CANCELLED => ['Cancelled', [220, 70, 70]],
            default                       => [ucfirst($card->status), [160, 160, 160]],
        };
    }

    private function formatAmount(float $amount): string
    {
        return fmod($amount, 1.0) === 0.0 ? number_format($amount, 0, '.', '') : number_format($amount, 2, '.', '');
    }

    /** Black body, soft highlight, then the grey world map on top. */
    private function drawBackground(GdImage $img): void
    {
        $bg = public_path((string) config('digital_cards.background_image', ''));
        if (config('digital_cards.background_image') && is_file($bg) && ($src = @imagecreatefromstring(file_get_contents($bg)))) {
            imagecopyresampled($img, $src, 0, 0, 0, 0, self::W, self::H, imagesx($src), imagesy($src));
            imagedestroy($src);
            return;
        }

        // Charcoal → black, left to right
        for ($x = 0; $x < self::W; $x++) {
            $c = (int) round(30 - 24 * ($x / self::W));
            imageline($img, $x, 0, $x, self::H, imagecolorallocate($img, $c, $c, $c));
        }
        // Gentle glow behind the upper-left area
        $glow = imagecolorallocatealpha($img, 255, 255, 255, 126);
        for ($r = 560; $r > 40; $r -= 26) {
            imagefilledellipse($img, 320, 160, $r * 2, (int) ($r * 1.3), $glow);
        }

        // World map (pre-rendered transparent PNG, opacity baked in)
        $map = public_path((string) config('digital_cards.map_image', 'assets/custom-images/digital-card-map.png'));
        if (is_file($map) && ($layer = @imagecreatefrompng($map))) {
            imagealphablending($img, true);
            imagecopyresampled($img, $layer, 0, 0, 0, 0, self::W, self::H, imagesx($layer), imagesy($layer));
            imagedestroy($layer);
        }
    }

    /** Bundle of thin white lines sweeping across the card. */
    private function drawWaves(GdImage $img): void
    {
        imagesetthickness($img, 2);
        for ($k = 0; $k < 22; $k++) {
            $color = imagecolorallocatealpha($img, 225, 225, 225, 72 + ($k % 3) * 8);
            $prevX = null;
            $prevY = null;
            for ($x = -10; $x <= self::W + 10; $x += 6) {
                $t = $x / self::W;
                $y = 80 + $k * 16
                    + 120 * sin($t * M_PI * 1.15 + 0.35)
                    + ($k * 2.6) * sin($t * M_PI * 2.0 + $k * 0.12)
                    + 150 * $t;
                if ($prevX !== null) {
                    imageline($img, $prevX, (int) $prevY, $x, (int) $y, $color);
                }
                $prevX = $x;
                $prevY = $y;
            }
        }
        imagesetthickness($img, 1);
    }

    private function drawChip(GdImage $img, int $x, int $y, int $w, int $h): void
    {
        $chip = imagecreatetruecolor($w, $h);
        for ($i = 0; $i < $h; $i++) {
            [$r, $g, $b] = $this->mix([247, 226, 140], [214, 178, 80], $i / $h);
            imageline($chip, 0, $i, $w, $i, imagecolorallocate($chip, $r, $g, $b));
        }
        $line = imagecolorallocate($chip, 150, 112, 30);
        imagesetthickness($chip, 3);
        $third = (int) ($h / 3);
        imageline($chip, 0, $third, (int) ($w * 0.33), $third, $line);
        imageline($chip, 0, $third * 2, (int) ($w * 0.33), $third * 2, $line);
        imageline($chip, (int) ($w * 0.67), $third, $w, $third, $line);
        imageline($chip, (int) ($w * 0.67), $third * 2, $w, $third * 2, $line);
        imagerectangle($chip, (int) ($w * 0.33), 14, (int) ($w * 0.67), $h - 14, $line);
        imageline($chip, (int) ($w * 0.33), (int) ($h / 2), 0, (int) ($h / 2), $line);
        imageline($chip, (int) ($w * 0.67), (int) ($h / 2), $w, (int) ($h / 2), $line);
        imagerectangle($chip, (int) ($w * 0.42), (int) ($h * 0.3), (int) ($w * 0.58), (int) ($h * 0.7), $line);
        $this->roundCorners($chip, 22);

        imagecopy($img, $chip, $x, $y, 0, 0, $w, $h);
        imagedestroy($chip);
    }

    private function drawBrand(GdImage $img): void
    {
        if ($this->drawLogo($img)) {
            return;
        }

        // Fallback when the logo file is missing: gold text brand.
        $line1 = (string) config('digital_cards.brand_line_1', 'GVI');
        $line2 = (string) config('digital_cards.brand_line_2', 'INTERNATIONAL');

        if ($line1 !== '') {
            $box = imagettfbbox(92, 0, $this->fontBold, $line1);
            $this->goldText($img, 92, self::W - 95 - ($box[2] - $box[0]), 175, $this->fontBold, $line1);
        }
        if ($line2 !== '') {
            $spaced = implode(' ', mb_str_split($line2));
            $box    = imagettfbbox(30, 0, $this->fontBold, $spaced);
            $this->goldText($img, 30, self::W - 95 - ($box[2] - $box[0]), 232, $this->fontBold, $spaced);
        }
    }

    /**
     * Company logo (transparent PNG, original colours) top-right, scaled to fit.
     * Returns false when no usable logo file exists.
     */
    private function drawLogo(GdImage $img): bool
    {
        $path = public_path(DigitalCardSettings::logoPath());
        if (!is_file($path) || !($logo = @imagecreatefromstring(file_get_contents($path)))) {
            return false;
        }
        // Palette PNGs must be converted so their transparency survives resampling.
        if (!imageistruecolor($logo)) {
            imagepalettetotruecolor($logo);
        }
        imagealphablending($logo, true);
        imagesavealpha($logo, true);

        $srcW  = imagesx($logo);
        $srcH  = imagesy($logo);
        $scale = min(640 / $srcW, 130 / $srcH);
        $dstW  = (int) round($srcW * $scale);
        $dstH  = (int) round($srcH * $scale);

        imagealphablending($img, true);
        imagecopyresampled($img, $logo, self::W - 70 - $dstW, 62, 0, 0, $dstW, $dstH, $srcW, $srcH);
        imagedestroy($logo);

        return true;
    }

    /**
     * Draw text filled with a vertical gold gradient: render the text as a
     * mask on a scratch layer, then paint gradient colours through it.
     */
    private function goldText(GdImage $img, float $size, int $x, int $y, string $font, string $text): void
    {
        $box  = imagettfbbox($size, 0, $font, $text);
        $minX = min($box[0], $box[6]);
        $minY = min($box[5], $box[7]);
        $w = max($box[2], $box[4]) - $minX + 4;
        $h = max($box[1], $box[3]) - $minY + 4;
        if ($w <= 0 || $h <= 0) {
            return;
        }

        $mask = imagecreatetruecolor($w, $h);
        imagefill($mask, 0, 0, imagecolorallocate($mask, 0, 0, 0));
        imagettftext($mask, $size, 0, 2 - $minX, 2 - $minY, imagecolorallocate($mask, 255, 255, 255), $font, $text);

        $ox = $x + $minX - 2;
        $oy = $y + $minY - 2;
        for ($py = 0; $py < $h; $py++) {
            [$r, $g, $b] = $this->mix(self::GOLD_LIGHT, self::GOLD_DARK, $py / $h);
            for ($px = 0; $px < $w; $px++) {
                $coverage = imagecolorat($mask, $px, $py) & 0xFF;
                if ($coverage === 0) {
                    continue;
                }
                $alpha = 127 - (int) round($coverage / 255 * 127);
                imagesetpixel($img, $ox + $px, $oy + $py, imagecolorallocatealpha($img, $r, $g, $b, $alpha));
            }
        }
        imagedestroy($mask);
    }

    private function roundCorners(GdImage $img, int $radius): void
    {
        $w = imagesx($img);
        $h = imagesy($img);
        imagealphablending($img, false);
        imagesavealpha($img, true);
        $clear = imagecolorallocatealpha($img, 0, 0, 0, 127);

        $corners = [[$radius, $radius], [$w - $radius - 1, $radius], [$radius, $h - $radius - 1], [$w - $radius - 1, $h - $radius - 1]];
        foreach ($corners as $i => [$cx, $cy]) {
            $xs = $i % 2 === 0 ? range(0, $radius) : range($w - $radius - 1, $w - 1);
            $ys = $i < 2 ? range(0, $radius) : range($h - $radius - 1, $h - 1);
            foreach ($xs as $px) {
                foreach ($ys as $py) {
                    if ((($px - $cx) ** 2 + ($py - $cy) ** 2) > $radius ** 2) {
                        imagesetpixel($img, $px, $py, $clear);
                    }
                }
            }
        }
        imagealphablending($img, true);
    }

    private function mix(array $a, array $b, float $t): array
    {
        return [
            (int) round($a[0] + ($b[0] - $a[0]) * $t),
            (int) round($a[1] + ($b[1] - $a[1]) * $t),
            (int) round($a[2] + ($b[2] - $a[2]) * $t),
        ];
    }
}
