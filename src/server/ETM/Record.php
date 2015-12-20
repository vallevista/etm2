<?php


namespace ETM;

use RedBeanPHP\R;
use Enpowi\App;

class Record
{
  public $number;
  public $publisher;
  public $out;
  public $in;

  private $_bean;

  public function __construct($number, $bean = null) {
    $this->number = $number;
    $this->_bean = $bean;

    if ($bean !== null) {
      $this->convertFromBean();
    }
  }

  private function convertFromBean() {
    $bean = $this->_bean;
    if ($bean === null) return $this;

    $this->number = $bean->number;
    $this->out = $bean->out;
    $this->in = $bean->in;

    return $this;
  }

  public function bindPublisher() {
    $bean = $this->_bean;
    if ($bean === null) return $this;

    $this->publisher = Publisher::fromBean($bean->publisher);

    return $this;
  }

  public static function latest($number) {
    return new Record($number, R::findOne('record', ' number = :number order by `out` desc ', [ 'number' => $number]));
  }

  public static function checkOut($number, Publisher $publisher) {
    if (R::count('record', ' number = :number AND isnull(`in`) ', [ 'number' => $number]) > 0) return null;

    $territoryBean = R::findOne('territory', ' number = :number ', ['number' => $number]);

    if ($territoryBean === null) return null;

    $bean = R::dispense('record');
    $bean->number = $number;
    $bean->out = time();
    $bean->in = null;
    $bean->checkOutBy = App::user()->id;
    $record = new Record($number, $bean);

    $territoryBean->sharedRecordList[] = $bean;
    $publisherBean = $publisher->bean();
    $publisherBean->ownRecordList[] = $bean;

    R::storeAll([$bean, $territoryBean, $publisherBean]);

    return $record;
  }

  public static function checkIn($number) {
    $bean = R::findOne('record', ' number = :number AND isnull(`in`) ', [ 'number' => $number]);
    if ($bean === null) return null;

    $bean->in = time();
    $bean->checkInBy = App::user()->id;
    $record = new Record($number, $bean);

    R::store($bean);

    return $record;
  }

  public static function history($number) {
    $recordBeans = R::findAll('record', ' number = :number order by `out` ', [ 'number' => $number]);
    $records = [];

    foreach ($recordBeans as $bean) {
      $records[] = (new Record($number, $bean))->bindPublisher();
    }

    return $records;
  }
}