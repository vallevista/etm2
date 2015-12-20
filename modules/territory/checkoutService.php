<?php
use ETM;
use Enpowi\App;
use Enpowi\Modules\Module;
Module::is();

$number = App::param('number');
$publisher = ETM\Publisher::fromName(App::param('publisherName'));

echo ETM\Record::checkOut($number, $publisher) === null ? -1 : 1;