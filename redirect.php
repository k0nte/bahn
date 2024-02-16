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
			return false;
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


$context = stream_context_create([
	"http" => [
		"header" => "Accept: application/vnd.de.db.ris+json
DB-Client-Id: $db_client
DB-Api-Key: $db_api"
	] ]);

$aktiv = $station = [];
$stationen = [[], []];
$q = urldecode($_GET["xq"]);
$ql = strtolower($q);
file_put_contents("anfragen.log", date("y-m-d H:i:s   "). $q."\n", FILE_APPEND);
$props = ["bc", "bc25", "bc50", "bc100", "bahncard", "bahncard25", "bahncard50", "bahncard100", "nah", "klasse", "1.klasse", "klasse1", "klasse2", "rad", "lang", "langsam", "best", "bestpreis", "bestpreise", "bp", "bpfern", "prompt", "dev", "dev2", "kalender", "kalendar", "calendar", "fern", "fernverkehr", "direkt", "leitpunkt", "l:", "betriebsstelle", "b:"];


if (in_array($ql, ["start", "home", "info", "hilfe", "help", "?", " ", ""])) {
	$q = "";
	return $nachricht = "Hallo!";
}
if (in_array($ql, ["map", "karte", "radar", "zugradar", "zugverfolgung"])) {
	header("Location: https://travic.app/");
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
						"nah" => "nahverkehr",			"fern" => "fernverkehr"];
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
$weekday = $date = false;
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
		case "nach":
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
		} elseif ($divide > 1 && preg_match("#(\d{1,2}h)?(\d{1,2}m)|(\d{1,2}h)$#", $k, $match)) {
			// Aufenthaltsdauer für "über"
			$aufenthalt[$divide] = (intval($match[1]) + (isset($match[3]) ? intval($match[3]) : 0)) * 60 + intval($match[2]);
		} else {																		// Uhrzeit ohne .
			if (isset($uhrzeit) && !isset($uhrzeit[3]))
				$minute = $match2[0];
			else {
				preg_match("#(\d+)(:(\d+))?#", $k, $uhrzeit);
				$stunde = $uhrzeit[1];
				$minute = isset($uhrzeit[3]) ? $uhrzeit[3] : 0;
			}
			if ($ankunft == "A")
				$minute += 15;
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
	$stationen[$divide][] = str_replace(["+", "#", ".", ","], "", $s);
}

if (!$date AND mktime($stunde, $minute) < $time)
	$tag++;
if ($weekday)		$date = $weekday + $stunde*3600+$minute*60;
else				$date = mktime($stunde, $minute, 0, $monat, $tag, $jahr);


$stationen_raw = $stationen;
require "re_check.php";
if (aktiv(["leitpunkt", "l:"]) == 2 AND 	  (check_list("L") || check_list("B"))) {		// Eingabe vor Cookie Betriebsstelle
} elseif (aktiv(["betriebsstelle", "b:"]) AND (check_list("B") || check_list("L"))) {		// nix
} elseif (aktiv(["leitpunkt", "l:"]) AND 	  (check_list("L") || check_list("B"))) {		// nix
} elseif (empty($stationen[1])) {
	$stationen_temp = $stationen[0];
	check_list("K");
	check($stationen[0]);
	if (!$start && !$ziel)
		check($stationen_temp); // ohne Umwandlung in Abkürzungen
} else {
	check_list("K");
	check_with_ziel($stationen);
}
if (!$ziel && !aktiv(["leitpunkt", "l:"]) && !aktiv(["betriebsstelle", "b:"])) {
	check_list("L") || check_list("B");
}

if ($ziel && $start != $ziel) {								// Fahrplanauskunft
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
	
	if (aktiv(["kalender", "kalendar", "calendar"])) {
		$bc = $bc * 25;
		$stunde = @$match[16];
		$url = "https://bahn.guru/calendar?origin=".urlencode($start)."&destination=".urlencode($ziel)."&submit=Suchen&class=$kl&bc=$bc&age=A&departureAfter=$stunde&arrivalBefore=&duration=&maxChanges=";
		header("Location: $url");
		exit;
	}

	$hd = date("Y-m-d\TH:i:s", $date);
	$long1 = $start_station->position->longitude;
	$lat1  = $start_station->position->latitude;
	$long2 = $ziel_station->position->longitude;
	$lat2  = $ziel_station->position->latitude;
	$dist = sqrt( pow(($lat2 - $lat1) * 111.13, 2) + pow(($long2 - $long1) * 71.44, 2) );
	$so = myUrlEncode($start);
	$zo = myUrlEncode($ziel);
	$soei = $start_station->evaNumber;
	$zoei = $ziel_station->evaNumber;
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
		$zeit = isset($aufenthalt[$i]) ? $aufenthalt[$i] : 10;
		$hz[] = [bahnhofID($name, $station[$n]->position->longitude, $station[$n]->position->latitude, $station[$n]->evaNumber, true), 
				$name, $zeit];
	}
	$rest = "hza=$ankunft&hz=".myUrlEncode(json_encode($hz, JSON_UNESCAPED_UNICODE))."&ar=false";
	$rest .= aktiv(["LANG", "langsam"]) ? "&s=false" : "&s=true";
	$rest .= aktiv("direkt") ? "&d=true" : "&d=false";
	$nah  = aktiv(["NAH", "nahverkehr"], ["FERN", "fernverkehr"]);
	$bp = strtotime("tomorrow") <= $date 		// Bestpreise nicht für gleichen Tag verfügbar
		&& ((!$nah && $dist > 120 && aktiv("BPFERN")) || aktiv(["BESTPREISE", "bestpreis", "BEST", "bp"]));
	$rest .= $bp ? "&bp=true" : "&bp=false";
	if (aktiv(["RAD", "fahrrad"])) {
		$r .= ",3:16:KLASSENLOS:1";
		$rest .= "&fm=true";		// Fahrradmitnahme
	} else 
		$rest .= "&fm=false";
	if ($nah)
		$rest .= "&vm=03,04,05,06,07,08,09";
	$soid = bahnhofID($start, $long1, $lat1, $soei);
	$zoid = bahnhofID($ziel, $long2, $lat2, $zoei);
	
	
	$hash = "Distanz=".round($dist)."km&sts=true&so=$so&zo=$zo&kl=$kl&r=$r&soid=$soid&zoid=$zoid&sot=ST&zot=ST&soei=$soei&zoei=$zoei&hd=$hd&$rest";
	$url = "https://www.bahn.de/buchung/fahrplan/suche#$hash";

	if (aktiv("DEV")) {
		echo "<br> $q<br><br>Start: $start<br>Ziel: $ziel<br>Tag: $tag<br>Monat: $monat.$jahr<br>Stunde: $stunde<br>Minute: $minute<br>Ankunft: $ankunft, Bahncard: $bc, <br>heute: $heute, date: $date, morgen: ".strtotime("tomorrow")."<br>$url<br>Ausführungszeit: ";
		echo round((microtime(true) - $time2)*1000) ."ms\n<pre>";
		echo date("H:i:s, y-m-d\\\n", $date);
		print_r($stationen);
		var_dump($station);
		print_r($aktiv);
		exit;
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
		echo "$q<br><br>Start: $start<br>Ziel: $ziel<br>Tag: $tag<br>Monat: $monat.$jahr<br>Stunde: $stunde<br>Minute: $minute<br>Ankunft: $ankunft<pre>\n";
		echo (microtime(true) - $time2)."ms\n";
		echo date("H:i:s, y-m-d", $date);
		print_r($stationen);
		echo '$station: ';
		var_dump($station);
		exit;
	}
	if (!$start) {
		file_put_contents("error.log", date("y-m-d H:i:s   "). $q."\n", FILE_APPEND);
		if ($nachricht)
			return;
		return $nachricht = "Suchanfrage nicht erkannt.";
	}
	$start = $start_station->names->DE->nameLong;
	
	$bahnhof = strtolower(str_replace(["ß", "ä", "ö", "ü", "(", ")", " "], ["ss", "ae", "oe", "ue", "-", "-", "-"], $start));
	header("Location: https://www.bahnhof.de/$bahnhof");
	exit;
}

?>