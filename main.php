<?php

include './Sop.php';

$sop = new Sop();
$sop->execute('LOAD 5 6 0');
$sop->execute('LOAD 0 10 0');

$sop->execute('ADD 5 0 2');

echo "5 + 10 = ".$sop->getRegister(2).PHP_EOL;
