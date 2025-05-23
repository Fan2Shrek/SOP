<?php

class Sop
{
    private const RAW_LEFT  = 1 << 6;
    private const RAW_RIGHT = 1 << 7;

    private const INSTRUCTION_NO_OP   = 0b00000000;
    private const INSTRUCTION_ADD     = 0b00000001;
    private const INSTRUCTION_SUB     = 0b00000010;
    private const INSTRUCTION_DIV     = 0b00000011;
    private const INSTRUCTION_LOAD    = 0b00000101;
    private const INSTRUCTION_HALT    = 0b00000111;

    private const INSTRUCTION_JUMP    = 0b00001000;
    private const INSTRUCTION_JEQZ    = 0b00001001;
    private const INSTRUCTION_JEQ     = 0b00001010;

    private const INTRUCTION_INCHAR   = 0b00001100;
    private const INTRUCTION_OUTCHAR  = 0b00001101;

    private const INSTRUCTION_LOADM   = 0b00001110;
    private const INSTRUCTION_STOREM  = 0b00001111;

    private const MNEMONICS = [
        'NOP'      => self::INSTRUCTION_NO_OP,
        'ADD'      => self::INSTRUCTION_ADD,
        'SUB'      => self::INSTRUCTION_SUB,
        'DIV'      => self::INSTRUCTION_DIV,
        'LOAD'     => self::INSTRUCTION_LOAD | self::RAW_LEFT,
        'HALT'     => self::INSTRUCTION_HALT,
        'JMP'      => self::INSTRUCTION_JUMP | self::RAW_LEFT,
        'JEQZ'     => self::INSTRUCTION_JEQZ | self::RAW_LEFT,
        'JEQ'      => self::INSTRUCTION_JEQ  | self::RAW_LEFT | self::RAW_RIGHT,
        'INCHAR'   => self::INTRUCTION_INCHAR | self::RAW_LEFT,
        'OUTCHAR'  => self::INTRUCTION_OUTCHAR,
        'LOADM'    => self::INSTRUCTION_LOADM | self::RAW_LEFT,
        'STOREM'   => self::INSTRUCTION_STOREM,
    ];

    private array $registers;
    private int $inputRegister;
    private int $outputRegister;
    private int $pc;

    private bool $isDebug = false;
    private Memory $memory;

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
        if (!preg_match('/^(\w+)(?:\s+(r?\d+))?(?:\s+(r?\d+))?(?:\s+(r?\d+))?\s*(?:\/\/.*)?$/', $code, $matches)) {
            throw new \InvalidArgumentException(\sprintf('Invalid instruction format "%s"', $code));
        }

        $instruction = $matches[1];
        $arg1 = trim($matches[2] ?? 0);
        $arg2 = trim($matches[3] ?? 0);
        $arg3 = trim($matches[4] ?? 0);

        if ($this->isDebug) {
            echo "Executing: $matches[0]\n";
        }

        if (null === $opCode = self::MNEMONICS[$instruction] ?? null) {
            throw new \InvalidArgumentException(\sprintf('Invalid instruction "%s"', $instruction));
        }

        if (!str_starts_with($arg1, 'r')) {
            $opCode |= self::RAW_LEFT;
        } else {
            $arg1 = (int) substr($arg1, 1);
        }

        if (!str_starts_with($arg2, 'r')) {
            $opCode |= self::RAW_RIGHT;
        } else {
            $arg2 = (int) substr($arg2, 1);
        }

        if (str_starts_with($arg3, 'r')) {
            $arg3 = (int) substr($arg3, 1);
        }

        if (!($opCode & self::RAW_LEFT)) {
            $arg1 = $this->getRegister($arg1);
        } else {
            $opCode &= ~self::RAW_LEFT;
        }

        if (!($opCode & self::RAW_RIGHT)) {
            $arg2 = $this->getRegister($arg2);
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
        if ($this->inputRegister === $index) {
            return ord(fgetc(STDIN));
        }

        if (!isset($this->registers[$index])) {
            throw new \InvalidArgumentException(\sprintf('Invalid register "%d"', $index));
        }

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

    public function addMemory(Memory $memory): void
    {
        $this->memory = $memory;
    }

    private function doInstruction(int $opCode, int $arg1, int $arg2, int $arg3): int
    {
        if ($this->isDebug) {
            echo "Executing instruction: $opCode - arg1: $arg1 - arg2: $arg2 - arg3: $arg3\n";
        }

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
            case self::INSTRUCTION_DIV:
                $this->setRegister($arg3, intdiv($arg1, $arg2));
                break;

            case self::INSTRUCTION_LOAD:
                $this->setRegister($arg1, $arg2);
                break;
            case self::INTRUCTION_INCHAR:
                shell_exec('stty -icanon -echo');
                $char = fgetc(STDIN);
                shell_exec('stty icanon echo');
                if ($char === false) {
                    $char = "\0";
                }

                $this->setRegister($arg1, ord($char));
                break;
            case self::INTRUCTION_OUTCHAR:
                $this->out(chr($arg1));
                break;
            case self::INSTRUCTION_LOADM:
                if ($this->isDebug) {
                    echo "Loading value from memory address $arg2 into register $arg1\n";
                }
                $this->setRegister($arg1, $this->memory->get($arg2));
                break;
            case self::INSTRUCTION_STOREM:
                if ($this->isDebug) {
                    echo "Storing value $arg1 in memory address $arg2\n";
                }
                $this->memory->set($arg2, $arg1);
                break;

            case self::INSTRUCTION_JUMP:
                $this->jumpTo($arg1);
                break;
            case self::INSTRUCTION_JEQ:
                if ($arg2 === $this->getRegister($arg3)) {
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
            $this->out($value);

            return;
        }

        if (!isset($this->registers[$index])) {
            throw new \InvalidArgumentException(\sprintf('Invalid register "%d"', $index));
        }

        $this->registers[$index] = $value;
    }

    private function out(int|string $value): void
    {
        if ($this->isDebug) {
            echo "Received value on output register:\n";
        }

        echo $value;
    }
}

class Memory
{
    private array $memory = [];

    public function __construct(
        int $size = 1024,
    )
    {
        $this->memory = array_fill(0, $size, 0);
    }

    public function get(int $address): int
    {
        if (!isset($this->memory[$address])) {
            throw new \InvalidArgumentException(\sprintf('Invalid memory address "%d"', $address));
        }

        return $this->memory[$address];
    }

    public function set(int $address, int $value): void
    {
        if (!isset($this->memory[$address])) {
            throw new \InvalidArgumentException(\sprintf('Invalid memory address "%d"', $address));
        }

        $this->memory[$address] = $value;
    }

    public function export(): array
    {
        return $this->memory;
    }
}
