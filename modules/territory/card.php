<?php
use Enpowi\App;
use Enpowi\Modules\Module;
use Enpowi\Modules\DataOut;
Module::is();

$data = (new DataOut)
    ->add('territory', (new \ETM\Territory(App::param('number')))->bindGeoJson())
    ->out();
?>
<div class="container" data="<?php echo $data?>">
  <title>Territory {{ territory.number }}</title>
  <style>
    #map {
      min-height: 400px;
    }
    #mapMini {
      min-height: 200px;
    }
    #card {
      width: 100%;
    }
    #cardLabel {
      position: absolute;
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
      margin: 0px;
      padding: 0px;
      width: 100%;
    }
    #directions ul {
      padding-left: 7px;
    }
    #aerial-map-note {
      color: red;
      line-height: auto;
    }
  </style>
  <!--img id="card" src="assets/card.png" />
  <table id="cardLabel" border="0">
    <tr class="top">
      <td style="width: 3%;"></td>
      <td style="width: 10%;"></td>
      <td style="width: 35%; max-width:35%;">
        <span style="position: absolute; max-width: 12em; top: 0.23em; line-height: 0.9em;">{{ territory.locality }}</span>
      </td>
      <td style="width: 1%;"></td>
      <td style="width: 10%;"></td>
      <td style="width: 12%;">{{ territory.number }}</td>
      <td style="width: 1%;"></td>
    </tr>
    <tr>
      <td colspan="3" style="text-align: center;padding-left: 1%; vertical-align: top;">
        <div id="aerial-map-note">
          (aerial map, larger map on back)
        </div>
      </td>
      <td></td>
      <td id="directions" colspan="2">
        <h3>Directions</h3>
        <ul>
          <li>Work <span style="font-weight: bold;">houses, apartments, and businesses</span> that are encompassed within the <span style="color: #50B414;">green highlighted area</span>.</li>
          <li>Keep track of do not calls on front.</li>
        </ul>
        <h3>Do Not Calls</h3>
        <table style="width: 100%;" border="1">
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
  <br style="line-height: 4px;" />
  <img id="north" src="assets/n.png" /-->
  <div id="mapMini"></div>
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
        $aerialMapNote =  $(app.getElementById('aerial-map-note')),
        $mapElement = $(mapElement),
        $mapMiniElement = $(mapMiniElement),
        $north = $(app.getElementById('north')),
        $card = $(app.getElementById('card')),
        $cardLabel = $(app.getElementById('cardLabel')),
        $directions = $(app.getElementById('directions')),
        height,
        width,
        options = {
          style: {
            color: '#50B414',
            weight: 5,
            opacity: 0.65
          }
        },
        mapGeoJson = L.geoJson(geoJson, options).addTo(map),
        mapMiniGeoJson = L.geoJson(geoJson, options).addTo(mapMini);

    map.fitBounds(mapGeoJson.getBounds());

    mapMini
        .fitBounds(mapMiniGeoJson.getBounds())
        .zoomOut()
        .zoomOut()
        .zoomOut();

    L.tileLayer('http://{s}.tile.openstreetmap.de/tiles/osmde/{z}/{x}/{y}.png', {
      maxZoom: 18,
      attribution: '&nbsp;'
    }).addTo(map);

    L.tileLayer('http://{s}.tile.openstreetmap.de/tiles/osmde/{z}/{x}/{y}.png', {
      maxZoom: 18,
      attribution: '&nbsp;'
    }).addTo(mapMini);

    //provideDirections(map.getCenter());

    function onPopupClose(evt) {
      select.unselectAll();
    }

    function provideDirections(latLng) {
      $cardLabel.find('.top').click(function() {
        window.open('https://maps.google.com/maps?q=' + latLng.lat + ',' + latLng.lng);
      });
    }
  });
</script>