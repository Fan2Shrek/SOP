<?php

include './Sop.php';

if (1 === $argc) {
    $code = <<<CODE
LOAD 5 6 0
LOAD 0 10 0

# ADD
ADD 5 0 2

SUB_2 2 5 2

JMP 24 0 0

# SKIP
FAKE 0 0 0

HALT 0 0 0
CODE;
} else {
    $code = file_get_contents($argv[1]);
}

$sop = new Sop();

$sop->process($code);

foreach ($sop->exportRegisters() as $index => $value) {
    echo "R$index: $value\n";
}
