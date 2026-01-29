<?php
$time = time();
$heute = mktime(0,0,0);
$time2 = microtime(true);
date_default_timezone_set("Europe/Berlin");
ob_start();
require "data/kennzeichen.php";
require "db_api.php";		// $db_client, $db_api, $mysqli
require "data/keywords.php";
require "data/functions.php";

$aktiv = $station = [];
$stationen = [[], []];
$q = urldecode($_GET["xq"]);
$ql = strtolower($q);
file_put_contents("log/anfragen.log", date("y-m-d H:i:s   "). $q."\n", FILE_APPEND);

// 1. Spezielle Eingaben prüfen

if (in_array($ql, ["start", "home", "info", "hilfe", "help", "?", " ", ""])) {
	$q = "";
	return $nachricht = "Hallo!";
}
foreach ($keywords->redirects as $redirect => $props) {
	if (in_array($ql, $props)) {
		header("Location: $redirect");
		exit;
	}
}
if (in_array($ql, $keywords->pages)) {          // Sonderseiten KFZ, Leitpunkte ...
	require "redirect/goToPage.php";
}
if (strpos($ql, "=") || strpos($ql, "-") === 0 || in_array($ql, $keywords->props)) {
	require "redirect/saveProperties.php";      // Speichere Variablen/Einstellungen
	return;
}
if (preg_match("#^[\pL]* *[\d]+$#", $ql)) {		// Zuginformation: Weiterleitung zugfinder.net
	require "redirect/zugfinder.php";
}

$mysqli->query("INSERT INTO bahn_stats VALUES ($heute, 1, 1) 
        ON DUPLICATE KEY UPDATE hits = hits + 1".(!isset($_COOKIE["session"]) ? ", 
                                users = users + 1" : ""));

// 2. Anfrage auslesen
require "redirect/readRequest.php";

// 3. Start- und Zielbahnhöfe durchprobieren

$stationen_raw = $stationen;
require "redirect/check.php";
$stationen_temp = $stationen[0];

if (aktiv(["leitpunkt", "l:"]) == 2 AND 	  (check_list("L") || check_list("B"))) {		// Eingabe vor Cookie Betriebsstelle
} elseif (aktiv(["betriebsstelle", "b:"]) AND (check_list("B") || check_list("L"))) {		// nix
} elseif (aktiv(["leitpunkt", "l:"]) AND 	  (check_list("L") || check_list("B"))) {		// nix
} elseif (empty($stationen[1])) {
	check_list("K");
	check($stationen[0]);
	if (count($stationen[0]) > 1 && (!$start || !$ziel))
		check($stationen_temp); // ohne Umwandlung in Abkürzungen
} else {
	check_list("K");
	check_with_ziel($stationen);
}
if (!$ziel && count($stationen[0]) > 1 && !aktiv(["leitpunkt", "l:"]) && !aktiv(["betriebsstelle", "b:"])) {
	echo "LB";
	check_list("L") || check_list("B");
}
if (!$ziel && !$start && isset($lastchance)) {
	echo "LASTCHANCE";
	$start = $station[$lastchance[0]];
	$ziel = $station[$lastchance[1]];
}


// 4. Fahrplanauskunft
if (aktiv($keywords->karte))
	require "redirect/goToMap.php";		// exit

if ($ziel && $start->name != $ziel->name) {
	if (aktiv(["vergleich", "traveluce", "blablacar", "flixbus"])) {
		exit(header("Location: https://www.traveluce.com/search?from=".urlencode($start->name)
			."&to=".urlencode($ziel->name)
			."&date=".date("d.m.Y", $date)));
	}
	
	$bcA = [25  => aktiv(["bc", "bc25", "bahncard", "bahncard25"]),
			50  => aktiv(["bc50", "bahncard50"]),
			100 => aktiv(["bc100", "bahncard100"])];
	foreach ($bcA as $key => $value) {
		if ($value) {
			$bc = $key;
			if ($value == 2)		// Eingabe, nicht nur Cookie
				break;
		}
	}
	$kl		= aktiv(["klasse", "klasse1", "1.klasse"], ["klasse2"]) ? 1 : 2;
	
	if (aktiv($keywords->kalender)) {
		$bc = $bc * 25;
		$stunde = @$match[16];
		$url = "https://bahn.guru/calendar?origin=".urlencode($start->name)."&destination=".urlencode($ziel->name)."&submit=Suchen&class=$kl&bc=$bc&age=A&departureAfter=$stunde&arrivalBefore=&duration=&maxChanges=";
		header("Location: $url");
		exit;
	}

	$hd = date("Y-m-d\TH:i:s", $date);
	$long1 = $start->data->X / 1000000;
	$lat1  = $start->data->Y / 1000000;
	$long2 = $ziel->data->X / 1000000;
	$lat2  = $ziel->data->Y / 1000000;
	$dist = sqrt( pow(($lat2 - $lat1) * 111.13, 2) + pow(($long2 - $long1) * 71.44, 2) );
	$so = myUrlEncode($start->name);
	$zo = myUrlEncode($ziel->name);
	$soei = $start->extId;
	$zoei = $ziel->extId;
	if 		(aktiv("kind"))		$alter = 11;
	elseif	(aktiv("jung"))		$alter = 9;
	elseif	(aktiv("senior"))	$alter = 12;
	else						$alter = 13;
	switch ($bc) {
		case 25: 	$r = "$alter:17:KLASSE_$kl:$personen";		break;
		case 50:	$r = "$alter:23:KLASSE_$kl:$personen";		break;
		case 100:	$r = "$alter:24:KLASSE_$kl:$personen";		break;
		default:	$r = "$alter:16:KLASSENLOS:$personen";
	}
	function bahnhofID($ort, $long, $lat, $id, $noencode = false) {
		global $time;
		$long = str_replace(".", "", $long);
		$lat  = str_replace(".", "", $lat);
		$text = "A=1@O=$ort@X=$long@Y=$lat@U=80@L=$id@B=1@p=$time@";
		return $noencode ? $text : myUrlEncode($text);
	}
	$hz = [];
	foreach ($stationen as $i => $ueber) {
		if ($i < 2 || $i > 3) continue;  // max. 2 Zwischenhalte
		$n = join(" ", $ueber);
		$name = station($n);
		$zeit = isset($aufenthalt[$i]) ? $aufenthalt[$i] : 0;
		$hz[] = [bahnhofID($name, $station[$n]->data->Y, $station[$n]->data->X, $station[$n]->extId, true), 
				$name, $zeit];
	}
	$rest = "hza=$ankunft&hz=".myUrlEncode(json_encode($hz, JSON_UNESCAPED_UNICODE))."&ar=false";
	$rest .= aktiv(["lang", "langsam"]) ? "&s=false" : "&s=true";
	$rest .= aktiv("direkt") ? "&d=true" : "&d=false";
	$nah  = aktiv(["nah", "nahverkehr", "nv"], ["fern", "fernverkehr", "fv"]);
	$bp = strtotime("tomorrow") <= $date 						// Bestpreise nicht für gleichen Tag verfügbar
		&& (aktiv(["bestpreise", "bestpreis", "best", "bp"])	// Bestpreise mit Schlüsselwort
			|| (!$nah && !$uhrzeit && aktiv(["bp", "best", "bestpreise", "bestpreis"]) !== 0));	// Bestpreise ohne "-bp" etc., wenn Uhrzeit angegeben
	$rest .= $bp ? "&bp=true" : "&bp=false";
	if (aktiv(["RAD", "fahrrad"])) {
		$r .= ",3:16:KLASSENLOS:$personen";		// Fahrradticket
		$rest .= "&fm=true";					// Fahrradmitnahme
	} else 
		$rest .= "&fm=false";
	if (isset($aufenthalt[0]))
		$rest .= "&mud=".$aufenthalt[0];
	if ($nah)
		$rest .= "&vm=03,04,05,06,07,08,09";
	else if (aktiv(["fern", "fernverkehr", "fv"]))
		$rest .= "&vm=00,01,02";
	$rest .= aktiv(["dicket", "ticket", "deutschlandticket", "dti"]) ? "&dltv=true" : "&dlt=false";
	$rest .= aktiv("dnah") ? "&dlt=true" : "&dlt=false";
	$soid = bahnhofID($start->name, $long1, $lat1, $soei);
	$zoid = bahnhofID($ziel->name, $long2, $lat2, $zoei);
	
	
	$hash = "Distanz=".round($dist)."km&sts=true&so=$so&zo=$zo&kl=$kl&r=$r&soid=$soid&zoid=$zoid&sot=ST&zot=ST&soei=$soei&zoei=$zoei&hd=$hd&$rest";
	$url = "https://www.bahn.de/buchung/fahrplan/suche#$hash";

	if (aktiv("DEV")) {
		dev();
	}
	
	setcookie("session", $q.time());
	// if ($bp && isset($_COOKIE["session"]))
		// exit ('<script>window.open("'.$url.'","_blank")</script>');
	file_put_contents("log/queries.log", date("y-m-d H:i:s   ").str_pad($q, 28)." $start->name — $ziel->name\n", FILE_APPEND);
	header("Location: $url", true, 301);
	// $meta = '<script>
	// caches.match(window.location.href).then(function(response) { 
		// if (response) 			alert("yess")
		// else					alert("noo")
		// } )
	// window.history.pushState({page: window.location.href}, "", window.location.href);
    // window.location.href = "'.$url.'";</script>';
	exit;
} else {										// Bahnhof
	if (aktiv(["DEV", "dev2"])) {
		var_dump(bahnhof(@$start->name));
		dev();
	}
	if (!$start) {
		$err = error_get_last();
		if ($err)
			$err = " | ".$err["message"] ."(".$err["file"]."; ".$err["type"]."; ".$err["line"];
		file_put_contents("log/error.log", date("y-m-d H:i:s   "). $q.$err."\n", FILE_APPEND);
		if ($nachricht)
			return;
		return $nachricht = "Suchanfrage nicht erkannt.";
	}
	
	if (!($bahnhof = bahnhof($start->name)))
		$bahnhof = str_replace(["ß", "ä", "ö", "ü", "(", ")", " "], ["ss", "ae", "oe", "ue", "-", "-", "-"], mb_strtolower($start->name));
	header("Location: https://www.bahnhof.de/$bahnhof");
	exit;
}

?>