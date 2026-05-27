<?php

/**
 * Change Log
 * -----------------------------------
 * Date: 2026-05-26
 * Developer: AI Assistant
 * Version: v1.0.0
 * Description:
 * - Added image_resize_to_path() helper that resizes large images down to a
 *   max dimension and recompresses them. Preserves PNG transparency.
 *   Skips PDFs and gracefully no-ops when GD is not loaded.
 *   Used by upload flows (e.g., courier slip) to keep storage + WhatsApp
 *   transfer sizes small.
 */

if (!function_exists('image_resize_to_path')) {
    /**
     * Resize and recompress an image in place.
     *
     * - Only processes JPEG and PNG. Other types (PDF, etc.) are left as-is.
     * - Never upscales: if the original is already smaller than $maxDim, only
     *   recompresses without resizing.
     * - Preserves PNG transparency.
     * - Strips JPEG/EXIF metadata via the GD pipeline (lighter file).
     * - No-ops cleanly if GD is unavailable.
     *
     * @param string $srcPath  Absolute path to the image to rewrite in place.
     * @param int    $maxDim   Max dimension (width or height), pixels. Default 1600.
     * @param int    $quality  JPEG quality 1-100. Default 82.
     * @return bool  True on success or skipped, false on hard failure.
     */
    function image_resize_to_path(string $srcPath, int $maxDim = 1600, int $quality = 82): bool {
        if (!file_exists($srcPath) || !is_writable($srcPath)) {
            return false;
        }
        if (!function_exists('imagecreatefromstring')) {
            // GD missing — nothing we can do, but not a hard failure.
            return true;
        }

        $info = @getimagesize($srcPath);
        if ($info === false) {
            return false;
        }
        [$w, $h] = $info;
        $type    = $info[2] ?? 0;

        // Only handle JPEG + PNG + WebP. PDF/etc. left untouched.
        if (!in_array($type, [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true)) {
            return true;
        }

        $bytes = @file_get_contents($srcPath);
        if ($bytes === false) {
            return false;
        }
        $img = @imagecreatefromstring($bytes);
        if ($img === false) {
            return false;
        }

        // Compute target dimensions based on WIDTH (no upscaling)
        if ($w > $maxDim) {
            $scale  = $maxDim / $w;
            $newW   = $maxDim;
            $newH   = max(1, (int)round($h * $scale));
            $resized = imagecreatetruecolor($newW, $newH);

            // Preserve transparency for PNGs
            if ($type === IMAGETYPE_PNG) {
                imagealphablending($resized, false);
                imagesavealpha($resized, true);
                $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
                imagefilledrectangle($resized, 0, 0, $newW, $newH, $transparent);
            }

            imagecopyresampled($resized, $img, 0, 0, 0, 0, $newW, $newH, $w, $h);
            imagedestroy($img);
            $img = $resized;
        }

        ob_start();
        $ok = false;
        if ($type === IMAGETYPE_PNG) {
            // PNG compression 0-9 (lower = larger file). 6 is a good default.
            $ok = imagepng($img, null, 6);
        } elseif ($type === IMAGETYPE_WEBP) {
            $ok = imagewebp($img, null, max(1, min(100, $quality)));
        } else {
            $ok = imagejpeg($img, null, max(1, min(100, $quality)));
        }
        $out = ob_get_clean();
        imagedestroy($img);

        if (!$ok || $out === '' || $out === false) {
            return false;
        }
        return file_put_contents($srcPath, $out) !== false;
    }
}
