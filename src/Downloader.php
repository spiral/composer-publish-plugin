<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Composer;

use Composer\Package\PackageInterface;

final class Downloader
{
    /** @var string */
    private $url;

    /** @var string */
    private $dir;

    /**
     * @param PackageInterface $package
     * @param string           $filename
     */
    public function __construct(PackageInterface $package, string $filename)
    {
        $this->url = str_replace(
            [
            '{tag}',
            '{file}'
            ],
            [
            $package->getPrettyVersion(),
            $filename
            ],
            $package->getExtra()['release-url']
        );
    }

    /**
     * Clean up.
     */
    public function __destruct()
    {
        if ($this->dir === null) {
            return;
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $file) {
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }

        rmdir($this->dir);
    }

    /**
     * @return string
     */
    public function getURL(): string
    {
        return $this->url;
    }

    /**
     * Creates temporary directory in a given path and returns its.
     *
     * @param string $directory
     * @return string
     * @throws \ErrorException
     */
    public function download(string $directory): string
    {
        if (!extension_loaded('zip')) {
            throw new \ErrorException('ZIP extension missing');
        }

        $this->dir = $directory . '/' . md5($this->url) . '/';
        mkdir($this->dir);

        $zipFilename = $this->dir . basename($this->url);

        file_put_contents($zipFilename, file_get_contents($this->url));

        $zip = new \ZipArchive();
        $zip->open($zipFilename);
        $zip->extractTo($this->dir);
        $zip->close();

        unlink($zipFilename);

        return $this->dir;
    }
}
