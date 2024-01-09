<?php
$time = time();
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
			$return = true;
		if (isset($_COOKIE[strtoupper($wert)]))
			$return = true;
	}
	if (!empty($extra) AND $empty != []) {
		foreach ($extra as $wert) {
			$wert = strtolower($wert);
			if (in_array($wert, $aktiv))
				return false;
		}
	}
	return $return;
}

function station($name) {
	global $station, $nachricht, $context, $mysqli;
	$sql_name = $mysqli->escape_string($name);
	$sql = $mysqli->query("SELECT name, data FROM bahn_cache WHERE query = '$sql_name'");
	if ($sql) 
		$sql = $sql->fetch_object();
	if (!isset($sql->name)) {
		$file = file_get_contents("https://apis.deutschebahn.com/db-api-marketplace/apis/ris-stations/v1/stop-places/by-name/".urlencode($name), false, $context);
		$s = json_decode($file)->stopPlaces;
		$mysqli->query("INSERT INTO bahn_cache VALUES ('$sql_name', '$file')");
	} else {
		$s = json_decode($mysqli->data)->stopPlaces;
	}
	// $si1 = $name == strtoupper($name) ? 1 : 0;
	if (!isset($s[0])) {
		$nachricht = "Bahnhof nicht gefunden! ($name)";
		return null;
	}
	$station[$name] = $s[0];
	return $s[0]->names->DE->nameLong;
}

$context = stream_context_create([
	"http" => [
		"header" => "Accept: application/vnd.de.db.ris+json
DB-Client-Id: $db_client
DB-Api-Key: $db_api"
	] ]);


$q = urldecode($_GET["xq"]);
$props = ["bc", "bc25", "bc50", "bahncard", "bahncard25", "nah", "klasse", "1.klasse", "klasse1", "klasse2", "rad", "lang", "langsam", "best", "bestpreis", "bestpreise", "bpfern", "prompt", "dev", "kalender", "kalendar", "calendar", "fern", "fernverkehr", "direkt"];


if (in_array(strtolower($q), ["start", "home", "info", "hilfe", "help", "?", " "])) {
	$q = "";
	return $nachricht = "Hallo!";
} 							// Speichere Variablen/Einstellungen
if (strpos($q, "=") || strpos($q, "-") === 0 || in_array(strtolower($q), $props)) {
	$data = explode("=", $q);
	$true = (count($data) == 1 || in_array($data[1], ["true", "wahr", "1"])) ? true : 
			(in_array($data[1], ["false", "falsch", "0", ""]) ? false : null);
	if (strpos($q, "-") === 0)
		$true = false;
	$key = str_replace(["-", " "], "", $data[0]);
	$keyi = strtolower($key);
	$value = isset($data[1]) ? trim($data[1]) : null;
	if (in_array($keyi, $props)) {
		if (in_array($keyi, ["bahncard25", "bahncard", "bc"]))		$key = "bc25";
		elseif (in_array($keyi, ["best", "bestpreis"]))				$key = "Bestpreise";
		elseif ($keyi == "lang")									$key = "langsam";
		elseif ($keyi == "fahrrad")									$key = "rad";
		elseif ($keyi == "nahverkehr")								$key = "nah";
		if ($true) {
			cookie(strtoupper($key), "true");
			$nachricht = "<i>$key</i> für zukünftige Suchen gespeichert. ";
			if ($key == "bc25")		cookie("BC50");
			if ($key == "bc50")		cookie("BC25");
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
			$nachricht = "Abkürzung \"$key\" gelöscht.";
		}
	}
	return;
}
if (preg_match("#^[\pL]* *[\d]{2,}$#", $q)) {		// Zug
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

// Bahnhöfe usw. auslesen
$aktiv = [];
$stationen = [[], []];
$station = [];
$tag = date("d");
$monat = date("m");
$jahr  = date("Y");
$stunde = date("H");
$minute = date("i");
$weekday = $date = false;
$divide = $count = 0;
$ankunft = "D";

$keywords = preg_split("#\s+#", $q);
foreach ($keywords as $k) {
	$k = strtolower(trim($k));
	if (!$k)
		continue;
	switch ($k) {
		case "uhr": case "am": 			continue 2;
		case "auf": case "an":
			$ankunft = "A";				continue 2;
		case "nach":
			$divide = 1;				continue 2;
		case "mo": case "di": case "mi": case "do": case "fr": case "sa": case "so": 
		case "montag": case "dienstag": case "mittwoch": case "donnerstag": case "freitag": case "samstag": case "sonntag": case "mon": case "die": case "mit": case "don": case "fri": case "sam": case "son":
			$k = substr($k, 0, 2);
		case "morgen": case "mor": case "übermorgen": case "gestern": case "heute":
			if ($k == "mor")
				$k = "morgen";
			$replace = ["mo" => "Monday", "di" => "Tuesday", "mi" => "Wednesday", "do" => "Thursday", "fr" => "Friday", "sa" => "Saturday", "so" => "Sunday", "morgen" => "tomorrow", "gestern" => "yesterday", "übermorgen" => "+2 days midnight"];
			$next = in_array($k, ["übermorgen", "morgen", "gestern"]) ? "" : "next ";
			$weekday = strtotime($next.$replace[$k]);
			continue 2;
	}
	if (in_array($k, $props) || (substr($k, 0, 1) == "-" && in_array(substr($k, 1), $props))) {
		$aktiv[] = $k;										// Eigenschaften "Rad" etc.
		continue;
	}
	if (preg_match("#\d#", $k)) {							// Tag oder Uhrzeit
		if (preg_match("#(\d{1,2})\.((\d{1,2})(\.)?(\d{2,4})?)?#", $k, $match)) {		// Mit .
			$tag	= $match[1];
			$monat  = isset($match[3]) ? $match[3] : ( $tag < date("d") ? date("m")+1 : date("m") );
			$jahr   = isset($match[5]) ? (strlen($match[5]) == 2 ? "20$match[5]" : $match[5])
					: ( $monat < date("m") ? date("Y")+1 : date("Y") );
		} else {																		// Ohne .
			preg_match("#(\d+)(:(\d+))?#", $k, $match);
			$stunde = $match[1];
			$minute = isset($match[3]) ? $match[3] : 0;
		}
		continue;
	}
	
	// Station
	if (isset($_COOKIE["station"]) && array_key_exists($k, $_COOKIE["station"])) {
		$k = $_COOKIE["station"][$k];				// Gespeicherte Variablen
		$count++;
	} else if (strlen($k) <= 3 && isset($kennzeichen[mb_strtoupper($k)])) {
		$k = $kennzeichen[mb_strtoupper($k)];		// KFZ-Kennzeichen
		$count++;
	}
	if ($count == 2) 				// Wenn zwei Abkürzungen erkannt wurden, z.B. "ms f", ist die zweite die zweite Station
		$divide = 1;
	$stationen[$divide][] = $k;
}

if ($weekday)		$date = $weekday + $stunde*3600+$minute*60;
else				$date = mktime($stunde, $minute, 0, $monat, $tag, $jahr);

if (empty($stationen[1])) {
	$count = count($stationen[0]);
	$proba = $start = $ziel = [];
	for ($i=0; $i<=$count; $i++) {
		$s = join(" ", array_slice($stationen[0], 0, $count-$i));
		$e = join(" ", array_slice($stationen[0], $count-$i));
		if ($s) {
			$proba[$s] = similar_text(station($s), $s);
			if ($proba[$s])
				$start[$i] = $s;
		}
		if ($e) {
			$proba[$e] = similar_text(station($e), $e);
			if ($proba[$e])
				$ziel[$i] = $e;
		}
	}
	$highest_proba = 0;
	foreach ($start as $key => $name) {
		if (!isset($ziel[$key]))
			continue;
		$value = $proba[$name]/strlen($name) + $proba[$ziel[$key]]/strlen($ziel[$key]);
		if ($value > $highest_proba) {
			$highest_proba = $value;
			$i = $key;
		}
	}
	if ($highest_proba) {
		$ziel_station = $station[$ziel[$i]];
		$ziel = $ziel_station->names->DE->nameLong;
	} else {
		$start[$i] = join($stationen[0]);
		$ziel  = null;
	}
	$start_station = $station[$start[$i]];
	$start = $start_station->names->DE->nameLong;
} else {
	$start = station(join($stationen[0]));
	$start_station = $station[join($stationen[0])];
	$ziel = station(join($stationen[1]));
	$ziel_station = $station[join($stationen[1])];
}

if ($ziel) {								// Fahrplanauskunft
	$bc 	= aktiv(["bc", "bc25", "bahncard", "bahncard25"]) ? 25 : aktiv(["bc50", "bahncard50"]) ? 50 : 0;
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
		default:	$r = "13:16:KLASSENLOS:1";
	}
	$nah  = aktiv(["NAH", "nahverkehr"], ["FERN", "fernverkehr"]);
	$rest = "hza=$ankunft&ar=false&hz=%5B%5D";
	$rest .= aktiv("direkt") ? "&d=true" : "&d=false";
	$rest .= aktiv(["LANG", "langsam"]) ? "&s=false" : "&s=true";
	$bp = (!$nah && $dist > 120 && aktiv("BPFERN")) || aktiv(["BESTPREISE", "bestpreis", "BEST"]);
	$rest .= $bp ? "&bp=true" : "&bp=false";
	if (aktiv(["RAD", "fahrrad"])) {
		$r .= ",3:16:KLASSENLOS:1";
		$rest .= "&fm=true";		// Fahrradmitnahme
	} else 
		$rest .= "&fm=false";
	if ($nah)
		$rest .= "&vm=03,04,05,06,07,08,09";
	$long = str_replace(".", "", $long1);
	$lat  = str_replace(".", "", $lat1);
	$soid = myUrlEncode("A=1@O=$start@X=$long@Y=$lat@U=80@L=$soei@B=1@p=$time@");
	$long = str_replace(".", "", $long2);
	$lat  = str_replace(".", "", $lat2);
	$zoid  = myUrlEncode("A=1@O=$ziel@X=$long@Y=$lat@U=80@L=$zoei@B=1@p=$time@");
	$sotzot = "sot=ST&zot=ST";
	
	if (aktiv("DEV")) {
		echo "<br><br>Start: $start ($match[2])<br>Ziel: $ziel ($match[4])<br>Tag: $tag<br>Monat: $monat.$jahr<br>Stunde: $stunde<br>Minute: $minute<br>Ankunft: $ankunft<pre>";
		echo "Dist: $dist, Nah: BC25:\n";
		var_dump($nah);
		var_dump($bc25);
		echo $rest;
		echo (microtime(true) - $time2)*1000 ."ms\n";
		echo date("H:i:s, y-m-d\\\n", $date);
		print_r($stationen);
		print_r($aktiv);
		var_dump($station);
		exit;
	}
	
	$hash = "Distanz=".round($dist)."km&sts=true&so=$so&zo=$zo&kl=$kl&r=$r&soid=$soid&zoid=$zoid&$sotzot&soei=$soei&zoei=$zoei&hd=$hd&$rest";
	$url = "https://www.bahn.de/buchung/fahrplan/suche#$hash";
	
	setcookie("session", "true");
	// if ($bp && isset($_COOKIE["session"]))
		// exit ('<script>window.open("'.$url.'","_blank")</script>');
	header("Location: $url");
	exit;
} else {										// Bahnhof
	$file = file_get_contents("https://apis.deutschebahn.com/db-api-marketplace/apis/ris-stations/v1/stop-places/by-name/".urlencode($q), false, $context);
	$station = json_decode($file)->stopPlaces;
	if (!isset($station[0])) {
		if (aktiv("dev")) {
			echo "<br><br>Start: $start ($match[2])<br>Ziel: $ziel ($match[4])<br>Tag: $tag<br>Wochentag: $wotag<br>Monat: $monat.$jahr<br>Stunde: $stunde<br>Minute: $minute<br>Ankunft: $ankunft<pre>\n";
			echo (microtime(true) - $time2)."ms\n";
			echo date("H:i:s, y-m-d", $date);
			print_r($stationen);
			print_r($aktiv);
			var_dump($station);
			exit;
		}
		if ($nachricht)
			return;
		return $nachricht = "Suchanfrage nicht erkannt.";
	}
	$start = $station[0]->names->DE->nameLong;
	
	$bahnhof = strtolower(str_replace(["ß", "ä", "ö", "ü", "(", ")", " "], ["ss", "ae", "oe", "ue", "-", "-", "-"], $start));
	header("Location: https://www.bahnhof.de/$bahnhof");
	exit;
}

// echo "<pre>";
//print_r($station2[0]);
//echo "https://www.bahn.de/buchung/fahrplan/suche#$hash";
//print_r($match);
// echo "</pre>";
//var_dump($match);
?>