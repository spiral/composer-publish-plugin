<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Composer\Tests;

use PHPUnit\Framework\TestCase;
use Spiral\Composer\Command;

class CommandTest extends TestCase
{
    public function testParseEnsure()
    {
        $p = Command::parse(".", '@public/dir', 'ensure');

        $this->assertSame(null, $p->getSource());
        $this->assertSame('@public/dir', $p->getTarget());
        $this->assertSame(null, $p->getMode());
        $this->assertSame('ensure', $p->getType());
    }

    public function testParseDirectory()
    {
        $p = Command::parse(".", 'dir:@public/dir', 'replace');

        $this->assertSame('./dir', $p->getSource());
        $this->assertSame('@public/dir', $p->getTarget());
        $this->assertSame(null, $p->getMode());
        $this->assertSame('replace', $p->getType());
    }

    public function testParseDirectoryStar()
    {
        $p = Command::parse(".", 'dir/*:@public/dir', 'replace');

        $this->assertSame('./dir/*', $p->getSource());
        $this->assertSame('@public/dir', $p->getTarget());
        $this->assertSame(null, $p->getMode());
        $this->assertSame('replace', $p->getType());
    }

    public function testParseFile()
    {
        $p = Command::parse(".", 'dir/file.json:@public/dir/file.json', 'follow');

        $this->assertSame('./dir/file.json', $p->getSource());
        $this->assertSame('@public/dir/file.json', $p->getTarget());
        $this->assertSame(null, $p->getMode());
        $this->assertSame('follow', $p->getType());
    }

    public function testParseFileMode()
    {
        $p = Command::parse(".", 'dir/file.json:@public/dir/file.json', 'follow:readonly');

        $this->assertSame('./dir/file.json', $p->getSource());
        $this->assertSame('@public/dir/file.json', $p->getTarget());
        $this->assertSame('readonly', $p->getMode());
        $this->assertSame('follow', $p->getType());
    }

    public function testParseFileMode2()
    {
        $p = Command::parse(".", 'dir/file.json:@public/dir/file.json', 'follow:runtime');

        $this->assertSame('./dir/file.json', $p->getSource());
        $this->assertSame('@public/dir/file.json', $p->getTarget());
        $this->assertSame('runtime', $p->getMode());
        $this->assertSame('follow', $p->getType());
    }

    /**
     * @expectedException \Spiral\Composer\Exception\PublishException
     */
    public function testParseException()
    {
        $p = Command::parse(".", 'dir/file.json:@public/dir/file.json', 'xx:runtime');
    }

    /**
     * @expectedException \Spiral\Composer\Exception\PublishException
     */
    public function testParseException2()
    {
        $p = Command::parse(".", 'dir/file.json:@public/dir/file.json', 'follow:wrong');
    }
}
