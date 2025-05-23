<?php

declare(strict_types=1);

namespace tests\Instructions;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

include_once dirname(__DIR__, 2).'/Sop.php';

final class LoadTest extends TestCase
{
    #[DataProvider('loadProvider')]
    public function testLoad(int $registerIndex, int $value)
    {
        $sop = new \Sop();
        $sop->execute("LOAD $registerIndex $value 0");

        $this->assertSame($value, $sop->getRegister($registerIndex));
    }

    public static function loadProvider(): iterable
    {
        yield [0, 1];
        yield [1, 2];
        yield [2, 3];
        yield [3, 4];
        yield [4, 5];
        yield [5, 6];
        yield [6, 7];
        yield [7, 8];
    }
}
