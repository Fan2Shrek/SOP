<?php

include './Sop.php';

$code = <<<CODE
LOAD 5 6 0
LOAD 0 10 0

# ADD
ADD 5 0 5
CODE;


$sop = new Sop();

$sop->process($code);

echo "5 + 10 = ".$sop->getRegister(2).PHP_EOL;
