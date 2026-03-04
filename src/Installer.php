<?php
declare(strict_types=1);

namespace Manhattan;

use Composer\Script\Event;

/**
 * Composer script handler — publishes Manhattan web assets into the consuming
 * project's web root so CSS/JS can be served by the web server.
 *
 * Triggered automatically on `composer install` and `composer update`.
 *
 * Configuration (in the consuming project's composer.json "extra" section):
 *
 *   "extra": {
 *       "manhattan": {
 *           "public-dir": "public"   // default: "public", use "." for legacy roots
 *       }
 *   }
 *
 * Assets are published to: <public-dir>/Manhattan/
 */
class Installer
{
    public static function publishAssets(Event $event): void
    {
        $composer = $event->getComposer();
        $io = $event->getIO();

        // Resolve asset source (inside this package)
        $packageDir = realpath(__DIR__ . '/..');
        if ($packageDir === false) {
            $io->writeError('<error>Manhattan: could not resolve package directory.</error>');
            return;
        }
        $assetSrc = $packageDir . '/assets';

        // Resolve destination from the consuming project's composer.json extra config
        $rootExtra = $composer->getPackage()->getExtra();
        $publicDir = (string)($rootExtra['manhattan']['public-dir'] ?? $rootExtra['manhattan']['public_dir'] ?? 'public');

        // Vendor dir is inside the project root; go two levels up from vendor/<owner>/<pkg>
        $vendorDir = $composer->getConfig()->get('vendor-dir');
        if (!is_string($vendorDir)) {
            $io->writeError('<error>Manhattan: could not resolve vendor directory.</error>');
            return;
        }
        $projectRoot = dirname($vendorDir);
        $assetDest = $projectRoot . '/' . ltrim($publicDir, '/') . '/Manhattan';

        self::copyDirectory($assetSrc, $assetDest, $io);

        $io->write(sprintf(
            '<info>Manhattan: assets published to %s/Manhattan/</info>',
            $publicDir
        ));
    }

    /**
     * Recursively copy $src directory to $dest.
     *
     * @param \Composer\IO\IOInterface $io
     */
    private static function copyDirectory(string $src, string $dest, $io): void
    {
        if (!is_dir($dest) && !mkdir($dest, 0755, true) && !is_dir($dest)) {
            $io->writeError(sprintf('<warning>Manhattan: could not create directory %s</warning>', $dest));
            return;
        }

        $items = scandir($src);
        if ($items === false) {
            return;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $srcPath = $src . '/' . $item;
            $destPath = $dest . '/' . $item;

            if (is_dir($srcPath)) {
                self::copyDirectory($srcPath, $destPath, $io);
            } else {
                copy($srcPath, $destPath);
            }
        }
    }
}
