<?php
use Enpowi\Modules\Module;
use Enpowi\Modules\DataOut;
use ETM\Record;
Module::is();

$data = (new DataOut)
  ->add('history', Record::historyComplete())
  ->out();
?>
<div
    v-module
    data="<?php echo $data?>"
    class="container">
  <table class="table">
    <thead>
    <tr>
      <th></th>
      <th v-t>Date Out</th>
      <th v-t>Date In</th>
      <th v-t>Territory</th>
      <th v-t>Publisher</th>
    </tr>
    </thead>
    <tbody>
      <tr v-repeat="record : history">
        <td></td>
        <td>{{ dateFormatted(record.out) }}</td>
        <td>{{ dateFormatted(record.in) }}</td>
        <td>{{ record.number }}</td>
        <td>{{ record.publisher.name }}</td>
      </tr>
    </tbody>
  </table>
</div>