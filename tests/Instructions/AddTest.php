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
        $sop->execute("LOAD 0 $valueA 0");
        $sop->execute("LOAD 1 $valueB 0");

        $sop->execute("ADD 0 1 $registerIndex");

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

    public function testAddImediate1()
    {
        $sop = new \Sop();
        $sop->execute("LOAD 5 16 0");

        $sop->execute("ADD_1 10 5 3");

        $this->assertSame(26, $sop->getRegister(3));
    }

    public function testAddImediate2()
    {
        $sop = new \Sop();
        $sop->execute("LOAD 5 16 0");

        $sop->execute("ADD_2 5 10 3");

        $this->assertSame(26, $sop->getRegister(3));
    }

    public function testAddImediate()
    {
        $sop = new \Sop();
        $sop->execute("ADDi 10 5 3");

        $this->assertSame(15, $sop->getRegister(3));
    }
}
