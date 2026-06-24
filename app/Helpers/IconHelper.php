<?php
namespace App\Helpers;

/**
 * Reusable Premium Icon Component Helper
 * Supports media library images, inline SVGs, FontAwesome libraries, and Lucide vector icons.
 */
class IconHelper {
    /**
     * Render an icon, image, SVG, FontAwesome, or Lucide tag.
     *
     * @param string $iconValue The icon identifier (e.g., 'cpu', 'fa-solid fa-code', '<svg...', or image path)
     * @param array $options Configuration options (size, color, class, image, style)
     * @return string HTML Markup
     */
    public static function render(string $iconValue, array $options = []): string {
        $iconValue = trim($iconValue);
        $image = trim($options['image'] ?? '');
        $size = $options['size'] ?? '24px';
        $color = $options['color'] ?? '';
        $class = $options['class'] ?? '';
        $style = $options['style'] ?? '';

        // 1. If a replacement image is explicitly provided and is not empty, use it
        if (!empty($image)) {
            $imgStyle = "width: {$size}; height: {$size}; object-fit: contain; {$style}";
            return sprintf(
                '<img src="%s" class="%s" style="%s" alt="Icon" />',
                htmlspecialchars(url($image)),
                htmlspecialchars($class),
                $imgStyle
            );
        }

        // 2. Check if the iconValue itself is an image path (ends with typical extensions)
        $isImage = preg_match('/\.(png|jpg|jpeg|svg|webp|gif)$/i', $iconValue);
        if ($isImage) {
            $imgStyle = "width: {$size}; height: {$size}; object-fit: contain; {$style}";
            return sprintf(
                '<img src="%s" class="%s" style="%s" alt="Icon" />',
                htmlspecialchars(url($iconValue)),
                htmlspecialchars($class),
                $imgStyle
            );
        }

        // 3. Check if it's an inline SVG (starts with <svg or has SVG children elements)
        if (str_starts_with($iconValue, '<svg') || str_starts_with($iconValue, '<path') || str_starts_with($iconValue, '<g') || str_contains($iconValue, '</svg>')) {
            $svgStyle = "width: {$size}; height: {$size}; display: inline-flex; align-items: center; justify-content: center; {$style}";
            if (!empty($color)) {
                $svgStyle .= " color: {$color}; fill: currentColor;";
            }
            
            // If it's just a path, wrap it in a standard responsive SVG tag
            if (str_starts_with($iconValue, '<path') || str_starts_with($iconValue, '<g')) {
                return sprintf(
                    '<svg class="%s" style="%s" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">%s</svg>',
                    htmlspecialchars($class),
                    $svgStyle,
                    $iconValue
                );
            }
            
            // If it has SVG, return it directly but wrapped in a helper div to enforce dimensions
            return sprintf(
                '<div class="svg-icon-wrap %s" style="%s">%s</div>',
                htmlspecialchars($class),
                $svgStyle,
                $iconValue
            );
        }

        // 4. Check if it's a FontAwesome icon
        $isFontAwesome = str_contains($iconValue, 'fa-') || preg_match('/^(fas|fab|far|fal|fad|fa)\s/i', $iconValue);
        if ($isFontAwesome) {
            $faStyle = "font-size: {$size}; width: {$size}; height: {$size}; display: inline-flex; align-items: center; justify-content: center; line-height: 1; {$style}";
            if (!empty($color)) {
                $faStyle .= " color: {$color};";
            }
            return sprintf(
                '<i class="%s %s" style="%s" aria-hidden="true"></i>',
                htmlspecialchars($iconValue),
                htmlspecialchars($class),
                $faStyle
            );
        }

        // 5. Default to Lucide vector icon
        $lucideStyle = "width: {$size}; height: {$size}; display: inline-flex; align-items: center; justify-content: center; {$style}";
        if (!empty($color)) {
            $lucideStyle .= " color: {$color};";
        }
        return sprintf(
            '<i data-lucide="%s" class="%s" style="%s"></i>',
            htmlspecialchars($iconValue ?: 'check'),
            htmlspecialchars($class),
            $lucideStyle
        );
    }
}
