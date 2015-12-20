<?php
use Enpowi\Modules\Module;
use Enpowi\Modules\DataOut;

Module::is();

$data = (new DataOut)
  ->add('congregationGeoJson', json_encode(ETM\Territory::allGeoJson()))
  ->out();
?>
<div data="<?php echo $data?>">
  <style>
    .leaflet-label-overlay {
      color: #000000;
      font-weight: bold;
      size: 4em;
    }
  </style>
  <div id="map" style="width: 100%; min-height: 500px;"></div>
</div>
<script>
  app.oneTo().land(function() {
    var mapElement = app.getElementById('map'),
        map = L.map(mapElement),
        geoJson = JSON.parse(datas[0].congregationGeoJson),
        options = {
          style: {
            color: '#50B414',
            weight: 5,
            opacity: 0.65
          }
        },
        geoJsonLayer = L.geoJson(geoJson, options);

    console.log(geoJsonLayer);
    /*window.onresize = function() {
      mapElement.style.height = mapElement.parentNode.clientHeight = 'px';
    };
    window.onresize();*/
    map.fitBounds(geoJsonLayer.getBounds());

    L.tileLayer('http://{s}.tile.openstreetmap.de/tiles/osmde/{z}/{x}/{y}.png', {
      maxZoom: 18,
      attribution: '&nbsp;'
    }).addTo(map);

    geoJsonLayer.eachLayer(function(layer) {
      var label = layer.feature.properties.number,
          labelOverlay = new L.LabelOverlay(layer, label);

      map.addLayer(labelOverlay);
    }).addTo(map);
  });
</script>