<?php
use Enpowi\App;
use Enpowi\Modules\Module;
use Enpowi\Modules\DataOut;
Module::is();

$data = (new DataOut)
    ->add('publisher', (new \ETM\Publisher(App::param('name')))->bindRecords())
    ->out();
?>
<div
    data="<?php echo $data?>"
    class="container">
  <h3><span v-t>Territory History for: </span>{{ publisher.name }}</h3>
  <table class="wide">
    <thead>
    <tr>
      <th v-t>Territory</th>
      <th v-t>Out</th>
      <th v-t>In</th>
    </tr>
    </thead>
    <tbody>
    <tr v-repeat="record : publisher.records">
      <td>
        <a href="#/territory/view?number={{ record.number }}">{{ record.number }}</a>
      </td>
      <td>{{ dateFormattedShort(record.out) }}</td>
      <td>{{ record.in ? dateFormattedShort(record.in) : '' }}</td>
    </tr>
    </tbody>
  </table>
</div>