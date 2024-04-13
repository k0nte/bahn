<?php
$time = time();
$heute = mktime(0,0,0);
$time2 = microtime(true);
date_default_timezone_set("Europe/Berlin");
require "kennzeichen.php";
require "db_api.php";

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
	echo "<h3>Auswahl</h3><b>$q</b><br>Start:\t\t".@$start->name."<br>Ziel:\t\t".@$ziel->name."<br>Tag: $tag ($weekday)<br>Monat: $monat.$jahr<br>Uhrzeit: $stunde:$minute<br>Aufenthalt: ".@$aufenthalt[2]."<br>Ankunft: $ankunft, Bahncard: $bc, <br>heute: $heute, date: $date, morgen: ".strtotime("tomorrow")."<br><pre style='overflow-wrap:anywhere;white-space:pre-wrap'>$url<br>$long1/$lat1, $long2/$lat2<br>Ausführungszeit: ";
	echo round((microtime(true) - $time2)*1000) ."ms\n";
	echo date("H:i:s, y-m-d\\\n", $date) . '$stationen:';
	print_r($stationen);
	var_dump($station);
	print_r($aktiv);
	exit;
}

$aktiv = $station = [];
$stationen = [[], []];
$q = urldecode($_GET["xq"]);
$ql = strtolower($q);
file_put_contents("anfragen.log", date("y-m-d H:i:s   "). $q."\n", FILE_APPEND);
$months = ["jan", "feb", "mär", "apr", "mai", "juni", "juli", "aug", "sep", "okt", "nov", "dez"];
$karte = ["map", "karte", "radar", "zugradar", "zugverfolgung", "züge", "echtzeit"];
$kalender = ["kalender", "kalendar", "calendar", "guru"];
$props = ["bc", "bc25", "bc50", "bc100", "bahncard", "bahncard25", "bahncard50", "bahncard100", "nah", "nv", "nahverkehr", "klasse", "1.klasse", "klasse1", "klasse2", "rad", "lang", "langsam", "best", "bestpreis", "bestpreise", "bp", "bpfern", "prompt", "dev", "dev2", "kalender", "kalendar", "calendar", "fern", "fernverkehr", "fv", "direkt", "leitpunkt", "l:", "betriebsstelle", "b:"];
$props = array_merge($props, $karte, $kalender);


if (in_array($ql, ["start", "home", "info", "hilfe", "help", "?", " ", ""])) {
	$q = "";
	return $nachricht = "Hallo!";
}
if (in_array($ql, $karte)) {
	header("Location: https://travic.app/?ol=orm_infra");
	exit;
}
if (in_array($ql, ["leitpunkte", "betriebsstellen", "kfz", "kennzeichen"])) {
	require "punkte.php";
	if ($ql == "kfz")
		$ql = "kennzeichen";
	echo '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head>
	<body style="background: rgb(255,207,192,1); width:100%;margin:0">';
	echo '<h1>'.ucfirst($ql).'</h1><dl style="columns: 14em; width: 90%; margin: auto">';
	ksort($$ql);
	foreach ($$ql as $kurz => $lang) 
		echo "<dt>$kurz</dt><dd>$lang</dd>\n";
	echo "</dl></body>";
	exit;
} 							// Speichere Variablen/Einstellungen
if (strpos($ql, "=") || strpos($ql, "-") === 0 || in_array($ql, $props)) {
	$data = explode("=", $ql);
	$true = (count($data) == 1 || in_array($data[1], ["true", "wahr", "1"])) ? true : 
			(in_array($data[1], ["false", "falsch", "0", ""]) ? false : null);
	if (strpos($ql, "-") === 0)
		$true = false;
	$key = str_replace(["-", " "], "", $data[0]);
	$value = isset($data[1]) ? trim($data[1]) : null;
	if (in_array($key, $props)) {
		$replacements = ["bahncard25" => "bc25",		"bahncard" => "bc25",		"bc" => "bc25",
						"bahncard50" => "bc50",			"bahncard100"=>"bc100",		"best" => "bestpreise",
						"bestpreis" => "bestpreise",	"lang" => "langsam",		"fahrrad" => "rad",			
						"nah" => "nahverkehr",			"nv" => "nahverkehr",
						"fern" => "fernverkehr", 		"fv" => "fernverkehr"];
		foreach ($replacements as $a => $b)
			if ($key == $a)
				$key = $b;
		if ($true) {
			cookie(strtoupper($key), "true");
			$nachricht = "<i>$key</i> für zukünftige Suchen gespeichert. ";
			foreach (["bc25" => "bc50", "nah" => "fern"] as $key2 => $value) {
				if 	   ($key == $key2)	cookie(strtoupper($value));
				elseif ($key == $value) cookie(strtoupper($key2));
			}
		} elseif ($true === false) {
			cookie(strtoupper($key));
			$nachricht = "Einstellung <i>$key</i> entfernt. ";
		}
	} else {
		if (!preg_match("#^[\pL]+$#ui", $key)) {
			$nachricht = "Die Abkürzung darf nur Buchstaben enthalten!";
		} else if ($true !== false) {
			$value = str_replace("-", " ", $value);
			cookie("station[$key]", $value);
			$nachricht = "Bahnhof \"$value\" als \"$key\" abgespeichert.";
		} else {
			cookie("station[$key]");
			cookie(strtoupper($key));
			$nachricht = "Abkürzung \"$key\" gelöscht.";
		}
	}
	return;
}
if (preg_match("#^[\pL]* *[\d]+$#", $ql)) {		// Zuginformation
	$postdata = http_build_query(
		array(		'lang' => 'de',
					'zugnr' => $q			)		);
	$context = stream_context_create(array('http' =>
		array(
			'method'  => 'POST',
			'header'  => 'Content-Type: application/x-www-form-urlencoded
sec-ch-ua: "Chromium";v="116", "Not)A;Brand";v="24", "Google Chrome";v="116"
sec-ch-ua-mobile: ?0
sec-ch-ua-platform: "Windows"
origin: https://www.zugfinder.net
user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/116.0.0.0 Safari/537.36
accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7
sec-fetch-site: same-origin
sec-fetch-mode: navigate
accept-language: de-DE,de;q=0.9,en-US;q=0.8,en;q=0.7,el;q=0.6,fr;q=0.5,es;q=0.4,lb;q=0.3',
			'content' => $postdata
		)
	));
	$result = get_headers('https://www.zugfinder.net/search.php', true, $context);
	if (is_array($result["Location"]) || !$result["Location"])
		return $nachricht = "Zug nicht erkannt.";
	header("Location: ".$result["Location"]);
	exit;
}

$mysqli->query("INSERT INTO bahn_stats VALUES ($heute, 1, 1) ON DUPLICATE KEY UPDATE hits = hits + 1".(!isset($_COOKIE["session"]) ? ", users = users + 1" : ""));

// Bahnhöfe usw. auslesen
$tag = date("d");
$monat = date("m");
$jahr  = date("Y");
$stunde = date("H");
$minute = date("i");
$weekday = $date = $timeday = false;
$start = $ziel = null;
$divide = $count = $bc = 0;
$aufenthalt = [];
$ankunft = "D";

$keywords = preg_split("#[\s\-]+#", $q);
foreach ($keywords as $i => $k) {
	$kcase = trim($k);
	$k = strtolower($kcase);
	$kgross = $kcase == strtoupper($kcase);
	switch ($k) {
		case "": case "uhr": case "am": case "um": case "ab": case "jetzt": case "aktuell": case "abfahrt": case "bahnu": case "aufenthalt": case "aufenthaltsdauer": // case "der": 
			if (!$kgross)					continue 2;
			break;
		case "auf": case "an": case "ankunft":
			if ($kgross)					break;
			$ankunft = "A";					continue 2;
		case "nach": case "–":
			$divide = 1;					continue 2;
		case "über": case "via": 
			$divide = $divide > 1 ? $divide + 1 : 2;	continue 2;
		case "mo": case "di": case "mi": case "do": case "fr": case "sa": case "so": 
			if ($i < 2 || $kgross)			break;
		case "montag": case "dienstag": case "mittwoch": case "donnerstag": case "freitag": case "samstag": case "sonntag": case "mon": case "die": case "mit": case "don": case "fri": case "sam": case "son":
			$k = substr($k, 0, 2);
		case "morgen": case "mor": case "übermorgen": case "gestern": case "heute":
			if ($k == "mor")
				$k = "morgen";
			$replace = ["mo" => "Monday", "di" => "Tuesday", "mi" => "Wednesday", "do" => "Thursday", "fr" => "Friday", "sa" => "Saturday", "so" => "Sunday", "morgen" => "tomorrow", "gestern" => "yesterday", "übermorgen" => "+2 days midnight", "heute" => "today"];
			$next = in_array($k, ["übermorgen", "morgen", "gestern"]) ? "" : "next ";
			$weekday = strtotime($next.$replace[$k]);
			$date = true;
			continue 2;
	}
	// Monate werden erkannt nach Datum oder "Nur-Stunden-Uhrzeit"
	if (($date OR ($timeday AND !$date AND !$minute)) AND ($key = startsWith($k, $months)) !== false) {	
		if ($timeday) {		// Bei "22 jan" wird die Uhrzeit "22" in Tag umgewandelt
			$timeday = false;
			$tag = $stunde;
			$stunde = date("H");
			$minute = date("i");
		}
		$monat = $key + 1;
		$jahr = $monat < date("m") ? date("Y")+1 : date("Y");
		continue;
	}
	
	if (in_array($k, $props) || (substr($k, 0, 1) == "-" && in_array(substr($k, 1), $props))) {
		$aktiv[] = $k;											// Eigenschaften "Rad" etc.
		continue;
	}
	if (preg_match("#\d+#", $k, $match2)) {						// Tag oder Uhrzeit
		if (preg_match("#(\d{1,2})\.((\d{1,2})(\.)?(\d{2,4})?)?#", $k, $match)) {		// Datum mit . 
			$tag	= $match[1];
			$monat  = isset($match[3]) ? $match[3] : ( $tag < date("d") ? date("m")+1 : date("m") );
			$jahr   = isset($match[5]) ? (strlen($match[5]) == 2 ? "20$match[5]" : $match[5])
					: ( $monat < date("m") ? date("Y")+1 : date("Y") );
			$date = true;
		} elseif ($divide > 1 && preg_match("#^(\d{1,2}m)|^(\d{1,2}h)(\d{1,2})?#", $k, $match3)) {
			// Aufenthaltsdauer für "über"
			$aufenthalt[$divide] = (intval($match3[1]) + intval(@$match3[3])) + intval(@$match3[2]) * 60;
		} else {																		// Uhrzeit ohne .
			if (isset($uhrzeit) && !isset($uhrzeit[4]))
				$minute = $match2[0];
			else {
				preg_match("#(\d+)(:(\d+))?#", $k, $uhrzeit);
				$stunde = $uhrzeit[1];
				$minute = isset($uhrzeit[3]) ? $uhrzeit[3] : 0;
			}
			if ($ankunft == "A")
				$minute += 15;
			$timeday = true;
		}
		continue;
	}
	
	// Station
	$s = $kcase;
	if (isset($_COOKIE["station"]) && array_key_exists($k, $_COOKIE["station"])) {
		$s = $_COOKIE["station"][$k];				// Gespeicherte Variablen
		if ($i)
			$divide = 1;		// Abkürzungen ab der zweiten Stelle werden als Ziel behandelt
	} else if (strpos($k, ":")) {
		$parts = explode(":", $k);
		switch ($parts[0]) {
			case "l": 
				require_once "punkte.php";
				if (isset($leitpunkte[strtoupper($parts[1])]))
					$s = $leitpunkte[strtoupper($parts[1])];
				break;
			case "b":
				require_once "punkte.php";
				if (isset($betriebsstellen[strtoupper($parts[1])]))
					$s = $betriebsstellen[strtoupper($parts[1])];
				break;
		}
	} else if (isset($ersetzungen[$k]))
		$s = $ersetzungen[$k];
	$stationen[$divide][] = str_replace(["+", "#", ".", ",", "–", " "], "", $s);
}

if (!$timeday AND $date AND isset($_COOKIE["station"]["uhrzeit"])) {
	preg_match("/(\d+)([^\d]+)?(\d+)?/", $_COOKIE["station"]["uhrzeit"], $match);
	$stunde = $match[1];
	$minute = isset($match[3]) ? $match[3] : 0;
	echo "*1,";
} else if (!$date AND mktime($stunde, $minute) < $time)
	$tag++;
if ($weekday)		$date = $weekday + $stunde*3600+$minute*60;
else				$date = mktime($stunde, $minute, 0, $monat, $tag, $jahr);


$stationen_raw = $stationen;
require "re_check.php";
$stationen_temp = $stationen[0];
if (aktiv($karte)) {
	ob_start();
	check_with_ziel($stationen);
	ob_clean();
	$id = $start->data->L;
	$x =  $start->data->X;
	$y =  $start->data->Y;
	require "maps.php";
	exit;
}

if (aktiv(["leitpunkt", "l:"]) == 2 AND 	  (check_list("L") || check_list("B"))) {		// Eingabe vor Cookie Betriebsstelle
} elseif (aktiv(["betriebsstelle", "b:"]) AND (check_list("B") || check_list("L"))) {		// nix
} elseif (aktiv(["leitpunkt", "l:"]) AND 	  (check_list("L") || check_list("B"))) {		// nix
} elseif (empty($stationen[1])) {
	check_list("K");
	check($stationen[0]);
	if (!$start || !$ziel)
		check($stationen_temp); // ohne Umwandlung in Abkürzungen
} else {
	check_list("K");
	check_with_ziel($stationen);
}
if (!$ziel && !aktiv(["leitpunkt", "l:"]) && !aktiv(["betriebsstelle", "b:"])) {
	check_list("L") || check_list("B");
}
if (!$ziel && !$start && isset($lastchance)) {
	$start = $station[$lastchance[0]];
	$ziel = $station[$lastchance[1]];
}

if ($ziel && $start->name != $ziel->name) {								// Fahrplanauskunft
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
	
	if (aktiv($kalender)) {
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
	switch ($bc) {
		case 25: 	$r = "13:17:KLASSE_$kl:1";		break;
		case 50:	$r = "13:23:KLASSE_$kl:1";		break;
		case 100:	$r = "13:24:KLASSE_$kl:1";		break;
		default:	$r = "13:16:KLASSENLOS:1";
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
	$bp = strtotime("tomorrow") <= $date 		// Bestpreise nicht für gleichen Tag verfügbar
		&& ((!$nah && $dist > 120 && aktiv("BPFERN") && aktiv(["bp", "best", "bestpreise", "bestpreis"]) !== 0) || aktiv(["BESTPREISE", "bestpreis", "BEST", "bp"]));
	$rest .= $bp ? "&bp=true" : "&bp=false";
	if (aktiv(["RAD", "fahrrad"])) {
		$r .= ",3:16:KLASSENLOS:1";
		$rest .= "&fm=true";		// Fahrradmitnahme
	} else 
		$rest .= "&fm=false";
	if ($nah)
		$rest .= "&vm=03,04,05,06,07,08,09";
	else if (aktiv(["fern", "fernverkehr", "fv"]))
		$rest .= "&vm=00,01,02";
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
		dev();
	}
	if (!$start) {
		$err = error_get_last();
		if ($err)
			$err = " | ".$err["message"] ."(".$err["file"]."; ".$err["type"]."; ".$err["line"];
		file_put_contents("error.log", date("y-m-d H:i:s   "). $q.$err."\n", FILE_APPEND);
		if ($nachricht)
			return;
		return $nachricht = "Suchanfrage nicht erkannt.";
	}
	
	$bahnhof = strtolower(str_replace(["ß", "ä", "ö", "ü", "(", ")", " "], ["ss", "ae", "oe", "ue", "-", "-", "-"], $start->name));
	header("Location: https://www.bahnhof.de/$bahnhof");
	exit;
}

?>