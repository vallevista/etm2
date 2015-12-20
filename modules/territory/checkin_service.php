<?php
use Enpowi\App;
use Enpowi\Modules\Module;
Module::is();

echo ETM\Record::checkIn(App::param('number')) === null ? -1 : 1;