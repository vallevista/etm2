<?php


namespace ETM;

use RedBeanPHP\R;
use Enpowi\App;

class Territory
{
  public $name;
  public $number;
  public $locality;
  public $congregation;

  //DO NOT populate geojson by default, it will make front end terribly slow
  public $geoJson;

  //dynamic from Records
  public $status;
  public $record;
  public $records;
  public $workedInYear;
  public $due;
  public static $secondsInMonth = 2592000;
  public static $secondsInYear = 60*60*24*365;
  public $idealReturnDate;

  public static $statusIn = 'in';
  public static $statusOut = 'out';
  public static $lastYear;

  private $_bean;

  public function __construct($number, $bean = null) {
    if (self::$lastYear === null) {
      self::$lastYear = time() - self::$secondsInYear;
    }

    $this->number = $number;
    $this->_bean = $bean;

    if ($bean === null) {
      $bean = R::findOne('territory', ' number = :number ', ['number' => $number]);
      if ($bean !== null) {
        $this->_bean = $bean;
        $this
            ->convertFromBean()
            ->updateDynamics();
      }
    } else {
      $this
          ->convertFromBean()
          ->updateDynamics();
    }
  }

  private function convertFromBean() {
    $bean = $this->_bean;
    $this->name = $bean->name;
    $this->number = $bean->number;
    $this->locality = $bean->locality;
    $this->congregation = $bean->congregation;

    return $this;
  }

  public function exists() {
    return $this->_bean !== null;
  }

  public function updateDynamics() {
    $latest = Record::latest($this->number)->bindPublisher();
    if ($latest === null) return $this;

    if ($latest->in === null) {
      $this->status = self::$statusOut;
    } else {
      $this->status = self::$statusIn;
      $this->workedInYear = ($latest->in < self::$lastYear);
    }
    $this->idealReturnDate = $latest->out + (self::$secondsInMonth * 4);
    $this->due = ($this->idealReturnDate <= time());
    $this->record = $latest;

    return $this;
  }

  public static function count() {
    return R::count('territory');
  }

  public static function pages($perPageCount = 5) {
    $count = self::count();
    $inPageCount = 0;
    $pages = [];
    $page = [];
    for ($i = 1; $i <= $count; $i++, $inPageCount++) {
      if ($inPageCount === $perPageCount) {
        $inPageCount = 0;
        $pages[] = $page;
        $page = [];
      }
      $page[] = $i;
    }
    $pages[] = $page;

    return $pages;
  }

  public static function page($pageNumber = 1, $count = 5) {
    $territories = [];

    $pageNumber = max(1, $pageNumber);

    //1 1
    //1 - 1 = 0 * 5 = 0 + 1 = 1

    //2 6
    //2 - 1 = 1 * 5 = 5 + 1 = 6

    //3 11
    //3 - 1 = 2 * 5 = 10 + 1 = 11

    $number = (($pageNumber - 1) * $count) + 1;
    $max = $number + $count;
    for (; $number < $max; $number++) {
      $territory = new Territory($number);
      if ($territory->exists()) {
        $territories[] = $territory->bindRecords();
      }
    }

    return $territories;
  }

  public static function all() {
    $beans = R::findAll('territory', ' order by number ');
    $territories = [];
    foreach ($beans as $bean) {
      $territories[] = new Territory($bean->number, $bean);
    }

    return $territories;
  }

  public static function workPriority($limit = 25) {
    $territories = array_filter(Territory::all(), function(Territory $t) {
      return $t->record->in !== null;
    });

    usort($territories, function (Territory $a, Territory $b) {
      return $a->record->in - $b->record->in;
    });

    if ($limit > 0) {
      $territories = array_slice($territories, 0, $limit);
    }

    return $territories;
  }

  public static function returnPriority() {

    $recordBeans = R::findAll('record', ' isnull(`in`) order by `out` ');
    $territories = [];
    foreach ($recordBeans as $recordBean) {
      $territories[] = new Territory($recordBean->number);
    }

    return $territories;
  }

  public function replace() {
    $user = App::user();
    $existingBeans = R::findAll('territory', ' number = :number ', ['number' => $this->number]);

    foreach ($existingBeans as $bean) {
      $copy = R::dispense('territoryedit');
      $copy->geoJson = $bean->geoJson;
      $copy->name = $bean->name;
      $copy->number = $bean->number;
      $copy->locality = $bean->locality;
      $copy->congregation = $bean->congregation;
      $copy->created = $bean->created;
      $copy->createdBy = $bean->createdBy;
      $copy->archived = time();
      $copy->archivedBy = $user->id;
      R::store($copy);
      R::trash($bean);
    }

    $bean = R::dispense('territory');
    $bean->geoJson = $this->geoJson;
    $bean->name = $this->name;
    $bean->number = $this->number;
    $bean->locality = $this->locality;
    $bean->congregation = $this->congregation;
    $bean->created = time();
    $bean->createdBy = $user->id;
    R::store($bean);

    $this->_bean = $bean;
    return $this;
  }

  public function bindGeoJson() {
    $this->geoJson = $this->_bean->geoJson;
    return $this;
  }

  public function bindRecords() {
    $records = [];
    foreach($this->_bean->sharedRecordList as $recordBean) {
      $records[] = (new Record($recordBean->number, $recordBean))->bindPublisher();
    }

    usort($records, function (Record $a, Record $b) {
      return $a->out - $b->out;
    });

    $this->records = $records;

    return $this;
  }

  public static function allGeoJson() {
    $features = [];

    $territories = Territory::all();

    foreach($territories as $territory) {
      $geoJson = json_decode($territory->bindGeoJson()->geoJson);
      foreach($geoJson->features as $feature) {
        $feature->properties = [
          'number' => $territory->number
        ];
        $features[] = $feature;
      }
    }

    return [
      "type" => "FeatureCollection",
      "features"=> $features
    ];
  }
}