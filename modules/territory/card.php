<?php
use Enpowi\App;
use Enpowi\Modules\Module;
use Enpowi\Modules\DataOut;
Module::is();

$data = (new DataOut)
    ->add('territory', (new \ETM\Territory(App::param('number')))->bindGeoJson())
    ->out();
?>
<div id="card" class="container" data="<?php echo $data?>">
  <title>Territory {{ territory.number }}</title>
  <style>
    #card {
      margin-left: auto;
      margin-right: auto;
    }
    #map {
      height: 600px;
      width: 1000px;
    }
    #mapMini {
      height: 375px;
    }
    #card {
      width: 1000px;
    }
    #cardLabel {
      position: absolute;
      top: 160px;
      width: 0px;
    }
    #cardLabel th {
      font-size: 30px;
    }
    #cardLabel .top {
      cursor: pointer;
    }
    #cardLabel table, #cardLabel table td {
      border: 1px solid #C3C3C3;
      border-collapse: collapse;
    }
    #cardLabel table th {
      border: 1px solid #C3C3C3;
      text-align: center;
      font-weight: bold;
    }
    #directions h3 {
      margin: 0;
      padding: 0;
      width: 100%;
      font-size: 20px;
    }
    #directions ul {
      padding-left: 7px;
    }
    #aerial-map-note {
      color: red;
      line-height: auto;
    }
    #doNotCallTable {
      width: 100%;
    }
    #doNotCallTable th {
      font-size: 10px;
    }
    #north {
      position: absolute;
      z-index: 900;
      top: 800px;
    }
  </style>
  <table id="cardLabel" border="0">
    <colgroup>
      <col style="width: 175px;">
      <col style="width: 540px;">
      <col style="width: 20px;">
      <col style="width: 10px;">
      <col style="width: 30px;">
      <col style="width: 190px;">
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
        <div id="mapMini"></div>
        <div id="aerial-map-note">
          (aerial map, larger map on back)
        </div>
      </td>
      <td></td>
      <td id="directions" colspan="3">
        <h3 v-t>Directions</h3>
        <ul>
          <li>Work <span style="font-weight: bold;">houses, apartments, and businesses</span> that are encompassed within the <span style="color: #50B414;">green highlighted area</span>.</li>
          <li>Keep track of do not calls on front.</li>
        </ul>
        <h3>Do Not Calls</h3>
        <table id="doNotCallTable" border="1">
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
  <img id="card" src="assets/card.png" />
  <hr style="line-height: 4px;" />
  <img id="north" src="assets/n.png" />
  <div id="map"></div>
</div>

<script>
  app.oneTo().land(function() {
    var data = datas[0],
        territory = data.territory,
        geoJson = JSON.parse(territory.geoJson),
        mapElement = app.getElementById('map'),
        mapMiniElement = app.getElementById('mapMini'),
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

    map.fitBounds(mapGeoJson.getBounds());

    mapMini
        .fitBounds(mapMiniGeoJson.getBounds())
        .zoomOut()
        .zoomOut()
        .zoomOut();
  });
</script>