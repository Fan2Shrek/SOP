<?php

declare(strict_types=1);

namespace tests\Instructions;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class SubTest extends TestCase
{
    #[DataProvider('subProvider')]
    public function testSubWithRegister(int $registerIndex, int $valueA, int $valueB)
    {
        $sop = new \Sop();
        $sop->execute("LOAD 0 $valueA 0");
        $sop->execute("LOAD 1 $valueB 0");

        $sop->execute("SUB 0 1 $registerIndex");

        $this->assertSame($valueA - $valueB, $sop->getRegister($registerIndex));
    }

    public static function subProvider(): iterable
    {
        yield [1, 0, 1];
        yield [2, 1, 2];
        yield [3, 2, 3];
        yield [4, 3, 4];
        yield [5, 4, 5];
        yield [6, 5, 6];
        yield [7, 6, 7];
    }
}
