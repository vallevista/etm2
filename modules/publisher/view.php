<?php
use Enpowi\App;
use Enpowi\Modules\Module;
use Enpowi\Modules\DataOut;
use ETM\Publisher;
Module::is();

$data = (new DataOut)
    ->add('publisher', new Publisher(App::param('name')))
    ->out();
?>
<div
    data="<?php echo $data?>"
    class="container">
  <h3><span v-t>Publisher: </span> {{ publisher.name }}</h3>
  <table class="table">
    <tr v-show="publisher.name">
      <th v-t>Name</th><td>{{ publisher.name }}</td>
    </tr>
    <tr v-show="publisher.email">
      <th v-t>Email</th><td>{{ publisher.email }}</td>
    </tr>
    <tr v-show="publisher.phone">
      <th v-t>Phone</th><td>{{ publisher.phone }}</td>
    </tr>
    <tr v-show="publisher.address1">
      <th v-t>Address 1</th><td>{{ publisher.adddress1 }}</td>
    </tr>
    <tr v-show="publisher.adddress2">
      <th v-t>Address 2</th><td>{{ publisher.adddress2 }}</td>
    </tr>
    <tr v-show="publisher.city">
      <th v-t>City</th><td>{{ publisher.city }}</td>
    </tr>
    <tr v-show="publisher.state">
      <th v-t>State</th><td>{{ publisher.state }}</td>
    </tr>
    <tr v-show="publisher.zip">
      <th v-t>Zip</th><td>{{ publisher.zip }}</td>
    </tr>
  </table>
</div>

