<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Composer;

use Spiral\Composer\Exception\PublishException;

final class Command
{
    /** @var string */
    private $target;

    /** @var null|string */
    private $source;

    /** @var string */
    private $type;

    /** @var null|string */
    private $mode;

    /**
     * @return string
     */
    public function getTarget(): string
    {
        return $this->target;
    }

    /**
     * @return null|string
     */
    public function getSource(): ?string
    {
        return $this->source;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return null|string
     */
    public function getMode(): ?string
    {
        return $this->mode;
    }

    /**
     * @param string $path
     * @param string $data
     * @param string $options
     * @return Command
     *
     * @throws PublishException
     */
    public static function parse(string $path, string $data, string $options): Command
    {
        $publish = new static();

        if (strpos($data, ':') !== false) {
            list($publish->source, $publish->target) = explode(':', $data);
        } else {
            $publish->target = $data;
        }

        if (!empty($publish->source)) {
            $publish->source = rtrim($path, '/') . '/' . $publish->source;
        }

        if (strpos($options, ':') !== false) {
            list($publish->type, $publish->mode) = explode(':', $options);
            self::validateMode($publish->mode);
        } else {
            $publish->type = $options;
        }

        self::validateType($publish->type);

        return $publish;
    }

    /**
     * @param string $type
     *
     * @throws PublishException
     */
    private static function validateType(string $type)
    {
        if (!in_array($type, ['follow', 'replace', 'ensure'])) {
            throw new PublishException("Invalid operation type `{$type}`.");
        }
    }

    /**
     * @param string $mode
     *
     * @throws PublishException
     */
    private static function validateMode(string $mode)
    {
        if (!in_array($mode, ['runtime', 'readonly'])) {
            throw new PublishException("Invalid file mode `{$mode}`.");
        }
    }
}