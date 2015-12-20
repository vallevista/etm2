<?php

use Enpowi\App;
use Enpowi\Modules\Module;
use Enpowi\Modules\DataOut;
use ETM\Territory;
Module::is();

$data = (new DataOut)
  ->add('territory', (new Territory(App::param('number')))->bindGeoJson())
  ->out();
?>
<form
    v-module
    action="territory/editService"
    data-done="territory?number={{ territory.number }}"
    class="create container"
    data="<?php echo $data?>">
  <style>
    .create #map {
      width: 100%;
      min-height: 500px;
    }
  </style>
  <table>
    <tr>
      <th><h3 v-t>Edit Territory</h3></th>
      <td> : </td>
      <td>
        <input v-placeholder="Number" v-model="territory.number">
        <input v-placeholder="Locality" v-model="territory.locality">
        <input v-placeholder="Congregation" v-model="territory.congregation">
        <button type="submit" class="btn btn-primary" v-t>Save</button>
        <input type="hidden" v-model="territory">
      </td>
    </tr>
  </table>
  <div id="map"></div>
</form>
<link rel="stylesheet" href="vendor/leaflet-dist/leaflet.css">
<link rel="stylesheet" href="vendor/leaflet-draw/dist/leaflet.draw.css">
<script src="vendor/leaflet-dist/leaflet.js"></script>
<script src="vendor/leaflet-draw/dist/leaflet.draw.js"></script>
<script>
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(loadMap);
  } else {
    loadMap();
  }

  function loadMap(position) {
    var data = datas[0],
        territory = data.territory,
        geoJson = JSON.parse(territory.geoJson),
        mapElement = app.getElementById('map'),
        map = L.map(mapElement),
        drawnItems = new L.FeatureGroup(),
        drawControl = new L.Control.Draw({
          edit: {
            featureGroup: drawnItems
          }
        }),
        options = {
          style: {
            color: '#50B414',
            weight: 5,
            opacity: 0.65
          },
          onEachFeature: function (feature, layer) {
            layer.on('click', function (e) {
              e.target.editing.enable();
            });
          }
        },
        mapGeoJson = L.geoJson(geoJson, options);

    map.fitBounds(mapGeoJson.getBounds());
    drawnItems.addLayer(mapGeoJson);
    map.addControl(drawControl);
    map.addLayer(drawnItems);

    L.tileLayer('http://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
      attribution: '&nbsp;'
    }).addTo(map);

    map.on('draw:created', function(e) {
      drawnItems.addLayer(e.layer);

      data.territory.geoJson = drawnItems.toGeoJSON();
      console.log(drawnItems.toGeoJSON());
    });
  }
</script>