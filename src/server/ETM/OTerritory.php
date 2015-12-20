<?php
namespace ETM;

class OTerritory {
  public $name;
  public $locality = '';
  public $publisher = '';
  public $congregation = '';
  public $status;
  public $out;
  public $in;
  public $idealReturnDate;

  public static $statusIn = 'in';
  public static $statusOut = 'out';

  static public $secondsInMonth = 2592000;

  public function __construct($row = null, $locality = null)
  {
    if ($row != null) {
      $this->name = $row->territory . '';
      $this->publisher = $row->publisher . '';

      //out
      $out = $row->out . '';
      if (empty($out)) {
        $this->out = null;
      } else {
        $this->out = \DateTime::createFromFormat(Reader::$config->dateFormat, $out)->getTimestamp();
      }

      //ideal return date
      $this->idealReturnDate = $this->out + (self::$secondsInMonth * 4);

      //in
      $in = $row->in . '';

      if (empty($in)) {
        $this->in = null;
        $this->status = self::$statusOut;
      } else {
        $this->in = \DateTime::createFromFormat(Reader::$config->dateFormat, $in)->getTimestamp();
        $this->status = self::$statusIn;
      }

      $this->locality = $locality;
    }
  }
}