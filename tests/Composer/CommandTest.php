<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Composer\Tests;

use PHPUnit\Framework\TestCase;
use Spiral\Composer\Command;

class CommandTest extends TestCase
{
    public function testParseEnsure(): void
    {
        $p = Command::parse('.', '@public/dir', 'ensure');

        $this->assertSame(null, $p->getSource());
        $this->assertSame('@public/dir', $p->getTarget());
        $this->assertSame(null, $p->getMode());
        $this->assertSame('ensure', $p->getType());
    }

    public function testParseDirectory(): void
    {
        $p = Command::parse('.', 'dir:@public/dir', 'replace');

        $this->assertSame('./dir', $p->getSource());
        $this->assertSame('@public/dir', $p->getTarget());
        $this->assertSame(null, $p->getMode());
        $this->assertSame('replace', $p->getType());
    }

    public function testParseDirectoryStar(): void
    {
        $p = Command::parse('.', 'dir/*:@public/dir', 'replace');

        $this->assertSame('./dir/*', $p->getSource());
        $this->assertSame('@public/dir', $p->getTarget());
        $this->assertSame(null, $p->getMode());
        $this->assertSame('replace', $p->getType());
    }

    public function testParseFile(): void
    {
        $p = Command::parse('.', 'dir/file.json:@public/dir/file.json', 'follow');

        $this->assertSame('./dir/file.json', $p->getSource());
        $this->assertSame('@public/dir/file.json', $p->getTarget());
        $this->assertSame(null, $p->getMode());
        $this->assertSame('follow', $p->getType());
    }

    public function testParseFileMode(): void
    {
        $p = Command::parse('.', 'dir/file.json:@public/dir/file.json', 'follow:readonly');

        $this->assertSame('./dir/file.json', $p->getSource());
        $this->assertSame('@public/dir/file.json', $p->getTarget());
        $this->assertSame('readonly', $p->getMode());
        $this->assertSame('follow', $p->getType());
    }

    public function testParseFileMode2(): void
    {
        $p = Command::parse('.', 'dir/file.json:@public/dir/file.json', 'follow:runtime');

        $this->assertSame('./dir/file.json', $p->getSource());
        $this->assertSame('@public/dir/file.json', $p->getTarget());
        $this->assertSame('runtime', $p->getMode());
        $this->assertSame('follow', $p->getType());
    }

    /**
     * @expectedException \Spiral\Composer\Exception\PublishException
     */
    public function testParseException(): void
    {
        $p = Command::parse('.', 'dir/file.json:@public/dir/file.json', 'xx:runtime');
    }

    /**
     * @expectedException \Spiral\Composer\Exception\PublishException
     */
    public function testParseException2(): void
    {
        $p = Command::parse('.', 'dir/file.json:@public/dir/file.json', 'follow:wrong');
    }
}
