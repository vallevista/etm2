<?php
use Enpowi\Modules\Module;
use Enpowi\Modules\DataOut;

Module::is();

$data = (new DataOut)
  ->add('publishers', ETM\Publisher::all())
  ->out();
?>
<div
    data="<?php echo $data?>"
    class="container">
  <table>
    <thead>
    <tr>
      <td v-t>Name</td>
      <td v-t>Commands</td>
    </tr>
    </thead>
    <tbody>
    <tr v-repeat=" publisher : publishers ">
      <td>{{ publisher.name }}</td>
      <td><a href="#/territory/publisher?name={{ publisher.name }}">Territory History</a></td>
    </tr>
    </tbody>
  </table>
</div>
