<?php
use Enpowi\App;
use Enpowi\Modules\Module;
use Enpowi\Modules\DataOut;
Module::is();

//TODO: make a listener

$data = (new DataOut)
    ->add('territory', (new \ETM\Territory(App::param('number')))->bindGeoJson())
    ->add('history', \ETM\Record::history(App::param('number')))
    ->add('action', 0)
    ->add('publisherName', '')
    ->add('showPublisherLookup', false)
    ->out();
?>
<div
    class="container center"
    data="<?php echo $data?>">
  <title>Territory {{ territory.number }}</title>
  <style>
    #map {
      min-height: 400px;
    }
    table.history {
      width: 50%;
      margin-left: auto;
      margin-right: auto;
    }
    .glyph-btn {
      margin-left: 30px;
    }
    .glyph-btn img {
      width: 50px;
    }
    form#check-in {
      display: inline;
    }
    .leaflet-bottom.leaflet-right {
      display: none;
    }
  </style>
  <h3>
    <span v-t>Territory #</span>
    {{ territory.number }},
    {{ territory.locality }}
  </h3>
  <div id="map"></div>
  <hr>

  <div v-show="!showPublisherLookup">
    <form
        id="check-in"
        v-module
        action="territory/checkin_service?number={{ territory.number }}"
        data-done="territory/view?number={{ territory.number }}">
      <button
          type="submit"
          v-show="territory.status !== 'in'"
          v-title="Check In"
          class="btn btn-default glyph-btn"><img src="assets/svg/school29.svg"></button>
    </form>

    <a
        v-show="territory.status !== 'out'"
        v-title="Check Out"
        v-on="click: showPublisherLookup = true"
        class="btn btn-default glyph-btn"><img src="assets/svg/box17.svg"></a>

    <a
        v-title="View Card"
        href="#/territory/card?number={{ territory.number }}"
        class="btn btn-default glyph-btn"><img src="assets/svg/maps4.svg"></a>

    <a
        id="directions"
        v-title="Directions"
        class="btn btn-default glyph-btn"><img src="assets/svg/map47.svg"></a>

    <a
        v-title="Edit Do Not Calls"
        href="#/territory/edit?number={{ territory.number }}"
        class="btn btn-default glyph-btn"><img src="assets/svg/hand369.svg"></a>

    <a
        v-title="Edit"
        href="#/territory/edit?number={{ territory.number }}"
        class="btn btn-default glyph-btn"><img src="assets/svg/pencil125.svg"></a>
  </div>
  <form
      v-module
      action="territory/checkout_service?number={{ territory.number }}&publisherName={{ publisherName }}"
      data-done="territory/view?number={{ territory.number }}"
      v-show="showPublisherLookup">
    <input
        class="typeahead"
        type="text"
        v-find="{
          find: 'publisher/find'
        }"
        v-model="publisherName"
        v-placeholder="Publisher Name">
    <button
        type="submit"
        v-attr="disabled: publisherName.length < 1"
        v-t>Check Out</button>
    <button
        v-on="click: showPublisherLookup = false"
        v-t>Cancel</button>
  </form>
  <hr>
  <h4 v-t>History</h4>
  <table class="table history">
    <thead>
    <tr>
      <th v-t>Publisher</th>
      <th v-t>Date Out</th>
      <th v-t>Date In</th>
    </tr>
    </thead>
    <tbody>
    <tr v-repeat="record : history">
      <td><a href="#/territory/publisher?name={{ record.publisher.name }}">{{ record.publisher.name }}</a></td>
      <td>{{ dateFormatted(record.out) }}</td>
      <td>{{ record.in ? dateFormatted(record.in) : '' }}</td>
    </tr>
    </tbody>
  </table>
</div>

<script>
  app.oneTo().land(function() {
    var data = datas[0],
        territory = data.territory,
        geoJson = JSON.parse(territory.geoJson),
        mapElement = app.getElementById('map'),
        map = L.map(mapElement),
        options = {
          style: {
            color: '#50B414',
            weight: 5,
            opacity: 0.65
          }
        },
        mapGeoJson = L.geoJson(geoJson, options).addTo(map);
    map.fitBounds(mapGeoJson.getBounds());
    map.scrollWheelZoom.disable();

    L.tileLayer('http://{s}.tile.openstreetmap.de/tiles/osmde/{z}/{x}/{y}.png', {
      maxZoom: 18,
      attribution: '&nbsp;'
    }).addTo(map);

    provideDirections(map.getCenter());

    function provideDirections(latLng) {
      app.getElementById('directions').onclick = function() {
        window.open('https://maps.google.com/maps?q=' + latLng.lat + ',' + latLng.lng);
        return false;
      };
    }
  });
</script>