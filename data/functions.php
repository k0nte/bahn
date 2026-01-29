<?php

function myUrlEncode($string) {
	$entities = array('%28', '%29', "+");
	$replacements = array("(", ")", "%20");
	return str_replace($entities, $replacements, urlencode($string));
}
function startsWith($string, $array) {
    foreach ($array as $key => $prefix) {
        if (strncmp($string, $prefix, strlen($prefix)) === 0) {
            return $key;
        }
    }
    return false;
}

function cookie($key, $val = false) {
	if (!$val) {
		setcookie($key, "", -1);
		if (preg_match("#^(.+)\[(.+)\]$#", $key, $parts))
			unset($_COOKIE[$parts[1]][$parts[2]]);
		else
			unset($_COOKIE[$key]);
	} else {
		setcookie($key, $val, 2147483647);
		if (preg_match("#^(.+)\[(.+)\]$#", $key, $parts))
			$_COOKIE[$parts[1]][$parts[2]] = $val;
		else
			$_COOKIE[$key] = $val;
	}
	global $q;
	$q = "";
}
function aktiv($werte, $extra = []) {
	global $aktiv;
	$return = false;
	if (!is_array($werte))
		$werte = [$werte];
	foreach ($werte as $wert) {
		$wert = strtolower($wert);
		if (in_array("-$wert", $aktiv))
			return 0;
		if (in_array($wert, $aktiv))
			$return = 2;
		if (isset($_COOKIE[strtoupper($wert)]))
			$return = 1;
	}
	if (!empty($extra) AND $extra != []) {
		foreach ($extra as $wert) {
			$wert = strtolower($wert);
			if (in_array($wert, $aktiv))
				return false;
		}
	}
	return $return;
}
function dev() {
	extract($GLOBALS, EXTR_REFS | EXTR_SKIP);
	error_reporting(0);
	echo "<h3>Auswahl</h3><b><a href='?xq=".trim(str_replace("dev", "", $q))."'>$q</a></b><br>Start:\t\t".@$start->name."<br>Ziel:\t\t".@$ziel->name."<br>Tag: $tag ($weekday)<br>Monat: $monat.$jahr<br>Uhrzeit: $stunde:$minute<br>Aufenthalt: ".@$aufenthalt[2]."<br>Ankunft: $ankunft, Bahncard: $bc, <br>heute: $heute, date: $date, morgen: ".strtotime("tomorrow")."<br><pre style='overflow-wrap:anywhere;white-space:pre-wrap'>$url<br><br>$long1/$lat1, $long2/$lat2<br>Ausführungszeit: ";
	echo round((microtime(true) - $time2)*1000) ."ms\n";
	echo date("H:i:s, y-m-d\\\n", $date) . '$stationen:';
	print_r($stationen);
	var_dump($station);
	print_r($aktiv);
	exit;
}