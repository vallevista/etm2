<?php
use Enpowi\Modules\Module;
use Enpowi\Modules\DataOut;

Module::is();

$data = (new DataOut)
  ->add('publishers', ETM\Publisher::all())
  ->add('action', '')
  ->out();
?>
<form
    data="<?php echo $data?>"
    class="container"
    action="publisher/list_service"
    data-done="publisher/list"
    v-module>
  <table class="table">
    <thead>
    <tr>
      <th></th>
      <th v-t>Name</th>
      <th v-t>Commands</th>
    </tr>
    </thead>
    <tbody>
    <tr v-repeat=" publisher : publishers ">
      <td>
        <input type="checkbox" name="publishers[]" value="{{ publisher.name }}">
        <input
            type="radio"
            name="publisher"
            value="{{ publisher.name }}"
            v-show=" action === 'merge' ">
      </td>
      <td><a href="#/publisher/view?name={{ publisher.name }}">{{ publisher.name }}</a></td>
      <td><a href="#/territory/publisher?name={{ publisher.name }}">Territory History</a></td>
    </tr>
    <tr>
      <td colspan="3">
        <select name="action" class="form-control inline" v-model="action">
          <option v-t value="">Action</option>
          <option v-t value="merge">Merge</option>
        </select>
        <button v-t class="btn btn-success">Submit</button>
      </td>
    </tr>
    </tbody>
  </table>
</form>