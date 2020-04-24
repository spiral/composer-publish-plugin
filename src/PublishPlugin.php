<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Composer;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Symfony\Component\Process\Process;

final class PublishPlugin implements PluginInterface, EventSubscriberInterface
{
    // Extra key to point to publish command handler
    public const PUBLISH_CMD = 'publish-cmd';

    // Extra key to declare files and directories to publish
    public const PUBLISH_KEY = 'publish';

    /** @var Composer instance */
    private $composer;

    /** @var IOInterface */
    private $io;

    /** @var array|null */
    private $lockedPackages = [];

    /**
     * @param Composer    $composer
     * @param IOInterface $io
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
    }

    /**
     * @param Event $event
     */
    public function captureLock(Event $event)
    {
        if ($event->getComposer()->getLocker()->isLocked()) {
            $this->lockedPackages = $event->getComposer()->getLocker()->getLockData()['packages'];
        }
    }

    /**
     * This is the main function.
     *
     * @param Event $event
     */
    public function publishFiles(Event $event)
    {
        $cmd = $this->composer->getPackage()->getExtra()[self::PUBLISH_CMD] ?? null;

        if ($cmd === null) {
            return;
        }

        $packages = $this->composer->getRepositoryManager()->getLocalRepository()->getCanonicalPackages();

        foreach ($packages as $package) {
            $publish = $package->getExtra()[self::PUBLISH_KEY] ?? null;

            if (empty($publish)) {//|| !$this->requiresUpdate($package)) {
                continue;
            }

            foreach ($publish as $data => $options) {
                $this->publish(
                    $cmd,
                    $this->composer->getInstallationManager()->getInstallPath($package),
                    $data,
                    $options,
                    $package
                );
            }
        }
    }

    /**
     * Return true if package has updated it's version.
     *
     * @param PackageInterface $package
     * @return bool
     */
    protected function requiresUpdate(PackageInterface $package): bool
    {
        foreach ($this->lockedPackages as $current) {
            if ($current['name'] === $package->getName()) {
                // version change
                return $package->getPrettyVersion() !== $current['version'];
            }
        }

        // newly installed
        return true;
    }

    /**
     * @param string           $cmd
     * @param string           $path
     * @param string           $data
     * @param string           $type
     * @param PackageInterface $package
     */
    protected function publish(
        string $cmd,
        string $path,
        string $data,
        string $type,
        PackageInterface $package
    ) {
        $publish = Command::parse($path, $data, $type);

        $source = $publish->getSource();
        if ($publish->isDownloaded()) {
            $downloader = new Downloader($package, $publish->getSource());

            if ($this->io->isVerbose()) {
                $this->io->write(
                    sprintf(
                        'Downloading <comment>%s</comment>... ',
                        $downloader->getURL()
                    ),
                    false
                );

                try {
                    $source = $downloader->download(sys_get_temp_dir());
                    $this->io->write('<info>OK</info>');
                } catch (\Throwable $e) {
                    $this->io->write(sprintf('<error>%s</error>', $e->getMessage()));
                    return;
                }
            }
        }

        $p = Process::fromShellCommandline(join(' ', [
            $cmd,
            escapeshellarg($publish->getType()),
            escapeshellarg($publish->getTarget()),
            escapeshellarg($source),
            escapeshellarg($publish->getMode())
        ]));
        $p->run();

        if (!$p->isSuccessful()) {
            $this->io->writeError($p->getOutput() . $p->getErrorOutput());

            return;
        }

        if ($this->io->isVerbose()) {
            $this->io->write($p->getOutput() . $p->getErrorOutput());
        }
    }

    /**
     * @return array list of events
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'pre-install-cmd'    => [['captureLock', 0]],
            'pre-update-cmd'     => [['captureLock', 0]],
            'post-update-dump'   => [['publishFiles', 0]],
            'post-autoload-dump' => [['publishFiles', 0]],
        ];
    }
}
