<?php
use Enpowi\App;
use Enpowi\Modules\Module;
use Enpowi\Modules\DataOut;
Module::is();

$data = (new DataOut)
    ->add('territoryCount', ETM\Territory::count())
    ->add('pages', ETM\Territory::pages())
    ->out();
?>
<div
    data="<?php echo $data?>"
    class="container">
  <h3>Territory Book</h3>
  <table>
    <tr>
      <th>Page</th>
      <th>&nbsp;</th>
      <th>Territories</th>
    </tr>
    <tr v-repeat="page : pages">
      <td><a href="#/territory/page?page={{ $index + 1 }}">{{ $index + 1 }}</a></td>
      <td></td>
      <td>{{ page[0] + ' - ' + page[page.length - 1] }}</td>
    </tr>
  </table>
</div>