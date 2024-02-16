<?php

function station($name, $fertig = false) {
	global $station, $start_station, $ziel_station, $nachricht, $context, $mysqli, $time;
	$sql_name = $mysqli->escape_string($name);
	$sql = $mysqli->query("SELECT data FROM bahn_cache WHERE query = '$sql_name'");
	if ($sql) 
		$sql = $sql->fetch_object();
	if (!isset($sql->data) OR aktiv("dev2")) {
		echo "<br>Abfrage durchgeführt: $name";
		$file = file_get_contents("https://apis.deutschebahn.com/db-api-marketplace/apis/ris-stations/v1/stop-places/by-name/".urlencode($name), false, $context);
		$s = json_decode($file)->stopPlaces;
		if ($name == "mainz")
			$s = $s[1];
		else
			$s = isset($s[0]) ? $s[0] : null;
		$mysqli->query("INSERT INTO bahn_cache VALUES ('$sql_name', '".$mysqli->escape_string(json_encode($s))."', $time)");
	} else {
		$s = json_decode($sql->data);
	}
	$station[$name] = $s;
	if ($s === null) {
		$nachricht = "Bahnhof nicht gefunden! ($name)";
		return null;
	}
	if ($fertig == "start")
		$start_station = $s;
	elseif ($fertig == "ziel")
		$ziel_station = $s;
	
	// echo "<br>$name: ".$s->names->DE->nameLong;
	return $s->names->DE->nameLong;
}

function check($list) {
	global $start, $ziel, $start_station, $ziel_station, $station;
	$count = count($list);
	$proba = $start2 = $ziel2 = [];
	$highest_proba = 0;
	for ($i=1; $i<=$count; $i++) {			// Mögliche Start- und Zielnamen durchprobieren
		if ($i == $count && $highest_proba)
			break;
		$s = join(" ", array_slice($list, 0, $count-$i));
		$z = join(" ", array_slice($list, $count-$i));
		if (aktiv(["DEV", "dev2"]))
			echo "<br>$s | $z ";
		
		if ($s && $name1 = station($s)) {
			$proba[$s] = similar_text($name1, $s) / strlen($name1) + count($station[$s]->availableTransports)/11;
			$start2[$i] = $s;
		}
		if ($z && $name2 = station($z)) {
			$proba[$z] = similar_text($name2, $z) / strlen($name2) + count($station[$z]->availableTransports)/11;
			$ziel2[$i] = $z;
		}
		if (isset($proba[$s], $proba[$z]) && $value = $proba[$s] + $proba[$z]) {
			if ($value > $highest_proba) {
				$highest_proba = $value;
				$j = $i;
				echo "<br>$name1 — $name2: $value, ".round($proba[$s],2)."|".round($proba[$z],2);
			}
		}
	}
	if ($highest_proba && !$ziel) {
		$ziel_station = $station[$ziel2[$j]];
		$ziel = $ziel_station->names->DE->nameLong;
	} elseif (!$ziel) {
		$j = 0;
		$start2[$j] = join(" ", $list);
		$ziel  = null;
	}
	if (!$start) {
		$start_station = $station[$start2[$j]];
		$start = @$start_station->names->DE->nameLong;
	}
}

function check_with_ziel($stationen) {
	global $start, $ziel;
	if (!$start)
		$start = station(join(" ", $stationen[0]), "start");
	if ($start && !$ziel)
		$ziel = station(join(" ", $stationen[1]), "ziel");
	return $start && $ziel;
}

function check_list($art) {
	global $stationen, $stationen_raw, $start, $ziel, $leitpunkte, $betriebsstellen, $kennzeichen, $uc_only;
	require_once "punkte.php";
	$found = 0;
	switch ($art) {
		case "L": $list = $leitpunkte;		break;
		case "B": $list = $betriebsstellen;	break;
		case "K": $list = $kennzeichen;
	}
	foreach ($stationen_raw as $key => $arr2) {
		foreach ($arr2 as $i => $station) {
			if (!isset($list[strtoupper($station)]) || $station != $stationen[$key][$i])
				continue;
			if ($art == "K" && in_array($station, $uc_only) && strtoupper($station) != $station)
				continue;
			$stationen[$key][$i] = $list[strtoupper($station)];
			$found++;
		}
	}
	// print_r($stationen);
	// echo "---$art--$found--$start---$ziel--";
	if (!$found || $art == "K")
		return 0;
	if (!empty($stationen[1]))
		return check_with_ziel($stationen);
	if (count($stationen[0]) == 2 AND $found == 2) {
		if (!$start)		$start = station(reset($stationen[0]), "start");
		if (!$ziel)			$ziel  = station(end($stationen[0]),   "ziel");
		return true;
	} 
	check($stationen[0]);
	return $start && $ziel;
}

// function check_betriebsstellen($array) {
	// global $betriebsstellen, $start, $ziel;
	// if (count($array) > 2) {
		// $prevKey = array_key_first($array);
		// foreach ($array as $key => $el) {
			// if ($prevKey === null) {
				// $prevKey = $key;
				// continue;
			// }
			// if (strlen($el) == 1) {
				// $array[$prevKey
			// }
		// }
		// for ($i =1; $i < count($array); $i++) {		// Z.B. "KKE 1" wird zusammengeführt
			// if (strlen($array[$i]) == 1) {
				// $array[$i-1] .= " ".$array[$i];
				// unset($array[$i]);
				// $i++;
			// }
		// }
	// }
	// $array = array_values($array);
	// if (isset($betriebsstellen[strtoupper($array[0])], $betriebsstellen[strtoupper($array[1])])) {
		// $start = station($betriebsstellen[strtoupper($array[0])], "start");
		// $ziel  = station($betriebsstellen[strtoupper($array[1])], "ziel");
		// if ($start && !$ziel) {
			// return true;
		// }
		// $start = $ziel = null;
	// }
	// return false;
// }