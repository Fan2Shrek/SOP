<?php

class Sop
{
    private const RAW_LEFT  = 1 << 2;
    private const RAW_RIGHT = 1 << 3;

    private const INSTRUCTION_NO_OP = 0b0000;
    private const INSTRUCTION_ADD   = 0b0001;
    private const INSTRUCTION_LOAD  = 0b0010;

    private const MNEMONICS = [
        'NOP'      => self::INSTRUCTION_NO_OP,
        'ADD'      => self::INSTRUCTION_ADD,
        'ADD_1'    => self::INSTRUCTION_ADD | self::RAW_LEFT,
        'ADD_2'    => self::INSTRUCTION_ADD | self::RAW_RIGHT,
        'ADDi'     => self::INSTRUCTION_ADD | self::RAW_LEFT | self::RAW_RIGHT,
        'LOAD'     => self::INSTRUCTION_LOAD | self::RAW_LEFT | self::RAW_RIGHT,
    ];

    private array $registers;
    private int $pc;

    public function __construct(
        int $registers = 8,
    )
    {
        $this->registers = array_fill(0, $registers, 0);
        $this->pc = 0;
    }

    /**
     * Process a SOP code
     */
    public function process(string $code): void
    {
        if (\is_file($code) && is_readable($code)) {
            $code = file_get_contents($code);
        }

        $program = [];
        $i = 0;
        foreach (explode("\n", $code) as $line) {
            if (empty($line) || $line[0] === '#') {
                continue;
            }

            $program[$i] = trim($line);
            $i +=4;
        }

        $this->pc = 0;
        foreach ($program as $line) {
            $this->execute($line);
            $this->pc += 4;
        }
    }

    /**
     * Execute a SOP instruction
     *
     * maybe will return int for flags
     */
    public function execute(string $code): void
    {
        if (!preg_match('/^(\w+)\s+(\d+)\s+(\d+)\s+(\d+)$/', $code, $matches)) {
            throw new \InvalidArgumentException('Invalid instruction format');
        }

        list($full, $instruction, $arg1, $arg2, $arg3) = $matches;

        if (null === $opCode = self::MNEMONICS[$instruction] ?? null) {
            throw new \InvalidArgumentException('Invalid instruction');
        }

        if (!($opCode & self::RAW_LEFT)) {
            $arg1 = $this->registers[$arg1];
        } else {
            $opCode &= ~self::RAW_LEFT;
        }

        if (!($opCode & self::RAW_RIGHT)) {
            $arg2 = $this->registers[$arg2];
        } else {
            $opCode &= ~self::RAW_RIGHT;
        }

        $this->doInstruction($opCode, $arg1, $arg2, $arg3);

        return;
    }

    public function exportRegisters(): array
    {
        return $this->registers;
    }

    public function getRegister(int $index): int
    {
        return $this->registers[$index];
    }

    public function jumpTo(int $address): void
    {
        $this->pc = $address;
    }

    private function doInstruction(int $opCode, int $arg1, int $arg2, int $arg3): int
    {
        switch ($opCode) {
            case self::INSTRUCTION_NO_OP:
                break;
            case self::INSTRUCTION_ADD:
                $this->registers[$arg3] = $arg1 + $arg2;
                break;

            case self::INSTRUCTION_LOAD:
                $this->registers[$arg1] = $arg2;
                break;

            default:
                throw new \InvalidArgumentException('Invalid instruction');
        }

        return 0;
    }
}
