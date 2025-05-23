<?php

declare(strict_types=1);

namespace tests\Instructions;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class AddTest extends TestCase
{
    #[DataProvider('addProvider')]
    public function testAddWithRegister(int $registerIndex, int $valueA, int $valueB)
    {
        $sop = new \Sop();
        $sop->execute("LOAD r0 $valueA");
        $sop->execute("LOAD r1 $valueB");

        $sop->execute("ADD r0 r1 r$registerIndex");

        $this->assertSame($valueA + $valueB, $sop->getRegister($registerIndex));
    }

    public static function addProvider(): iterable
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
