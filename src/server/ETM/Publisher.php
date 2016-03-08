<?php

namespace ETM;

use RedBeanPHP\R;

class Publisher
{
  public $name;
  public $email;
  public $phone;
  public $address1;
  public $address2;
  public $city;
  public $state;
  public $zip;
  public $records = [];

  private $_bean;

  public function __construct($name, $bean = null) {
    $this->name = $name;

    if ($bean === null) {
      $bean = R::findOne('publisher', 'name = :name', ['name' => $name]);
    }

    $this->_bean = $bean;

    $this->convertFromBean();
  }

  public static function fromBean($bean) {
    return new self($bean->name, $bean);
  }

  public static function fromName($name) {
    $publisher = new self($name);
    if ($publisher->_bean === null) {
      $publisher->save();
    }

    return $publisher;
  }

  private function convertFromBean() {
    $bean = $this->_bean;
    if ($bean === null) return $this;

    $this->name = $bean->name;
    $this->email = $bean->email;
    $this->phone = $bean->phone;
    $this->address1 = $bean->address1;
    $this->address2 = $bean->address2;
    $this->city = $bean->city;
    $this->state = $bean->state;
    $this->zip = $bean->zip;
    $this->records = [];

    return $this;
  }

  public function bindRecords() {
    $bean = $this->_bean;
    if ($bean === null) return $this;

    $records = [];
    foreach($bean->ownRecordList as $recordBean) {
      $records[] = new Record($recordBean->number, $recordBean);
    }

    usort($records, function(Record $a, Record $b) {
      return $a->out - $b->out;
    });

    $this->records = $records;

    return $this;
  }

  public function save() {
    $bean = $this->_bean ?: R::dispense('publisher');

    $bean->name = $this->name;
    $bean->email = $this->email;
    $bean->phone = $this->phone;
    $bean->address1 = $this->address1;
    $bean->address2 = $this->address2;
    $bean->city = $this->city;
    $bean->state = $this->state;
    $bean->zip = $this->zip;

    R::store($bean);
    $this->_bean = $bean;
    return $this;
  }

  public function bean() {
    return $this->_bean;
  }

  public static function searchByName($name, $limit = 10) {
    $beans = R::findAll('publisher', ' name like :name order by name limit :limit ', [
        'limit' => $limit,
        'name' => '%' . $name . '%'
    ]);

    $publishers = [];

    foreach($beans as $bean) {
      $publishers[] = Publisher::fromBean($bean);
    }

    return $publishers;
  }

  public static function all() {
    $beans = R::findAll('publisher', ' order by name ');
    $publishers = [];
    foreach ($beans as $bean) {
      $publishers[] = new Publisher($bean->name, $bean);
    }

    return $publishers;
  }

  public static function withoutRecords() {
  }

  public static function merge($name, Array $names) {
    $index = array_search($name, $names);
    if ($index > -1) {
       array_splice($names, $index, 1);
    }

    $into = (new self($name))->bean();
    $trash = [];
    $records = $into->ownRecordList ?: [];

    if ($into === null) {
      throw new \Exception('merge into publisher does not exist');
    }

    foreach($names as $_name) {
      $trash[] =
      $publisherBean = (new Publisher($_name))->bean();

      if ($publisherBean === null) {
        throw new \Exception('merge from publisher does not exist');
      }
      foreach($publisherBean->ownRecordList as $bean) {
        $records[] = $bean;
      }
      $publisherBean->ownRecordList = [];
    }

    $into->ownRecordList = $records;

    R::trashAll($trash);
    return R::store($into);
  }
}