#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Manhattan PHP 7.4 Compatibility Checker
 *
 * Scans all PHP source files for PHP 8.0+ features that won't work on PHP 7.4.
 * Exit code 0 = compatible, 1 = incompatible syntax found
 */

const COLOR_RED    = "\033[0;31m";
const COLOR_GREEN  = "\033[0;32m";
const COLOR_YELLOW = "\033[1;33m";
const COLOR_BLUE   = "\033[0;34m";
const COLOR_NC     = "\033[0m";

// Directories to scan
$dirsToScan = [
    __DIR__ . '/../src',
    __DIR__ . '/../demo',
];

// PHP 8.0+ features to detect
$php80Patterns = [
    // Constructor property promotion
    '/public\s+function\s+__construct\s*\(\s*(?:public|protected|private)/' => 
        'Constructor property promotion (PHP 8.0+)',
    
    // Nullsafe operator
    '/\$[a-zA-Z_][a-zA-Z0-9_]*\s*\?\-\>/' => 
        'Nullsafe operator ?-> (PHP 8.0+)',
    
    // Match expression
    '/\bmatch\s*\(/' => 
        'Match expression (PHP 8.0+)',
    
    // Union types (beyond nullable) - more specific pattern
    '/function\s+[a-zA-Z_][a-zA-Z0-9_]*\s*\([^)]*\):\s*(?:int|string|bool|float|array|object)\s*\|\s*(?:int|string|bool|float|array|object)/' =>
        'Union types (PHP 8.0+)',
    
    // Mixed type hint
    '/:\s*mixed\s*[;{]/' =>
        'Mixed type hint (PHP 8.0+)',
];

// PHP 8.0+ functions
$php80Functions = [
    'str_contains',
    'str_starts_with',
    'str_ends_with',
    'fdiv',
    'get_debug_type',
    'get_resource_id',
    'preg_last_error_msg',
];

$errors = [];

echo COLOR_BLUE . "=========================================\n";
echo " Manhattan PHP 7.4 Compatibility Check\n";
echo "=========================================" . COLOR_NC . "\n\n";

foreach ($dirsToScan as $dir) {
    if (!is_dir($dir)) {
        echo COLOR_YELLOW . "⚠ Skipping $dir (not found)\n" . COLOR_NC;
        continue;
    }
    
    echo "Scanning " . basename($dir) . "/...\n";
    
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    
    foreach ($iterator as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }
        
        $path = $file->getPathname();
        $content = file_get_contents($path);
        
        if ($content === false) {
            continue;
        }
        
        // Remove comments and strings to reduce false positives
        $cleaned = preg_replace([
            '#/\*.*?\*/#s',           // Multi-line comments
            '#//.*$#m',                // Single-line comments
            '#\'(?:\\\\.|[^\'])*\'#', // Single-quoted strings
            '#"(?:\\\\.|[^"])*"#',    // Double-quoted strings
        ], '', $content);
        
        // Check for PHP 8.0+ patterns
        foreach ($php80Patterns as $pattern => $description) {
            if (preg_match($pattern, $cleaned, $matches, PREG_OFFSET_CAPTURE)) {
                $line = substr_count($content, "\n", 0, $matches[0][1]) + 1;
                $relPath = str_replace(__DIR__ . '/../', '', $path);
                $errors[] = [
                    'file' => $relPath,
                    'line' => $line,
                    'desc' => $description,
                    'code' => trim($matches[0][0]),
                ];
            }
        }
        
        // Check for PHP 8.0+ functions
        foreach ($php80Functions as $func) {
            if (preg_match('/\b' . preg_quote($func, '/') . '\s*\(/', $cleaned, $matches, PREG_OFFSET_CAPTURE)) {
                $line = substr_count($content, "\n", 0, $matches[0][1]) + 1;
                $relPath = str_replace(__DIR__ . '/../', '', $path);
                $errors[] = [
                    'file' => $relPath,
                    'line' => $line,
                    'desc' => "PHP 8.0+ function: $func()",
                    'code' => trim($matches[0][0]),
                ];
            }
        }
    }
}

echo "\n";

if (empty($errors)) {
    echo COLOR_GREEN . "✓ No PHP 8.0+ features detected" . COLOR_NC . "\n";
    echo COLOR_GREEN . "✓ Code is PHP 7.4 compatible" . COLOR_NC . "\n\n";
    exit(0);
} else {
    echo COLOR_RED . "✗ Found " . count($errors) . " compatibility issue(s):\n\n" . COLOR_NC;
    
    foreach ($errors as $error) {
        echo COLOR_RED . "  ✗ " . COLOR_NC;
        echo $error['file'] . ":" . $error['line'] . "\n";
        echo "    " . COLOR_YELLOW . $error['desc'] . COLOR_NC . "\n";
        echo "    Code: " . COLOR_BLUE . $error['code'] . COLOR_NC . "\n\n";
    }
    
    echo COLOR_RED . "Fix these issues before deploying to a PHP 7.4 server.\n" . COLOR_NC;
    exit(1);
}
