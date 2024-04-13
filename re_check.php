<?php
$context = stream_context_create([
	"http" => [
		"header" => "Accept: application/vnd.de.db.ris+json
DB-Client-Id: $db_client
DB-Api-Key: $db_api"
	] ]);

function station($name, $fertig = false) {
	global $station, $start, $ziel, $nachricht, $context, $mysqli, $time;
	$sql_name = $mysqli->escape_string($name);
	$sql = $mysqli->query("SELECT data FROM bahn_cache WHERE query = '$sql_name'");
	if ($sql) 
		$sql = $sql->fetch_object();
	if (!isset($sql->data) OR aktiv("dev2")) {
		echo "Abfrage durchgeführt: $name<br>";
		$file = file_get_contents("https://www.bahn.de/web/api/reiseloesung/orte?suchbegriff=".urlencode($name)."&typ=ALL&limit=1");
		$s = json_decode($file);
		$s = isset($s[0]) ? $s[0] : null;
		if ($s) {
			parse_str(str_replace("@", "&", $s->id), $s->data);
			$s->data = (object) $s->data;
			$file2 = file_get_contents("https://apis.deutschebahn.com/db-api-marketplace/apis/ris-stations/v1/stop-places/by-name/".urlencode($name), false, $context);
			$data = @json_decode($file2)->stopPlaces[0];
			if ($data AND @$data->names->DE->nameLong == $s->name)
				$s->availableTransports = $data->availableTransports;
			else 
				$s->availableTransports = [1];
		}
		// if ($name == "mainz")
			// $s = $s[1];
		// else
			// $s = isset($s[0]) ? $s[0] : null;
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
		$start = $s;
	elseif ($fertig == "ziel")
		$ziel = $s;
	
	// echo "<br>$name: ".$s->names->DE->nameLong;
	return $s->name;
}

function check($list) {
	global $start, $ziel, $start, $ziel, $station;
	$count = count($list);
	$proba = $start2 = $ziel2 = [];
	$highest_proba = 0;
	for ($i=1; $i<=$count; $i++) {			// Mögliche Start- und Zielnamen durchprobieren
		if ($i == $count && $highest_proba)
			break;
		$s = str_ireplace(["hbf", "hauptbahnhof"], "", join(" ", array_slice($list, 0, $count-$i)));
		$z = str_ireplace(["hbf", "hauptbahnhof"], "", join(" ", array_slice($list, $count-$i)));
		if (aktiv(["DEV", "dev2"]))
			echo "<br>Start: $s | Ziel: $z <br>";
		if (!$s && $z) {
			return station($z, "start");
		}
		
		if ($s && $name1 = station($s)) {
			$proba[$s] = similar_text($name1, $s) / strlen($name1) + count($station[$s]->availableTransports)/8;
			$start2[$i] = $s;
		}
		if ($z && $name2 = station($z)) {
			$proba[$z] = similar_text($name2, $z) / strlen($name2) + count($station[$z]->availableTransports)/8;
			$ziel2[$i] = $z;
		}
		if (isset($proba[$s], $proba[$z])) {
			$value = $proba[$s] + $proba[$z];
			$schwelle = 0.41;
			if ($value > $highest_proba && $proba[$s] > $schwelle && $proba[$z] > $schwelle) {
				$highest_proba = $value;
				$j = $i;
			} elseif ($value > $highest_proba) {
				global $lastchance;
				$lastchance = [$start2[$i], $ziel2[$i]];
			}
			echo "$name1 — $name2: ".round($value, 2)." = ".round($proba[$s],2)."+".round($proba[$z],2) ."<br>";
		}
	}
	if ($highest_proba) {
		if (!$ziel)
			$ziel  = $station[$ziel2[$j]];
		if (!$start)
			$start = $station[$start2[$j]];
	}
}

function check_with_ziel($stationen) {
	global $start, $ziel;
	if (!$start)
		station(join(" ", $stationen[0]), "start");
	if ($start && !$ziel)
		station(join(" ", $stationen[1]), "ziel");
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
			if (!isset($list[mb_strtoupper($station)]) || $station != $stationen[$key][$i])
				continue;
			if ($art == "K" && in_array($station, $uc_only) && mb_strtoupper($station) != $station)
				continue;
			$stationen[$key][$i] = $list[mb_strtoupper($station)];
			$found++;
		}
	}
	if (!$found || $art == "K")
		return 0;
	if (!empty($stationen[1]))
		return check_with_ziel($stationen);
	if (count($stationen[0]) == 2 AND $found == 2) {
		if (!$start)		station(reset($stationen[0]), "start");
		if (!$ziel)			station(end($stationen[0]),   "ziel");
		return true;
	} 
	check($stationen[0]);
	return $start && $ziel;
}

if (aktiv("dev"))
	echo "<h3>Möglichkeiten</h3>";

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