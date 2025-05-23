<?php

class Sop
{
    private const RAW_LEFT  = 1 << 6;
    private const RAW_RIGHT = 1 << 7;

    private const INSTRUCTION_NO_OP = 0b00000000;
    private const INSTRUCTION_ADD   = 0b00000001;
    private const INSTRUCTION_SUB   = 0b00000010;
    private const INSTRUCTION_LOAD  = 0b00000101;
    private const INSTRUCTION_HALT  = 0b00000111;

    private const INSTRUCTION_JUMP  = 0b00001000;
    private const INSTRUCTION_JEQZ  = 0b00001001;
    private const INSTRUCTION_JEQ   = 0b00001010;

    private const MNEMONICS = [
        'NOP'      => self::INSTRUCTION_NO_OP,
        'ADD'      => self::INSTRUCTION_ADD,
        'ADD_1'    => self::INSTRUCTION_ADD  | self::RAW_LEFT,
        'ADD_2'    => self::INSTRUCTION_ADD  | self::RAW_RIGHT,
        'ADDi'     => self::INSTRUCTION_ADD  | self::RAW_LEFT | self::RAW_RIGHT,
        'SUB'      => self::INSTRUCTION_SUB,
        'SUB_1'    => self::INSTRUCTION_SUB  | self::RAW_LEFT,
        'SUB_2'    => self::INSTRUCTION_SUB  | self::RAW_RIGHT,
        'SUBi'     => self::INSTRUCTION_SUB  | self::RAW_LEFT | self::RAW_RIGHT,
        'LOAD'     => self::INSTRUCTION_LOAD | self::RAW_LEFT | self::RAW_RIGHT,
        'LOAD_2'   => self::INSTRUCTION_LOAD | self::RAW_LEFT,
        'HALT'     => self::INSTRUCTION_HALT,
        'JMP'      => self::INSTRUCTION_JUMP | self::RAW_LEFT,
        'JEQZ'     => self::INSTRUCTION_JEQZ | self::RAW_LEFT,
        'JEQ'      => self::INSTRUCTION_JEQ  | self::RAW_LEFT | self::RAW_RIGHT,
    ];

    private array $registers;
    private int $inputRegister;
    private int $outputRegister;
    private int $pc;

    private bool $isDebug = false;

    public function __construct(
        int $registers = 8,
    )
    {
        $this->registers = array_fill(0, $registers, 0);
        $this->inputRegister = $registers;
        $this->outputRegister = $registers + 1;
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
        while ($this->pc >= 0 && $this->pc < count($program) * 4) {
            $oldPc = $this->pc;
            $this->execute($program[$this->pc]);

            // If jump
            if ($oldPc === $this->pc) {
                $this->pc += 4;
            }
        }
    }

    /**
     * Execute a SOP instruction
     *
     * maybe will return int for flags
     */
    public function execute(string $code): void
    {
        if (!preg_match('/^(\w+)(?:\s+(\d+))?(?:\s+(\d+))?(?:\s+(\d+))?\s*(?:\/\/.*)?$/', $code, $matches)) {
            throw new \InvalidArgumentException(\sprintf('Invalid instruction format "%s"', $code));
        }

        $instruction = $matches[1];
        $arg1 = $matches[2] ?? 0;
        $arg2 = $matches[3] ?? 0;
        $arg3 = $matches[4] ?? 0;

        if ($this->isDebug) {
            echo "Executing: $matches[0]\n";
        }

        if (null === $opCode = self::MNEMONICS[$instruction] ?? null) {
            throw new \InvalidArgumentException(\sprintf('Invalid instruction "%s"', $instruction));
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

    public function enableDebug(): void
    {
        $this->isDebug = true;
    }

    private function doInstruction(int $opCode, int $arg1, int $arg2, int $arg3): int
    {
        switch ($opCode) {
            case self::INSTRUCTION_NO_OP:
                break;

            ## ALU related
            case self::INSTRUCTION_ADD:
                $this->setRegister($arg3, $arg1 + $arg2);
                break;
            case self::INSTRUCTION_SUB:
                $this->setRegister($arg3, $arg1 - $arg2);
                break;

            case self::INSTRUCTION_LOAD:
                $this->setRegister($arg1, $arg2);
                break;

            case self::INSTRUCTION_JUMP:
                $this->jumpTo($arg1);
                break;
            case self::INSTRUCTION_JEQ:
                if ($arg2 === $this->registers[$arg3]) {
                    $this->jumpTo($arg1);
                }
                break;

            case self::INSTRUCTION_HALT:
                $this->jumpTo(-999);
                break;

            default:
                throw new \InvalidArgumentException(\sprintf('Unhandled instruction "%d"', $opCode));
        }

        return 0;
    }

    private function setRegister(int $index, int $value): void
    {
        if ($this->outputRegister === $index) {
            echo "Output: $value\n";

            return;
        }

        if (!isset($this->registers[$index])) {
            throw new \InvalidArgumentException(\sprintf('Invalid register "%d"', $index));
        }

        $this->registers[$index] = $value;
    }
}
