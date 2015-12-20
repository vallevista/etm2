<?php
use Enpowi\App;
use Enpowi\Modules\Module;
use Enpowi\Modules\DataOut;
Module::is();

$data = (new DataOut)
  ->add('territories', ETM\Territory::all())
  ->out();
?>
<div
    class="container"
    data="<?php echo $data?>">
  <h3><span v-t>Territories</span>
    <a v-title="New Territory" href="#/territory/edit"><span class="glyphicon glyphicon-plus-sign"></span></a></h3>
  <table class="table territory-detail wide">
    <thead>
    <tr>
      <th v-t>Number</th>
      <th v-t>Locality</th>
      <th v-t>Status</th>
      <th v-t>Publisher</th>
      <th v-t>Last Worked</th>
    </tr>
    </thead>
    <tbody>
    <tr
        v-repeat="territory : territories"
        class="territory-entry">
      <td><a
            href="#/territory/view?number={{ territory.number }}"
            v-title="View Territory">{{ territory.number }}</td>
      <td>{{ territory.locality }}</td>
      <td>{{ territory.status }}</td>
      <td>
        <span v-show="territory.status === 'in'">
          <a href="#/territory/publisher?name={{ territory.record.publisher.name }}">
            {{ territory.record.publisher.name }}<a/>
        </span>
      </td>
      <td>
        <span v-show="territory.status === 'in'">
          {{ dateFormattedShort(territory.record.in) }}
        </span></td>
    </tr>
    </tbody>
  </table>
</div>