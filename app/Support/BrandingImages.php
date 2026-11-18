<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Website CMS → Branding: the connected website's logo and browser icon.
 *
 *   logo     PNG, WebP or SVG (SVGs with scripts, event handlers or external links are refused,
 *            because the file is served from the CRM's own domain).
 *   favicon  PNG/JPG/WebP, at least 512 × 512; cropped to a centred square and saved as PNGs
 *            in the sizes browsers and phones ask for (32, 180, 192, 512).
 *
 * Files go to the public disk under branding/{organization}/.
 */
class BrandingImages
{
    public const FAVICON_SIZES = [32, 180, 192, 512];

    public const FAVICON_MIN = 512;

    /** @return array{url: string} */
    public static function storeLogo(UploadedFile $file, int $organizationId): array
    {
        $ext = strtolower($file->getClientOriginalExtension());
        if ($ext === 'svg' || $file->getMimeType() === 'image/svg+xml') {
            self::assertSafeSvg((string) file_get_contents($file->getRealPath()));
            $ext = 'svg';
        }
        $path = $file->storeAs(self::folder($organizationId), 'logo-'.Str::lower(Str::random(10)).'.'.$ext, 'public');

        return ['url' => Storage::disk('public')->url($path)];
    }

    /** @return array{url: string, icons: array<string, string>, cropped: bool} */
    public static function storeFavicon(UploadedFile $file, int $organizationId): array
    {
        $source = @imagecreatefromstring((string) file_get_contents($file->getRealPath()));
        if (! $source) {
            throw new InvalidArgumentException('That file couldn’t be read as an image. Please use a PNG or JPG.');
        }
        $w = imagesx($source);
        $h = imagesy($source);
        if (min($w, $h) < self::FAVICON_MIN) {
            imagedestroy($source);
            throw new InvalidArgumentException("The browser icon must be at least 512 × 512 pixels (this one is {$w} × {$h}).");
        }

        // Centred square crop.
        $side = min($w, $h);
        $x = intdiv($w - $side, 2);
        $y = intdiv($h - $side, 2);

        $folder = self::folder($organizationId);
        $stamp = Str::lower(Str::random(8));
        $icons = [];
        foreach (self::FAVICON_SIZES as $size) {
            $canvas = imagecreatetruecolor($size, $size);
            imagealphablending($canvas, false);
            imagesavealpha($canvas, true);
            imagefill($canvas, 0, 0, imagecolorallocatealpha($canvas, 0, 0, 0, 127));
            imagecopyresampled($canvas, $source, 0, 0, $x, $y, $size, $size, $side, $side);
            ob_start();
            imagepng($canvas, null, 9);
            $png = (string) ob_get_clean();
            imagedestroy($canvas);

            $path = "{$folder}/favicon-{$stamp}-{$size}.png";
            Storage::disk('public')->put($path, $png);
            $icons[(string) $size] = Storage::disk('public')->url($path);
        }
        imagedestroy($source);

        return ['url' => $icons['512'], 'icons' => $icons, 'cropped' => $w !== $h];
    }

    /** Refuse SVGs that could run code or load things when opened directly. */
    public static function assertSafeSvg(string $svg): void
    {
        $unsafe = [
            '/<\s*script\b/i', '/\son[a-z]+\s*=/i', '/javascript\s*:/i', '/<\s*foreignObject\b/i',
            '/<\s*(iframe|embed|object)\b/i', '/<!ENTITY/i', '/(xlink:)?href\s*=\s*["\']\s*(?!#|data:image\/)/i',
        ];
        if (! preg_match('/<svg\b/i', $svg)) {
            throw new InvalidArgumentException('That file isn’t a valid SVG image.');
        }
        foreach ($unsafe as $pattern) {
            if (preg_match($pattern, $svg)) {
                throw new InvalidArgumentException('This SVG contains code or links, so it can’t be used. Export it again as a plain SVG, or use a PNG.');
            }
        }
    }

    private static function folder(int $organizationId): string
    {
        return "branding/{$organizationId}";
    }
}
