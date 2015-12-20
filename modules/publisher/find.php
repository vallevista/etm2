<?php
use Enpowi\App;
use Enpowi\Modules\Module;

Module::is();

$publishers = ETM\Publisher::searchByName(App::param('query'));

$names = [];

foreach ($publishers as $publisher) {
  $names[] = $publisher->name;
}

echo json_encode($names);