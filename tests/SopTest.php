<?php

declare(strict_types=1);

namespace tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

include_once dirname(__DIR__).'/Sop.php';

final class SopTest extends TestCase
{
    #[DataProvider('registersProvider')]
    public function testRegisterCount(int $registers): void
    {
        $sop = new \Sop($registers);
        $this->assertCount($registers, $sop->exportRegisters());
    }

    public static function registersProvider(): iterable
    {
        yield [2];
        yield [16];
    }

    public function testExecuteUnknownMnemonic(): void
    {
        $sop = new \Sop();
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid instruction');
        $sop->execute('SOP 5 6 5');
    }
}
