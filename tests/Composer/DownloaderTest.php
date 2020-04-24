<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Composer;

use Composer\Package\Package;
use PHPUnit\Framework\TestCase;
use Spiral\Composer\Downloader;

class DownloaderTest extends TestCase
{
    public function testDownload(): void
    {
        $package = new Package('spiral/toolkit', '1.1.0.0', 'v1.1.0');
        $package->setExtra([
            'release-url' => 'https://github.com/spiral/toolkit/releases/download/{tag}/{file}',
        ]);

        $downloader = new Downloader($package, 'keeper.zip');

        $downloader->download(sys_get_temp_dir());

        $dir = sys_get_temp_dir() . '/' . md5($downloader->getURL());
        $this->assertDirectoryExists($dir);
        unset($downloader);
        $this->assertDirectoryNotExists($dir);
    }
}
