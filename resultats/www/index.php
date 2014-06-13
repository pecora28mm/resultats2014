<?php
$departement = isset($_GET['departement']) ? str_pad($_GET['departement'], 3, 0, STR_PAD_LEFT) : "059";

$page = <<<HTML
<!DOCTYPE html>
<html>
<head>
	<title>Leaflet Layers Control Example</title>
	<meta charset="utf-8" />

	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<link rel="stylesheet" href="medias/css/leaflet.css" />

	<style>
		#map {
			width: 1200px;
			height: 800px;
		}

		.info {
			padding: 6px 8px;
			font: 14px/16px Arial, Helvetica, sans-serif;
			background: white;
			background: rgba(255,255,255,0.8);
			box-shadow: 0 0 15px rgba(0,0,0,0.2);
			border-radius: 5px;
		}
		.info h4 {
			margin: 0 0 5px;
			color: #777;
		}

		.legend {
			text-align: left;
			line-height: 18px;
			color: #555;
		}
		.legend i {
			width: 18px;
			height: 18px;
			float: left;
			margin-right: 8px;
			opacity: 0.7;
		}
	</style>
</head>
<body>
	<h2>Les résultats de Nouvelle Donne par département aux Européennes 2014</h2>
	<form method="get" action="">
		<strong>dans le département :</strong>
		<input name="departement" value="{$departement}" type="text" />
		<input name="submit" value="mettre à jour" type="submit" />
	</form>
	<br />
	<div id="map"></div>

	<script src="medias/js/leaflet.js"></script>

	<script type="text/javascript" src="medias/js/data-2014-{$departement}.js"></script>
	<script type="text/javascript">

		var map = L.map('map').setView([48,4], 5);

		L.tileLayer('https://{s}.tiles.mapbox.com/v3/{id}/{z}/{x}/{y}.png', {
			maxZoom: 18,
			attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, ' +
				'<a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, ' +
				'Imagery © <a href="http://mapbox.com">Mapbox</a>',
			id: 'examples.map-20v6611k'
		}).addTo(map);


		// control that shows state info on hover
		var info = L.control();

		info.onAdd = function (map) {
			this._div = L.DomUtil.create('div', 'info');
			this.update();
			return this._div;
		};

		info.update = function (props) {
			this._div.innerHTML = '<h4>Résultats des élections européenens 2014</h4>' +  (props ?
				'<b>' + props.name + '</b><br />' + props.density + ' %'
				: 'Survoler une commune');
		};

		info.addTo(map);


		// get color depending on population density value
		function getColor(d) {
			return d > 10 ? '#800026' :
			       d > 7  ? '#BD0026' :
			       d > 5  ? '#E31A1C' :
			       d > 4  ? '#FC4E2A' :
			       d > 3   ? '#FD8D3C' :
			       d > 2   ? '#FEB24C' :
			       d > 1   ? '#FED976' :
			                  '#FFEDA0';
		}

		function style(feature) {
			return {
				weight: 2,
				opacity: 1,
				color: 'white',
				dashArray: '3',
				fillOpacity: 0.7,
				fillColor: getColor(feature.properties.density)
			};
		}

		function highlightFeature(e) {
			var layer = e.target;

			layer.setStyle({
				weight: 5,
				color: '#666',
				dashArray: '',
				fillOpacity: 0.7
			});

			if (!L.Browser.ie && !L.Browser.opera) {
				layer.bringToFront();
			}

			info.update(layer.feature.properties);
		}

		var geojson;

		function resetHighlight(e) {
			geojson.resetStyle(e.target);
			info.update();
		}

		function zoomToFeature(e) {
			map.fitBounds(e.target.getBounds());
		}

		function onEachFeature(feature, layer) {
			layer.on({
				mouseover: highlightFeature,
				mouseout: resetHighlight,
				click: zoomToFeature
			});
		}

		geojson = L.geoJson(statesData, {
			style: style,
			onEachFeature: onEachFeature
		}).addTo(map);

		coord_x = statesData.features[0].geometry.coordinates[0][0][0];
		coord_y = statesData.features[0].geometry.coordinates[0][0][1];
 		map.setView(new L.LatLng(coord_y, coord_x), 9);
		
		map.attributionControl.addAttribution('Résultats des élections européenens 2014 - Source : Ministère de l\'Intérieur');


		var legend = L.control({position: 'bottomright'});

		legend.onAdd = function (map) {

			var div = L.DomUtil.create('div', 'info legend'),
				grades = [0, 1, 2, 3, 4, 5, 7, 10],
				labels = [],
				from, to;

			for (var i = 0; i < grades.length; i++) {
				from = grades[i];
				to = grades[i + 1];

				labels.push(
					'<i style="background:' + getColor(from + 1) + '"></i> ' +
					from + (to ? '&ndash;' + to : '+'));
			}

			div.innerHTML = labels.join('<br>');
			return div;
		};

		legend.addTo(map);

	</script>
</body>
</html>
HTML;

echo $page;