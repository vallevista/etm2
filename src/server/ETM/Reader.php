<?php
namespace ETM;

class Reader
{
  public $secure = false;
  public $store = [];
  public $storeByLocality = [];
  public $storeByName = [];

  public $territoryString;

  /**
   * @var \SimpleXMLElement
   */
  public $territoryXML;

  /**
   * @var \SimpleXmlElement
   */
  public $worksheetXml;
  /**
   * @var \SimpleXMLElement[]
   */
  public $territoryActivityXML = [];

  /**
   * @var \SimpleXMLElement
   */
  public $dncXML;

  /**
   * @var TerritoryCollection[]
   */
  public $territoryCollections = [];

  /**
   * @var int
   */
  public $territoryCount = 0;

  /**
   * @var Territory[]
   */
  public $territoriesOut = [];

  public $territoryReturnDates = [];

  /**
   * @var Territory[]
   */
  public $territoryList = [];

  /**
   * @var Territory[]
   */
  public $needReworked = [];

  /**
   * @var Config
   */
  public static $config = null;

  public static function setConfig(Config $config) {
    self::$config = $config;
  }

  function __construct($secure = false) {
    if (self::$config === null) {
      throw new \Exception('No configuration found.  Run `Reader::setConfig(\ETM\Config $config)` first.');
    }

    $this->secure = $secure;

    $kmlFileLocation = path . self::$config->territoryPath;

    //Throw helpful error if territory.kml doesn't exist
    if (!file_exists($kmlFileLocation)) {
      throw new \Exception("The 'territory.kml' file, created with Google Earth, does not exist in '$kmlFileLocation' folder.  Please save it there, and continue.");
    }

    //get territory.kml file, and read it to xml, and setup kml namespace
    $this->territoryString = file_get_contents($kmlFileLocation);
    $territoryXML = simplexml_load_string($this->territoryString);
    $territoryXML->registerXPathNamespace('kml', 'http://earth.google.com/kml/2.2');
    $this->territoryXML = $territoryXML;

    //get google.spreadsheet.key, a spreadsheet, for use with tracking changes with territory over time

    $key = self::$config->googleSpreadsheetKey;
    if (empty($key)) {
      throw new \Exception('The googleSpreadsheetKey is not set in your config file.  Please insert key and continue.');
    }
    $worksheetUrl = 'https://spreadsheets.google.com/feeds/worksheets/' . $key . '/public/values';
    $this->worksheetXml = GoogleSheetUtilities::fromUrl($worksheetUrl);
    $this->openWorksheet();

    //Search through the xml at Document.Folder, or Document.Placemark
    $this->store = $this->territoryXML->xpath(<<<XPATH
//kml:Document
    /kml:Folder
|//kml:Document
    /kml:Placemark
XPATH
    );

    $this
        ->readKML()
        ->readActivity()
        ->readDNCs();
  }

  public function sortCollections() {
    foreach($this->territoryCollections as $territoryCollection) {
      $territoryCollection->sort();
    }
    return $this;
  }

  public function collectionRange($at, $max) {
    $range = [];
    $end = $at + 4;
    for($i = $at; $i <= $end && $i <= $max; $i++) {
      if (empty($this->territoryCollections[$i])) continue;
      $territoryCollection = $this->territoryCollections[$i];
      $territoryCollection->sort();
      $range[] = $territoryCollection;
    }

    return $range;
  }

  private function readKML() {
    foreach($this->store as $data) {
      if (isset($data->Placemark)) {
        foreach($data->Placemark as $territory) {
          $name = $territory->name . '';
          $territory->locality = $data->name . '';
          $this->storeByName[$name] = $territory;
        }
      }
    }

    return $this;
  }

  private function openWorksheet() {
    $worksheet = $this->worksheetXml;
    $title = null;

    foreach ($worksheet->entry as $spreadsheetReference) {
      $title = $spreadsheetReference->title . '';

      //first is xml list feed
      $url = $spreadsheetReference->link[0]['href'] . '';

      if ($title === self::$config->activityTitle) {
        $this->territoryActivityXML[] = GoogleSheetUtilities::fromUrl($url);
      }

      else if ($title === self::$config->activityArchiveTitle) {
        $this->territoryActivityXML[] = GoogleSheetUtilities::fromUrl($url);
      }

      else if ($title === self::$config->dncTitle) {
        $this->dncXML = GoogleSheetUtilities::fromUrl($url);
      }
    }

    return $this;
  }

  private function readActivity() {
    $territoryListRaw = [];
    foreach ($this->territoryActivityXML as $xml) {
      foreach ($xml->entry as $child) {
        $row = $child->children('gsx', TRUE);
        $territoryName = $row->territory . '';
        if (empty($territoryName)) continue;
        $locality = $this->storeByName[$territoryName]->locality . '';

        if (empty($this->territoryCollections[$territoryName])) {
          $this->territoryCollections[$territoryName] = new TerritoryCollection();
          $this->territoryCount++;
        }

        $territory = new OTerritory($row, $locality);
        $this->territoryCollections[$territoryName]->add($territory);
        if (empty($territoryListRaw[$territoryName])) {
          $territoryListRaw[$territoryName] = $territory;
        } else {
          if ($territoryListRaw[$territoryName]->out < $territory->out) {
            $territoryListRaw[$territoryName] = $territory;
          }
        }

        if (empty($territory->in)) {
          $this->territoryCollections[$territoryName]->out = true;
          $this->territoriesOut[$territoryName] = $territory;
        }
      }
    }

    $territoryList = [];
    ksort($territoryListRaw);
    foreach ($territoryListRaw as $territory) {
      $territoryList[] = $territory;
    }
    //get entire territory list of current activity
    $this->territoryList = $territoryList;

    //get the territories that are in that need worked, sorted by date earliest date
    $needReworkedRaw = array_merge([], $territoryList);
    usort($needReworkedRaw, function($a, $b) {
      return $a->in > $b->in;
    });

    //now push
    $needReworked = [];
    foreach($needReworkedRaw as $territory) {
      if ($territory->status === Territory::$statusOut) continue;
      $needReworked[] = $territory;
    }
    $this->needReworked = $needReworked;

    return $this;
  }

  private function readDNCs() {
return;
    foreach ($this->dncXML->entry as $child) {
      $row = $child->children('gsx', TRUE);

      $territoryName = $row->territory . '';
      if (isset($this->territoryCollections[$territoryName])) {
        $this->territoryCollections[$territoryName]->dnc[] = [
            'address'=>$row->address . '',
            'name'=>$row->name . '',
            'date'=>$row->date . '',
            'territory'=>$row->date . ''
        ];
      }
    }

    return $this;
  }

  /**
   * @return \SimpleXMLElement[]
   */
  public function all() {
    return $this->store;
  }

  /**
   * @param string $territory
   * @param string $locality
   * @return \SimpleXMLElement[]
   */
  public function lookupKml($territory, $locality = null) {
    $territoryKML = null;
    //If locality is set (ie Folder), use folder, otherwise, use Placemark
    if (empty($locality)) {
      //try first for  Placemark, then Folder
      $territoryKML = $this->territoryXML->xpath(<<<XPATH
//kml:Document
    /kml:Placemark[kml:name/text()='$territory']
XPATH
      );

      if (empty($territoryKML)) {
        $territoryKML = $this->territoryXML->xpath(<<<XPATH
//kml:Document
    /kml:Folder
        /kml:Placemark[kml:name/text()='$territory']
XPATH
        );
      }
    } else {
      $territoryKML = $this->territoryXML->xpath(<<<XPATH
//kml:Document
    /kml:Folder[kml:name/text()='$locality']
        /kml:Placemark[kml:name/text()='$territory']
XPATH
      );
    }

    if (empty($territoryKML)) {
      return null;
    }

    return $territoryKML;
  }

  /**
   * @param $territory
   * @param $locality
   * @return null|Territory
   */
  public function lookup($territory, $locality = null) {
    $kml = $this->lookupKml($territory, $locality);

    if ($kml == null) {
      return null;
    }

    $locality = $kml[0]->xpath("..");

    $root = $locality[0]->xpath("..");

    require_once(path . '/src/server/ETM/OTerritory.php');

    if (isset($root[0]->Document)) {
      $foundTerritory = new OTerritory();
      $foundTerritory->name = $kml[0]->name . '';
      $foundTerritory->congregation = $locality[0]->name . '';
    }

    else {
      $foundTerritory = new OTerritory();
      $foundTerritory->name = $kml[0]->name . '';
      $foundTerritory->locality = $locality[0]->name . '';
      $foundTerritory->congregation = $root[0]->name . '';
    }

    if ($this->secure) {
      if (isset($this->territoryCollections[$foundTerritory->name])) {
        $preSecureTerritoryCollection = $this->territoryCollections[$foundTerritory->name];
        $preSecureTerritory = $preSecureTerritoryCollection->mostRecent();
        $publisherNameParts = explode(" ", $preSecureTerritory->publisher);
        $initials = '';
        foreach($publisherNameParts as $part) {
          $initials .= $part{0};
        }

        $attemptedInitials = strtolower(preg_replace("/[^A-Za-z0-9 ]/", '', $_REQUEST['initials']));
        $actualInitials = strtolower(preg_replace("/[^A-Za-z0-9 ]/", '', $initials));
        if (
            !empty($attemptedInitials)
            && $attemptedInitials ===  $actualInitials
        ) {
          //SUCCESS!
          session_start();
          $_SESSION['viewFolder'] = true;
          return $foundTerritory;
        } else {
          //Failed attempt
          session_destroy();
          return null;
        }
      }
    }

    return $foundTerritory;
  }

  /**
   * @param $territory
   * @param $locality
   * @return string
   */
  public function getSingleKml($territory, $locality) {
    $result = '';
    $territoryItems = $this->lookupKml($territory, $locality);

    //list folders if is set
    if (empty($territoryItems) == false) {
      foreach($territoryItems as $territoryItem) {
        if (empty($territoryItems->Placemark)) {
          $territoryItem->styleUrl = '#standardStyle';
        } else {
          foreach($territoryItem->Placemark as $placemark) {
            $placemark->styleUrl = '#standardStyle';
          }
        }
        $result = $territoryItem->asXML();
      }
    }

    return $result;
  }


  /**
   * @param $territory
   * @return null|Territory
   *
   */
  public function getSingleStatus($territory) {
    $activity = null;
    $mostRecentActivity = null;
    if (array_key_exists($territory, $this->territoryCollections)) {
      $activity = $this->territoryCollections[$territory];
    }

    if ($activity != null) {
      if ($activity->out) {
        $mostRecentActivity = $activity->mostRecent();
      }
    }

    return $mostRecentActivity;
  }

  public function sort() {
    usort($this->territoriesOut, function (Territory $a, Territory $b) {
      return $a->out - $b->out;
    });

    return $this;
  }

  /**
   * @return Territory[]
   */
  public function getPriority() {
    $territories = [];
    foreach ($this->territoryCollections as $territoryCollection) {
      $territory = $territoryCollection->mostRecent();
      if (!empty($territory->in)) {
        $territories[$territory->territory] = $territory;
      }
    }

    usort($territories, function (Territory $a, Territory $b) {
      return $a->in - $b->in;
    });

    return $territories;
  }

  public function lastTerritoryName() {

    //First look up folder structure Document / Folder / Placemark
    $last = $this->territoryXML->xpath(<<<XPATH
//kml:Document
    /kml:Folder[last()]
XPATH
    );
    if (!empty($last[0]->Placemark)) {
      $name = $last[0]->Placemark->name . '';
      return $name;
    }


    //If the above structure doesn't exist, look up folder structure Document / Placemark
    else {
      foreach($this->territoryActivityXML as $xml) {
        $last = $xml->xpath(<<<XPATH
//kml:Document
    /kml:Placemark[last()]
XPATH
        );
        if (!empty($last[0])) {
          $name = $last[0]->name . '';
          return $name;
        }
      }
    }

    //If all fails, return empty string.
    return '';
  }
// 0 * 5 = 0 + 5 = 5
// 1 * 5 = 5 + 5 = 10
// 2 * 5 = 10

// 1 * 5 = 5 + 5 = 10
// 2 * 5 = 10 + 5 = 15
  public function assignmentRecordsList($increments = 5) {
    $list = [];
    $max = ceil($this->territoryCount / $increments);
    for ($i = 0; $i < $max; $i++) {
      $begin = $i * $increments;
      $list[] = [
        'begin'=>$begin,
        'end'=>$begin + $increments
      ];
    }

    return $list;
  }
}