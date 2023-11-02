<?php
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

$context = stream_context_create([
	"http" => [
		"header" => "Accept: application/vnd.de.db.ris+json
DB-Client-Id: $db_client
DB-Api-Key: $db_api"
	] ]);
	
$q = urldecode($_GET["q"]);
$q1 = preg_replace("#[ \-]+$#", "", 			str_replace(["NAH", "RAD", "LANG", "BEST"], "", 
		str_ireplace(["bc25", "bahncard25", "bc50", "bahncard50", "bc", "nahverkehr", "klasse", "fahrrad", "langsam", "bestpreise", "bestpreis", "kalender"], "", $q)));

# (^von *)
preg_match("#(([\pL\/ ]+)( *\- *| +nach +| +n +)([\pL\/ ]+?)|([\pL\/]+) +([\pL\/]+))( *(\d{1,2})\.((\d{1,2})(\.)?(\d{2,4})?)?| (am )?(gestern|übermorgen|morgen|mo|di|mi|do|fr|sa|so)\pL*)?( *(auf|an)? *(\d{1,2}) *(h|uhr)?([,.: ](\d{1,2}))?)?[ \d\.\-]*$#ui", $q1, $match);
$start  = @$match[2] ? trim(@$match[2]) : trim(@$match[5]);
$ziel   = @$match[4] ? trim(@$match[4]) : trim(@$match[6]);

$keywords = ["bc", "bc25", "bc50", "bahncard", "bahncard25", "nah", "klasse", "rad", "lang", "best"];

												// Speichere Variablen/Einstellungen
if (strpos($q, "=") || strpos($q, "-") === 0 || in_array(strtolower($q), $keywords)) {	
	$data = explode("=", $q);
	$true = (count($data) == 1 || in_array($data[1], ["true", "wahr", "1"])) ? true : 
			(in_array($data[1], ["false", "falsch", "0", ""]) ? false : null);
	if (strpos($q, "-") === 0)
		$true = false;
	$key = str_replace(["-", " "], "", $data[0]);
	$keyi = strtolower($key);
	$value = isset($data[1]) ? trim($data[1]) : null;
	if (in_array($keyi, $keywords)) {
		if (in_array($keyi, ["bahncard25", "bahncard", "bc"]))
			$key = "bc25";
		elseif (in_array($keyi, ["best", "bestpreis"]))
			$key = "Bestpreise";
		if ($true) {
			cookie(strtoupper($key), "true");
			$nachricht = "<i>$key</i> für zukünftige Suchen gespeichert. ";
		} elseif ($true === false) {
			cookie(strtoupper($key));
			$nachricht = "Einstellung <i>$key</i> entfernt. ";
		}
	} else {
		if (!preg_match("#^[\pL]+$#ui", $key)) {
			$nachricht = "Die Abkürzung darf nur Buchstaben enthalten!";
		} else if ($true !== false) {
			cookie("station[$key]", $value);
			$nachricht = "Bahnhof \"$value\" als \"$key\" abgespeichert.";
		} else {
			cookie("station[$key]");
			$nachricht = "Abkürzung \"$key\" gelöscht.";
		}
	}
} elseif ($ziel) {								// Fahrplanauskunft
	function aktiv($wert, $auchwert, $extra = "") {
		global $q;
		if ($auchwert)
			$auchwert = "|$auchwert";
		if (preg_match("#\-($wert$auchwert)#", $q))
			return false;
		if (preg_match("#[^\-]($wert$auchwert)#", $q))
			return true;
		if ($extra && preg_match("#$extra#", $q))
			return false;
		return (bool) $_COOKIE[$wert];
	}

	$start_parts = explode(" ", $start);
	foreach ($start_parts as $key => $part) {
		if (isset($_COOKIE["station"]) && array_key_exists($part, $_COOKIE["station"]))
			$start_parts[$key] = $_COOKIE["station"][$start];
		if (strlen($start_parts[$key]) <= 3)
			$start_parts[$key] = isset($kennzeichen[mb_strtoupper($start_parts[$key])]) ? $kennzeichen[mb_strtoupper($start_parts[$key])] : $start_parts[$key];
	}
	$start = join(" ", $start_parts);

	$ziel_parts = explode(" ", $ziel);
	foreach ($ziel_parts as $key => $part) {
		if (isset($_COOKIE["station"]) && array_key_exists($part, $_COOKIE["station"]))
			$ziel_parts[$key] = $_COOKIE["station"][$ziel];
		if (strlen($ziel_parts[$key]) <= 3)
			$ziel_parts[$key] = isset($kennzeichen[mb_strtoupper($ziel_parts[$key])]) ? $kennzeichen[mb_strtoupper($ziel_parts[$key])] : $ziel_parts[$key];
	}
	$ziel = join(" ", $ziel_parts);

	$file = file_get_contents("https://apis.deutschebahn.com/db-api-marketplace/apis/ris-stations/v1/stop-places/by-name/".urlencode($start), false, $context);
	$station = json_decode($file)->stopPlaces;
	if (!isset($station[0]))
		return $nachricht = "Start nicht gefunden!";
	$start = $station[0]->names->DE->nameLong;
	
	$file = file_get_contents("https://apis.deutschebahn.com/db-api-marketplace/apis/ris-stations/v1/stop-places/by-name/".urlencode($ziel), false, $context);
	$station2 = json_decode($file)->stopPlaces;
	if (!isset($station2[0]))
		return $nachricht = "Ziel nicht gefunden!";
	$ziel = $station2[0]->names->DE->nameLong;
	
	$bc25 	= aktiv("BC25", "(?i)bc25|(?i)bahncard|(?i)bc$");
	$kl		= aktiv("KLASSE", "(?i)klasse|(?i)class", "(?i)klasse2") ? 1 : 2;
	
	if (preg_match("#kalend(a|e)r#i", $q)) {
		$bc = $bc25 ? 2 : 0;
		$stunde = @$match[16];
		$url = "https://bahn.guru/calendar?origin=".urlencode($start)."&destination=".urlencode($ziel)."&submit=Suchen&class=$kl&bc=$bc&age=A&departureAfter=$stunde&arrivalBefore=&duration=&maxChanges=";
		header("Location: $url");
		exit;
	}
		
	$ankunft = @$match[16] == "auf" ? "A" : "D";
	$stunde  = @$match[17] ? @$match[17] : date("H");
	$minute  = @$match[19] ? @$match[19] : (@$match[17] ? 0 : date("i"));
	$tag	 = @$match[8]  ? @$match[8]  : ( $stunde < date("H") ? date("d")+1 : date("d") );
	$monat   = @$match[10] ? @$match[10] : ( $tag < date("d") && !@$match[8] ? date("m")+1 : date("m") );
	$jahr    = @$match[12] ? (strlen($match[12]) == 2 ? "20$match[12]" : @$match[12])
			: ( $monat < date("m") && !@$match[12] ? date("Y")+1 : date("Y") );
	$wotag   = @$match[14];

	if ($wotag) {
		$replace = ["mo" => "Monday", "di" => "Tuesday", "mi" => "Wednesday", "do" => "Thursday", "fr" => "Friday", "sa" => "Saturday", "so" => "Sunday", "morgen" => "tomorrow", "gestern" => "yesterday", "übermorgen" => "+2 days midnight"];
		$next = in_array(mb_strtolower($wotag), ["übermorgen", "morgen", "gestern"]) ? "" : "next ";
		$date = strtotime($next.$replace[mb_strtolower($wotag)])+$stunde*60*60+$minute*60;
	} else {
		$date = mktime($stunde, $minute, 0, $monat, $tag, $jahr);
	}
	$hd = date("Y-m-d\TH:i:s", $date);
	
	$so = myUrlEncode($start);
	$zo = myUrlEncode($ziel);
	$soei = $station[0]->evaNumber;
	$zoei = $station2[0]->evaNumber;
	$r  = $bc25 ? "13:17:KLASSE_$kl:1" : "13:16:KLASSENLOS:1"; //  			13:17:KLASSE_1:1			13:23:KLASSE_2:1		13:23:KLASSE_1:1
	$rest = "hza=$ankunft&ar=false&d=false&hz=%5B%5D&bp=false";
	$rest .= aktiv("LANG", "(?i)langsam") ? "&s=false" : "&s=true";
	$rest .= aktiv("BEST", "(?i)bestpreis") ? "&bp=true" : "&bp=false";
	if (aktiv("RAD", "(?i)fahrrad")) {
		$r .= ",3:16:KLASSENLOS:1";
		$rest .= "&fm=true";
	}
	if (aktiv("NAH", "(?i)nahverkehr", "FERN|(?i)fernverkehr"))
		$rest .= "&vm=03,04,05,06,07,08,09";
	$long = str_replace(".", "", $station[0]->position->longitude);
	$lat  = str_replace(".", "", $station[0]->position->latitude);
	$soid = myUrlEncode("A=1@O=$start@X=$long@Y=$lat@U=80@L=$soei@B=1@p=1698259482@");
	$long = str_replace(".", "", $station2[0]->position->longitude);
	$lat  = str_replace(".", "", $station2[0]->position->latitude);
	$zoid  = myUrlEncode("A=1@O=$ziel@X=$long@Y=$lat@U=80@L=$zoei@B=1@p=1698251742@");
	$sotzot = "sot=ST&zot=ST";
	
	// echo "<br><br>Start: $start<br>Ziel: $ziel<br>Tag: $tag<br>Wochentag: $wotag<br>Monat: $monat.$jahr<br>Stunde: $stunde<br>Minute: $minute<br>Ankunft: $ankunft<pre>";
	// var_dump($match);
	// exit;
	
	$hash = "sts=true&so=$so&zo=$zo&kl=$kl&r=$r&soid=$soid&zoid=$zoid&$sotzot&soei=$soei&zoei=$zoei&hd=$hd&$rest";
	$url = "https://www.bahn.de/buchung/fahrplan/suche#$hash";
	
	header("Location: $url");
	exit;
} else if (preg_match("#^[\pL]* *[\d]{2,}$#", $q)) {		// Zug
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
} else {										// Bahnhof
	$file = file_get_contents("https://apis.deutschebahn.com/db-api-marketplace/apis/ris-stations/v1/stop-places/by-name/".urlencode($q), false, $context);
	$station = json_decode($file)->stopPlaces;
	if (!isset($station[0]))
		 return $nachricht = "Suchanfrage nicht erkannt.";
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