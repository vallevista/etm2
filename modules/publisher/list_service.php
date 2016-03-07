<?php
use Enpowi\App;
use Enpowi\Modules\Module;
use Enpowi\Modules\DataIn;
Module::is();

if (App::param('action') === 'merge') {
  $publisher = App::param('publisher');
  $publishers = App::params('publishers');
  print_r($publisher);
  print_r($publishers);
}