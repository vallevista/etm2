<?php
use Enpowi\App;
use Enpowi\Modules\Module;
use ETM\Publisher;
Module::is();

if (App::param('action') === 'merge') {
  $publisher = App::param('publisher');
  $publishers = App::params('publishers');
  if (Publisher::merge($publisher, $publishers) > 0) {
    echo 1;
  } else {
    echo 0;
  }
}