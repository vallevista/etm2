<?php
use Enpowi\App;
use Enpowi\Modules\Module;
use Enpowi\Modules\DataOut;
Module::is();

$data = (new DataOut)
    ->add('cardScale', 100)
    ->add('territory', (new \ETM\Territory(App::param('number')))->bindGeoJson())
    ->add('controlsVisible', false)
    ->out();
?>
<div class="container" data="<?php echo $data?>">
  <title>Territory {{ territory.number }}</title>
  <style>
    #card-container {
      margin-left: auto;
      margin-right: auto;
      position: relative;
      width: {{ 1000 * (cardScale / 100)}}px;
    }
    #card {
      width: {{ 1000 * (cardScale / 100) }}px;
    }
    #map {
      height: {{ 600 * (cardScale / 100) }}px;
      width: {{ 1000 * (cardScale / 100) }}px;
    }
    #map-mini {
      height: {{ 375 * (cardScale / 100) }}px;
    }
    #card-label {
      position: absolute;
      top: {{ 90 * (cardScale / 100) }}px;
      left: {{ 20 * (cardScale / 100) }}px;
      width: 0;
    }
    #card-label .top {
      font-size: {{ 30 * (cardScale / 100)}}px;
      cursor: pointer;
    }
    #card-label table, #card-label table td {
      border: 1px solid #C3C3C3;
      border-collapse: collapse;
    }
    #card-label table th {
      border: 1px solid #C3C3C3;
      text-align: center;
      font-weight: bold;
    }
    #directions h3 {
      margin: 0;
      padding: 0;
      width: 100%;
      font-size: {{ 20 * (cardScale / 100) }}px;
    }
    #directions ul {
      padding-left: {{ 7 * (cardScale / 100) }}px;
    }
    #directions ul li {
      font-size: {{ 12 * (cardScale / 100) }}px;
    }
    #aerial-map-note {
      color: red;
      line-height: auto;
      font-size: {{ 10 * (cardScale / 100) }}px;
    }
    #do-not-call-table {
      width: 100%;
      font-size: {{ 10 * (cardScale / 100) }}px;
    }
    #north {
      position: absolute;
      z-index: 900;
      top: {{ 800 * (cardScale / 100) }}px;
      width: {{ 109 * (cardScale / 100) }}px;
    }
    .glyph-btn {
      margin-left: 30px;
    }
    .glyph-btn img {
      width: 20px;
    }
    #card-controls {
      text-align: right;
      height: 40px;
    }
    .leaflet-control-container {
      display: {{controlsVisible ? '' : 'none'}};
    }
    .leaflet-bottom.leaflet-right {
      display: {{controlsVsisible ? '' : 'none'}};
    }
    footer {
      display: none;
    }
    #col1{
      width: {{ 175 * (cardScale / 100) }}px;
    }
    #col2{
      width: {{ 540 * (cardScale / 100) }}px;
    }
    #col3{
      width: {{ 20 * (cardScale / 100) }}px;
    }
    #col4{
      width: {{ 10 * (cardScale / 100) }}px;
    }
    #col5{
      width: {{ 30 * (cardScale / 100) }}px;
    }
    #col6{
      width: {{ 190 * (cardScale / 100) }}px;
    }
  </style>
  <div id="card-controls">
    <label>
      <span v-t>Scale</span>
      <input id="card-scale" type="text" v-model="cardScale" v-title="Scale">
    </label>
    <button
        v-title="Toggle Controls"
        href="#"
        id="toggle-controls"
        v-on="click: controlsVisible = !controlsVisible"
        class="btn btn-default glyph-btn pull-right"><img src="assets/svg/hand369.svg"></button>
    <a
        v-title="Print"
        href="#"
        id="print-card"
        class="btn btn-default glyph-btn pull-right"><img src="assets/svg/print.svg"></a>
  </div>
  <div
      id="card-container">
    <table id="card-label" border="0">
      <colgroup>
        <col id="col1">
        <col id="col2">
        <col id="col3">
        <col id="col4">
        <col id="col5">
        <col id="col6">
      </colgroup>
      <tr class="top">
        <td></td>
        <th>{{ territory.locality }}</th>
        <td></td>
        <td></td>
        <td></td>
        <th>{{ territory.number }}</th>
      </tr>
      <tr>
        <td colspan="2" style="text-align: center;padding-left: 1%; vertical-align: top;">
          <div id="map-mini"></div>
          <div id="aerial-map-note">
            (aerial map, larger map on back)
          </div>
        </td>
        <td></td>
        <td id="directions" colspan="3">
          <h3 v-t>Directions</h3>
          <ul>
            <li>Work <strong>houses, apartments, and businesses</strong> that are encompassed within the <span style="color: #50B414;">green highlighted area</span>.</li>
            <li>Keep track of do not calls on front.</li>
          </ul>
          <h3>Do Not Calls</h3>
          <table id="do-not-call-table" border="1">
            <tr>
              <th>Name</th>
              <th>Address</th>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td>&nbsp;</td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
    <img
        id="card"
        src="assets/card.png"/>
    <hr style="line-height: 4px;" />
    <img id="north" src="assets/n.png" />
    <div id="map"></div>
  </div>
</div>
<script>
  app.oneTo().land(function() {
    var data = datas[0],
        territory = data.territory,
        geoJson = JSON.parse(territory.geoJson),
        mapElement = app.getElementById('map'),
        mapMiniElement = app.getElementById('map-mini'),
        printCard = app.getElementById('print-card'),
        map = L.map(mapElement),
        mapMini = L.map(mapMiniElement),
        options = {
          style: {
            color: '#50B414',
            weight: 5,
            opacity: 0.65
          }
        },
        mapGeoJson = L.geoJson(geoJson, options).addTo(map),
        mapMiniGeoJson = L.geoJson(geoJson, options).addTo(mapMini);

    L.tileLayer('http://{s}.tile.openstreetmap.de/tiles/osmde/{z}/{x}/{y}.png', {
      maxZoom: 18,
      attribution: '&nbsp;'
    }).addTo(map);

    L.tileLayer('http://{s}.tile.openstreetmap.de/tiles/osmde/{z}/{x}/{y}.png', {
      maxZoom: 18,
      attribution: '&nbsp;'
    }).addTo(mapMini);

    map.scrollWheelZoom.disable();
    map.fitBounds(mapGeoJson.getBounds());

    mapMini.scrollWheelZoom.disable();
    mapMini
        .fitBounds(mapMiniGeoJson.getBounds())
        .zoomOut()
        .zoomOut()
        .zoomOut();

    printCard.onclick = function() {
      window.print();
      return false;
    };
  });
</script>