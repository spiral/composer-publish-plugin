<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Composer;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;

class PublishPlugin implements PluginInterface
{
    /** @var Composer instance */
    private $composer;

    /** @var IOInterface */
    private $io;

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
     * This is the main function.
     *
     * @param Event $event
     */
    public function publishFiles(Event $event)
    {
        print_r($event);
    }

    /**
     * @return array list of events
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'post-autoload-dump' => [['publishFiles', 0]],
        ];
    }
}