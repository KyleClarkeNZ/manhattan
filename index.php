<?php
declare(strict_types=1);

/**
 * Manhattan Demo — Production Entry Point
 * 
 * This file serves as the root entry point for the Manhattan demo.
 * It bootstraps the demo environment and includes the main demo page.
 */

// Redirect to demo directory for clean URLs, or include directly
// We'll include directly so all paths work correctly
require_once __DIR__ . '/demo/index.php';
