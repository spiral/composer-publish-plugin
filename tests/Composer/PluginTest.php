<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Composer\Tests;

use Composer\Composer;
use Composer\Config;
use Composer\Package\Package;
use Composer\Package\RootPackage;
use Composer\Repository\RepositoryManager;
use Composer\Repository\WritableArrayRepository;
use Composer\Script\Event;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Spiral\Composer\PublishPlugin;

class PluginTest extends TestCase
{
    /** @var PublishPlugin */
    private $plugin;

    /** @var Composer */
    private $composer;

    private $io;

    /** @var MockObject */
    private $im;

    public function setUp()
    {
        parent::setUp();
        $this->composer = new Composer();
        $this->composer->setConfig(new Config(true, getcwd()));
        $this->io = $this->createMock('Composer\IO\IOInterface');

        $root = new RootPackage("root", "stable", "stable");
        $root->setExtra(['publish-cmd' => 'php "tests/handler.php"']);
        $this->composer->setPackage($root);

        $this->composer->setRepositoryManager(
            new RepositoryManager($this->io, $this->composer->getConfig())
        );
        $this->composer->getRepositoryManager()->setLocalRepository(new WritableArrayRepository());

        $this->im = $this->createMock('Composer\Installer\InstallationManager');
        $this->composer->setInstallationManager($this->im);

        $this->plugin = new PublishPlugin();
        $this->plugin->activate($this->composer, $this->io);
    }

    public function tearDown()
    {
        if (file_exists("tests/handler.log")) {
            unlink("tests/handler.log");
        }
    }

    public function testGetSubscribedEvents()
    {
        $this->assertSame(
            [
                'post-autoload-dump' => [['publishFiles', 0]]
            ],
            $this->plugin->getSubscribedEvents()
        );
    }

    public function testPublishFiles()
    {
        $p = new Package("sample", "stable", "stable");
        $p->setExtra([
            'publish' => [
                'file.json:@public/file.json' => 'replace:readonly'
            ]
        ]);

        $this->composer->getRepositoryManager()->getLocalRepository()->addPackage($p);
        $this->im->method('getInstallPath')->willReturn('/vendor/sample/');

        $this->plugin->publishFiles(new Event("post-autoload-dump", $this->composer, $this->io));

        $done = file_get_contents('tests/handler.log');
        $this->assertSame(
            'tests/handler.php replace @public/file.json /vendor/sample/file.json readonly',
            $done
        );
    }

    public function testPublishFilesNoHandler()
    {
        $root = new RootPackage("root", "stable", "stable");
        $root->setExtra([]);
        $this->composer->setPackage($root);

        $p = new Package("sample", "stable", "stable");
        $p->setExtra([
            'publish' => [
                'file.json:@public/file.json' => 'replace:readonly'
            ]
        ]);

        $this->composer->getRepositoryManager()->getLocalRepository()->addPackage($p);
        $this->im->method('getInstallPath')->willReturn('/vendor/sample/');

        $this->plugin->publishFiles(new Event("post-autoload-dump", $this->composer, $this->io));

        $this->assertFileNotExists('tests/handler.log');
    }

    public function testPublishFilesNoFiles()
    {
        $p = new Package("sample", "stable", "stable");
        $p->setExtra([]);

        $this->composer->getRepositoryManager()->getLocalRepository()->addPackage($p);
        $this->im->method('getInstallPath')->willReturn('/vendor/sample/');

        $this->plugin->publishFiles(new Event("post-autoload-dump", $this->composer, $this->io));

        $this->assertFileNotExists('tests/handler.log');
    }
}