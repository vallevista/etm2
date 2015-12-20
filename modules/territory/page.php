<?php
use Enpowi\App;
use Enpowi\Modules\Module;
use Enpowi\Modules\DataOut;
Module::is();

$page = App::paramInt('page');

$data = (new DataOut)
    ->add('territoryCount', ETM\Territory::count())
    ->add('page', $page)
    ->add('territories', ETM\Territory::page($page))
    ->out();
?>
<div
    v-module
    data="<?php echo $data?>">
  <title>Territory Assignment Records Page {{ page }}</title>
  <style>
    * {
      font-family: "Consolas", monospace;
      font-size: 35px;
      line-height: 53px;
      text-decoration: none;
      color: black;
    }
    a {
      color: black ! important;
    }
  </style>
  <img id="card" src="assets/s13.png" style="position: absolute; top: 0px; left: 0px;"/>
  <table
      v-repeat="territory : territories"
      v-attr="style: '
      position: absolute;
      top: 290px;
      left: ' + (140 + ($index * 455)) + 'px;
      width: 454px;
    '">
    <thead>
    <tr>
      <td colspan='2' style='height: 40px; padding-left: 120px; padding-bottom: 10px;'>
        <a href="#/territory/view?number={{ territory.number }}">{{ territory.number }}</a>
      </td>
    </tr>
    </thead>
    <tbody v-repeat="record : territory.records">
    <tr>
      <td colspan='2'><a href="#/territory/publisher?name={{ record.publisher.name }}">{{ record.publisher.name }}</a></td>
    </tr>
    <tr>
      <td style="height: 60px;">{{ dateFormattedShort(record.out) }}</td>
      <td>{{ record.in ? dateFormattedShort(record.in) : '' }}</td>
    </tr>
    </tbody>
  </table>
</div>
<script>
  /*var canvas = document.getElementById('canvas');
   var ctx = canvas.getContext('2d');

   var data = '<svg xmlns="http://www.w3.org/2000/svg" width="200" height="200">' +
   '<foreignObject width="100%" height="100%">' +
   '<div xmlns="http://www.w3.org/1999/xhtml" style="font-size:40px">' +
   '<em>I</em> like ' +
   '<span style="color:white; text-shadow:0 0 2px blue;">' +
   'cheese</span>' +
   '</div>' +
   '</foreignObject>' +
   '</svg>';

   var DOMURL = window.URL || window.webkitURL || window;

   var img = new Image();
   var svg = new Blob([data], {type: 'image/svg+xml;charset=utf-8'});
   var url = DOMURL.createObjectURL(svg);

   img.onload = function () {
   ctx.drawImage(img, 0, 0);
   DOMURL.revokeObjectURL(url);
   }

   img.src = url;*/
</script>