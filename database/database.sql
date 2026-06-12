-- Database Schema for Digitalium CMS
-- Laravel-style Architecture for PHP 8.1+

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `settings`;
DROP TABLE IF EXISTS `media`;
DROP TABLE IF EXISTS `blocks`;
DROP TABLE IF EXISTS `sections`;
DROP TABLE IF EXISTS `pages`;
DROP TABLE IF EXISTS `users`;

-- 1. Users Table
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Pages Table
CREATE TABLE IF NOT EXISTS `pages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL UNIQUE,
  `meta_title` VARCHAR(255) NULL,
  `meta_description` TEXT NULL,
  `status` ENUM('draft', 'published') DEFAULT 'draft',
  `sort_order` INT DEFAULT 0,
  `in_navigation` TINYINT DEFAULT 1,
  
  -- Advanced Hero settings
  `hero_title` VARCHAR(255) NULL,
  `hero_subtitle` TEXT NULL,
  `hero_image` VARCHAR(255) NULL,
  `hero_cta1_text` VARCHAR(100) NULL,
  `hero_cta1_url` VARCHAR(255) NULL,
  `hero_cta2_text` VARCHAR(100) NULL,
  `hero_cta2_url` VARCHAR(255) NULL,
  `hero_bg_color` VARCHAR(100) NULL,
  `hero_effect` VARCHAR(50) DEFAULT 'particles',
  `hero_variant` VARCHAR(50) DEFAULT 'hero_split_large_image',
  `hero_image_layout` VARCHAR(50) DEFAULT 'right',
  `hero_image_size` VARCHAR(50) DEFAULT 'large',
  `hero_badge` VARCHAR(255) NULL,
  `hero_status` TINYINT DEFAULT 1,
  
  -- Advanced Header settings
  `header_bg_mode` VARCHAR(50) DEFAULT 'glass',
  `header_opacity` FLOAT DEFAULT 0.65,
  `header_blur` INT DEFAULT 20,
  `header_shadow` VARCHAR(50) DEFAULT 'moyen',
  `header_contrast_mode` VARCHAR(50) DEFAULT 'default',
  
  -- Brand settings
  `logo_light` VARCHAR(255) NULL,
  `logo_dark` VARCHAR(255) NULL,
  `logo_size` INT DEFAULT 38,
  
  -- Advanced Hero positions & mobile layout
  `hero_layout_mode` VARCHAR(50) DEFAULT 'moyen',
  `hero_text_position` VARCHAR(50) DEFAULT 'centre',
  `hero_text_alignment` VARCHAR(50) DEFAULT 'center',
  `hero_text_width` VARCHAR(50) DEFAULT '100%',
  `hero_overlay_opacity` FLOAT DEFAULT 0.45,
  `hero_shadow_strength` VARCHAR(50) DEFAULT 'moyen',
  `hero_image_mobile` VARCHAR(255) NULL,
  `responsive_settings` TEXT NULL,
  
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2.1 Projects Table
CREATE TABLE IF NOT EXISTS `projects` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `category` VARCHAR(100) NOT NULL,
  `logo` VARCHAR(255) NULL,
  `main_image` VARCHAR(255) NOT NULL,
  `gallery` TEXT NULL,
  `context` TEXT NULL,
  `impact` TEXT NULL,
  `technologies` VARCHAR(255) NULL,
  `external_link` VARCHAR(255) NULL,
  `sort_order` INT DEFAULT 0,
  `is_featured` TINYINT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Sections Table
CREATE TABLE IF NOT EXISTS `sections` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `page_id` INT NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `type` VARCHAR(50) NOT NULL,
  `sort_order` INT DEFAULT 0,
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`page_id`) REFERENCES `pages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Blocks Table
CREATE TABLE IF NOT EXISTS `blocks` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `section_id` INT NOT NULL,
  `block_key` VARCHAR(100) NOT NULL,
  `type` ENUM('text', 'textarea', 'wysiwyg', 'image', 'link', 'color', 'number') DEFAULT 'text',
  `value` LONGTEXT NULL,
  `sort_order` INT DEFAULT 0,
  `group_id` INT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Media Table
CREATE TABLE IF NOT EXISTS `media` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `filename` VARCHAR(255) NOT NULL,
  `filepath` VARCHAR(255) NOT NULL,
  `original_name` VARCHAR(255) NOT NULL,
  `file_size` INT NOT NULL,
  `mime_type` VARCHAR(100) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Settings Table
CREATE TABLE IF NOT EXISTS `settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(100) NOT NULL UNIQUE,
  `setting_value` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
