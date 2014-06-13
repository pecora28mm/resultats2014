<?php
/* Nouvelle Donne -- Copyright (C) Perrick Penet-Avez 2014 - 2014 */

$handle = fopen("data/2012-communes-of-france-with-shapes.csv", "r");
while (($data = fgetcsv($handle)) !== FALSE) {
	$element = $data[18];
	$element = trim(strip_tags($element));
	$elements = explode(",0 ", $element);
	$points = array();
	foreach ($elements as $points_in_string) {
		if (strpos($points_in_string, ",") !== false) {
			$pair = explode(",", $points_in_string);
			if (floatval($pair[0]) != 0.0 and floatval($pair[1]) != 0.0) {
				$points[] = array(floatval($pair[0]), floatval($pair[1]));
			} else {
				echo $data[2]."\n";
				var_export($points_in_string);
				echo "\n\n";
				die();
			}
		}
	}
	$coordinates[$data[2]] = $points;
}
fclose($handle);

$passage = 0;
$features = array();
$departements = array();
$handle = fopen("data/euro-2014-resultats-communes-c.csv", "r");
while (($data = fgetcsv($handle)) !== FALSE) {
	$codeinsee = str_pad($data[1], 3, "0", STR_PAD_LEFT).str_pad($data[4], 3, "0", STR_PAD_LEFT);
	$codedepartement = str_pad($data[1], 3, "0", STR_PAD_LEFT);
	if (!in_array($codedepartement, $departements)) {
		$departements[] = $codedepartement;
	}
	$name = $data[5];
	
	for ($i = 20; $i <= count($data); $i = $i + 7) {
		if (isset($data[$i + 1]) and $data[$i + 1] == "LDVG" and in_array($data[$i + 3], array("POILANE Emmanuel", "COUTELIS Jean-Baptiste", "BOUSSION Joseph", "MAURER Isabelle", "DEVRIENDT Arthur", "LARROUTUROU Pierre", "DANIEAU Laurence"))) {
echo $data[$i + 1]." > ".$data[$i + 3]."\n";
			$value = str_replace(",", ".", $data[$i + 6]);
		}
	}
	
	if (isset($coordinates[$codeinsee])) {
		$features[$codedepartement][] = array(
			'type' => "Feature",
			'id' => $codeinsee,
			'properties' => array(
				'name' => $name,
				'density' => floatval($value),
			),
			'geometry' => array(
				'type' => "Polygon",
				'coordinates' => array($coordinates[$codeinsee]),
			),
		);
		$passage++;
	}
}
fclose($handle);

foreach($departements as $departement) {
	$statesData = array(
		'type' => "FeatureCollection",
		'features' => isset($features[$departement]) ? $features[$departement] : array(),
	);

	file_put_contents(__DIR__."/../www/medias/js/data-2014-".$departement.".js", "var statesData = ".json_encode($statesData));
}
