<?php
/**
 * Admin subdirectory fallback redirect.
 * In case mod_rewrite is disabled or bypassed.
 */
header("Location: /admin/dashboard");
exit;
