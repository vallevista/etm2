<?php
use Enpowi\Modules\Module;
use Enpowi\Modules\DataIn;

Module::is();

$territory = (new DataIn)->in('territory');

$territory->replace();

echo 1;