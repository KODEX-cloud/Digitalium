<?php
/**
 * Public runner for blog + hero_features migration.
 * DELETE this file after running it once.
 */
define('SECURE_ACCESS', true);
require dirname(__DIR__) . '/database/add_blog_and_hero_features.php';
