<?php
use Enpowi\App;
use Enpowi\Modules\Module;
use Enpowi\Modules\DataOut;
Module::is();

$workPriority = ETM\Territory::workPriority();
$territoriesNotWorkedInYear = 0;
foreach($workPriority as $territory) {
  if ($territory->workedInYear === false) continue;
  $territoriesNotWorkedInYear++;
}

$data = (new DataOut)
    ->add('territories', $workPriority)
    ->add('territoriesNotWorkedInYear', $territoriesNotWorkedInYear)
    ->out();
?>
<div data="<?php echo $data?>" class="container">
  <h3 v-t>Territory Priority</h3>
  <span v-t>Territories not worked in the year: </span><span>{{ territoriesNotWorkedInYear }}</span>
  <table class="table territory-detail wide">
    <thead>
    <tr>
      <th v-t>Territory</th>
      <th v-t>Locality</th>
      <th v-t>Last Worked</th>
      <th v-t>Last Worked By</th>
      <th v-t>Worked In Year</th>
    </tr>
    </thead>
    <tbody>
    <tr
        v-repeat="territory : territories"
        v-attr="class: territory.workedInYear
          ? 'warning'
          : '' ">
      <td><a
            href="#/territory/view?number={{ territory.number }}"
            v-title="View Territory">{{ territory.number }}</td>
      <td>{{ territory.locality }}</td>
      <td>{{ dateFormatted(territory.record.in) }}</td>
      <td><a href="#/territory/publisher?name={{ territory.record.publisher.name }}">{{ territory.record.publisher.name }}</a></td>
      <td>
        <span v-show="!territory.workedInYear" v-t>Yes</span>
        <span v-show="territory.workedInYear" v-t>No</span>
      </td>
    </tr>
    </tbody>
  </table>
</div>