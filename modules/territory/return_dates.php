<?php
use Enpowi\App;
use Enpowi\Modules\Module;
use Enpowi\Modules\DataOut;
Module::is();

$data = (new DataOut)
    ->add('territories', ETM\Territory::returnPriority())
    ->out();
?>
<div
    class="container"
    data="<?php echo $data?>">
  <h3 v-t>Ideal Territory Return Dates</h3>
  <table class="table territory-detail wide">
    <thead>
    <tr>
      <th v-t>Territory</th>
      <th v-t>Locality</th>
      <th v-t>Checkout Out</th>
      <th v-t>Ideal Return Date</th>
      <th v-t>Publisher</th>
      <th v-t>Status</th>
    </tr>
    </thead>
    <tbody>
    <tr
        v-repeat="territory : territories"
        v-attr="class: territory.due
          ? 'warning'
          : '' ">
      <td><a
            href="#/territory/view?number={{ territory.number }}"
            v-title="View Territory">{{ territory.number }}</td>
      <td>{{ territory.locality }}</td>
      <td>{{ dateFormatted(territory.record.out) }}</td>
      <td>{{ dateFormatted(territory.idealReturnDate) }}</td>
      <td><a href="#/territory/publisher?name={{ territory.record.publisher.name }}">{{ territory.record.publisher.name }}</a></td>
      <td>
        <span
            v-t
            v-show="territory.due">Due</span>
        <span
            v-t
            v-show="!territory.due">Out</span>
      </td>
    </tr>
    </tbody>
  </table>
</div>