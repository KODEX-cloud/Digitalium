<?php
namespace App\Helpers;

class Sanitizer {
    /**
     * Sanitize string or recursive arrays against XSS injections.
     */
    public static function clean(mixed $data): mixed {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = self::clean($value);
            }
            return $data;
        }

        if (is_string($data)) {
            return htmlspecialchars(trim($data), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }

        return $data;
    }

    /**
     * Clean rich HTML text (Quill editor output) by removing malicious tags 
     * but preserving layout and structure tags (p, h1, h2, ul, li, strong, em).
     */
    public static function cleanHtml(string $html): string {
        // Strip script tags completely
        $html = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $html);
        
        // Remove harmful attributes like onload, onclick, onerror
        $html = preg_replace('#\s(on[a-z]+)\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]*)\s*(?=[/>\s])#is', '', $html);
        
        // Remove javascript: links
        $html = preg_replace('#href\s*=\s*("|\')\s*javascript:(.*?)\s*\1#is', 'href="#"', $html);

        return trim($html);
    }
}
