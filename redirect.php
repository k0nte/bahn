<?php
date_default_timezone_set("Europe/Berlin");
require "kennzeichen.php";
require "db_api.php";
$context = stream_context_create([
		"http" => [
			"header" => "Accept: application/vnd.de.db.ris+json
DB-Client-Id: $db_client
DB-Api-Key: $db_api"
		] ]);
	
$q = urldecode($_GET["q"]);
preg_match("#([\pL\/]+)( *\-| nach| n| ) *([\pL\/]+)( *(\d{1,2})\.((\d{1,2})(\.)?)?| (am )?(mo|di|mi|do|fr|sa|so)\pL*?)?( *(\d{1,2})([,.:h ](\d{1,2}))?)?#ui", $q, $match);
$start  = trim(@$match[1]);
$ziel   = trim(@$match[3]);
if (!$ziel) {
	preg_match("#^([\pL\/]+) (\pL+)#ui", $q, $match2);
	$start = $match2[1];
	$ziel  = $match2[2];
}
$stunde = @$match[12] ? @$match[12] : date("H");
$tag	= @$match[5]  ? @$match[5]  : ($stunde < date("H") ? date("d")+1 : date("d"));
$monat  = @$match[7]  ? @$match[7]  : ($tag < date("d") ? date("m")+1 : date("m"));
$wotag  = @$match[10];
$minute = @$match[14] ? @$match[14] : (@$match[12] ? 0 : date("i"));

$bc25 = preg_match("#(bc *25|bahn *card *25|bc$)#ui", $q);
$klasse1 = preg_match("#(klasse|class)#ui", $q);
$nahverkehr = preg_match("#(NAH|(?i)nahverkehr)#", $q);

if (strlen($start) <= 3)
	$start = isset($kennzeichen[mb_strtoupper($start)]) ? $kennzeichen[mb_strtoupper($start)] : $start;
if (strlen($ziel) <= 3)
	$ziel = isset($kennzeichen[mb_strtoupper($ziel)]) ? $kennzeichen[mb_strtoupper($ziel)] : $ziel;

if ($ziel) {		// Fahrplanauskunft
	function myUrlEncode($string) {
		$entities = array('%28', '%29', "+");
		$replacements = array("(", ")", "%20");
		return str_replace($entities, $replacements, urlencode($string));
	}

	$file = file_get_contents("https://apis.deutschebahn.com/db-api-marketplace/apis/ris-stations/v1/stop-places/by-name/".urlencode($start), false, $context);
	$station = json_decode($file)->stopPlaces;
	$start = $station[0]->names->DE->nameLong;
	
	
	$file = file_get_contents("https://apis.deutschebahn.com/db-api-marketplace/apis/ris-stations/v1/stop-places/by-name/".urlencode($ziel), false, $context);
	$station2 = json_decode($file)->stopPlaces;
	$ziel = $station2[0]->names->DE->nameLong;
	
	$so = myUrlEncode($start);
	$zo = myUrlEncode($ziel);
	$soei = $station[0]->evaNumber;
	$zoei = $station2[0]->evaNumber;
	$kl = $klasse1 ? 1 : 2;
	$r  = $bc25 ? "13:17:KLASSE_".($klasse1 ? 1 : 2).":1" : "13:16:KLASSENLOS:1"; //  			13:17:KLASSE_1:1			13:23:KLASSE_2:1		13:23:KLASSE_1:1
	$long = str_replace(".", "", $station[0]->position->longitude);
	$lat  = str_replace(".", "", $station[0]->position->latitude);
	$soid = myUrlEncode("A=1@O=$start@X=$long@Y=$lat@U=80@L=$soei@B=1@p=1698259482@");
	$long = str_replace(".", "", $station2[0]->position->longitude);
	$lat  = str_replace(".", "", $station2[0]->position->latitude);
	$zoid  = myUrlEncode("A=1@O=$ziel@X=$long@Y=$lat@U=80@L=$zoei@B=1@p=1698251742@");
	$sotzot = "sot=ST&zot=ST";
	
	if ($wotag) {
		$replace = ["mo" => "Monday", "di" => "Tuesday", "mi" => "Wednesday", "do" => "Thursday", "fr" => "Friday", "sa" => "Saturday", "so" => "Sunday"];
		$date = strtotime("next ".$replace[strtolower($wotag)])+$stunde*60*60+$minute*60;
	} else {
		echo "hi<br>";
		$date = mktime($stunde, $minute, 0, $monat, $tag);
		// if ($date < time())
			// $date = mktime($stunde, $minute, 0, $monat+1, $tag);
	}
	
	$hd = date("Y-m-d\TH:i:s", $date);
	$rest = "hza=D&ar=false&s=true&d=false&hz=%5B%5D&fm=false&bp=false";
	if ($nahverkehr)
		$rest .= "&vm=03,04,05,06,07,08,09";
	$hash = "sts=true&so=$so&zo=$zo&kl=$kl&r=$r&soid=$soid&zoid=$zoid&$sotzot&soei=$soei&zoei=$zoei&hd=$hd&$rest";
	$url = "https://www.bahn.de/buchung/fahrplan/suche#$hash";
	
	header("Location: $url");
	exit;
} else if (preg_match("#\d{2,}#", $q)) {		// Zug
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
	header("Location: ".$result["Location"]);
	exit;
	// var_dump($result);
} else {										// Bahnhof
	$file = file_get_contents("https://apis.deutschebahn.com/db-api-marketplace/apis/ris-stations/v1/stop-places/by-name/".urlencode($q), false, $context);
	$station = json_decode($file)->stopPlaces;
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
	echo "<br><br>Start: $start<br>Ziel: $ziel<br>Tag: $tag<br>Wochentag: $wotag<br>Monat: $monat<br>Stunde: $stunde<br>Minute: $minute<br>hd: $hd - $date - ".time();
	// exit("<script>location.hash = ''</script>");
	?>