<?php

include './Sop.php';

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

$sop = new Sop();

$sop->process($code);

echo "6 + 10 - 5 = ".$sop->getRegister(2).PHP_EOL;
