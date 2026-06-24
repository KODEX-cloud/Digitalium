<?php
/**
 * Dynamic Section Renderer (Refactored)
 * Iterates through active sections and imports their view components.
 */

if (empty($sections)) {
    echo "<!-- No sections active on this page. -->";
} else {
    foreach ($sections as $sec) {
        $sectionId = $sec['id'];
        $type = $sec['type'];
        $name = $sec['name'];
        
        // Bypass duplicate legacy hero sections as the universal Hero covers them
        if (in_array($type, ['hero', 'about_hero', 'services_hero', 'blog_hero', 'contact_hero']) || str_contains($type, '_hero')) {
            continue;
        }
        
        $blocks = $sectionBlocks[$sectionId] ?? ['single' => [], 'groups' => []];
        $single = $blocks['single'];
        $groups = $blocks['groups'];

        $componentPath = APP_PATH . '/Views/frontend/sections/' . $type . '.php';

        if (file_exists($componentPath)) {
            echo "<!-- Section: " . htmlspecialchars($name) . " (" . $type . ") -->";
            
            (function($single, $groups, $sectionId, $settings, $currentSlug) use ($componentPath) {
                require $componentPath;
            })($single, $groups, $sectionId, $settings, $currentSlug);
            
            echo "<!-- End Section: " . htmlspecialchars($name) . " -->";
        } else {
            echo "<!-- Error: View component not found for type '" . htmlspecialchars($type) . "' -->";
        }
    }
}
