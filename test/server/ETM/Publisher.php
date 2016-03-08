<?php
use ETM\Publisher;
use ETM\Record;
use ETM\Territory;

$tf->test('Publisher can be merged', function(\Testify\Testify $tf) {
  $bob = Publisher::fromName('Bob');
  $bobby = Publisher::fromName('Bobby');
  $robert = Publisher::fromName('Robert');

  (new Territory(1))->replace();
  (new Territory(2))->replace();
  (new Territory(3))->replace();
  (new Territory(4))->replace();
  (new Territory(5))->replace();
  (new Territory(6))->replace();

  Record::checkOut(1, $bob);
  Record::checkOut(2, $bob);
  $bob->save();

  Record::checkOut(3, $bobby);
  Record::checkOut(4, $bobby);
  $bobby->save();

  Record::checkOut(5, $robert);
  Record::checkOut(6, $robert);
  $robert->save();

  Publisher::merge('Bob', ['Bobby', 'Robert']);

  $tf->assertEquals(count((new Publisher('Bob'))->bindRecords()->records), 6);
});

$tf->test('Merging will not happen on destination publisher', function(\Testify\Testify $tf) {
  $bob = Publisher::fromName('Bob');
  $bobby = Publisher::fromName('Bobby');
  $robert = Publisher::fromName('Robert');

  (new Territory(1))->replace();
  (new Territory(2))->replace();
  (new Territory(3))->replace();
  (new Territory(4))->replace();
  (new Territory(5))->replace();
  (new Territory(6))->replace();

  Record::checkOut(1, $bob);
  Record::checkOut(2, $bob);
  $bob->save();

  Record::checkOut(3, $bobby);
  Record::checkOut(4, $bobby);
  $bobby->save();

  Record::checkOut(5, $robert);
  Record::checkOut(6, $robert);
  $robert->save();

  Publisher::merge('Bob', ['Bob', 'Bobby', 'Robert']);

  $tf->assertEquals(count((new Publisher('Bob'))->bindRecords()->records), 6);
});