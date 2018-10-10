<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Composer\Tests;

use PHPUnit\Framework\TestCase;
use Spiral\Composer\Publish;

class PublishTest extends TestCase
{
    public function testParseEnsure()
    {
        $p = Publish::parse(".", '@public/dir', 'ensure');

        $this->assertSame(null, $p->getSource());
        $this->assertSame('@public/dir', $p->getTarget());
        $this->assertSame(null, $p->getMode());
        $this->assertSame('ensure', $p->getType());
    }
}