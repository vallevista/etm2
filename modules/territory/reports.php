<?php
use Enpowi\Modules\Module;
use Enpowi\Modules\DataOut;
use ETM\Reader;
Module::is();

if (!file_exists('../config.php')) {
  echo "You need to setup your config.php file in the root directory before running this.";
}

require_once(path . 'modules/territory/config.php');

$key = $config->googleSpreadsheetKey;
$etm = new Reader($config);

$data = (new DataOut)
  ->add('territories', $etm->territoryCollections)
  ->add('idealReturnDates', $etm->idealReturnDates())
  ->add('assignmentRecordsList', $etm->assignmentRecordsList())
  ->add('lastTerritoryName', $etm->lastTerritoryName())
  ->out();
?>
<title v-t>Territories</title>
<link href="../assets/style.css" type="text/css" rel="Stylesheet" />
<div id="tabs">
  <ul>
    <li><a href="#list" v-t>List</a></li>
    <li><a href="#priority" v-t>Need Reworked</a></li>
    <li><a href="#idealReturnDates" v-t>Ideal Return Dates</a></li>
    <li><a href="#territoryAssignmentRecords" v-t>Territory Assignment Records</a></li>
    <li id="spreadsheet" class="ui-button ui-widget thin" style="float:right;">
      <a href="https://docs.google.com/spreadsheets/d/<?php echo $key; ?>" target="_top"><img src="../assets/img/spreadsheet7.svg" class="territory-icon"></a>
    </li>
    <li v-repeat="" id='overview' title='Click for map of all territories' class='ui-button ui-widget territory thin' style='float: right'>
      <a href='../viewTerritories.php?index=$index' title='$localityName - Overview' target='_blank'><img src='../assets/img/web22.svg' class='territory-icon'></a>
    </li>
  </ul>
  <div id="list">
    <table class="territory-detail">
      <thead>
      <tr>
        <th>Territory</th>
        <th>Locality</th>
        <th>Status</th>
        <th>Publisher</th>
      </tr>
      </thead>
      <tbody>
      <tr
          v-repeat="territory : territories"
          v-on="click: window.open('../viewTerritory.php?' +
            'territory=' + territory.name +
            '&locality=' + territory.locality
          , '_blank', '');">
        <td
          class='territory'
          v-attr="id: 'territory' + $index,
          data-index: $index">
            {{ territory.name }}</td>
        <td>{{ territory.locality }}</td>
        <td>{{ territory.status }}</td>
        <td>{{ territory.publisher.name }}</td>
      </tr>
      </tbody>
    </table>
  </div>
  <div id="priority">
    <table class="territory-detail">
      <thead>
      <tr>
        <th v-t>Territory</th>
        <th v-t>Last Worked On Date</th>
      </tr>
      </thead>
      <tbody>
      <tr
          v-repeat="priority : priorities"
          v-on="onclick: window.open(
              '../viewTerritory.php?' +
              'territory=' + priority.territory +
              '&locality=' + priority.locality
            , '_blank', '');">
        <td class='center'>{{ priority.territory }}</td>
        <td class='center date'>{{ priority.in }}</td>
      </tr>
      </tbody>
    </table>
  </div>
  <div id="idealReturnDates">
    <table class="territory-detail">
      <thead>
      <tr>
        <th v-t>Publisher</th>
        <th v-t>Territory</th>
        <th v-t>Locality</th>
        <th v-t>Date</th>
      </tr>
      </thead>
      <tbody>
      <tr
          v-repeat="territory : idealReturnDates"
          v-on="click: go('territory/view?territory=' + territory.name + '&locality=' + territory.locality)">
        <td>{{ territory.publisher.name }}</td>
        <td>{{ territory.territory }}</td>
        <td>{{ territory.locality }}</td>
        <td>{{ territory.idealReturnDate }}</td>
      </tr>
      </tbody>
    </table>
  </div>
  <div id="territoryAssignmentRecords">
    <ul class="assignment-record-list">
      <li v-repeat="assignmentRecords : assignmentRecordsList">
        <a
            v-on="click: go('territory/assignmentRecords?at=' + $index + '&max=' + lastTerritoryNumber)"
            target='_blank'>Set {{ assignmentRecords.begin }} to {{ assignmentRecords.end }}
        </a>
      </li>
    </ul>
  </div>
</div>
<script>
  $('.ui-button').button();

  $('#tabs').tabs();

  $('#spreadsheet').mousedown(function(e) {
    var a = $(this).find('a');
    window.open(a.attr('href'), '_blank', '');
    e.stopPropagation();
    e.preventDefault();
  });
</script>