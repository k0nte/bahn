<?php
$tag = date("d");
$monat = date("m");
$jahr  = date("Y");
$stunde = date("H");
$minute = date("i");
$weekday = $date = $uhrzeit = false;
$start = $ziel = null;
$divide = $count = $bc = 0;
$personen = 1;
$aufenthalt = [];
$ankunft = "D";

$kwords = preg_split("#\s+#", $q);
foreach ($kwords as $i => $k) {
	$kcase = trim($k);
	$k = strtolower($kcase);
	$kgross = $kcase == strtoupper($kcase);
	switch ($k) {
		case "": case "uhr": case "am": case "um": case "ab": case "jetzt": case "aktuell": case "abfahrt": case "bahnu": case "aufenthalt": case "aufenthaltsdauer": case "umstieg": case "umstiegszeit": case "umstiegsdauer": // case "der": 
			if (!$kgross)
				continue 2;
			break;
		case "auf": case "an": case "ankunft":
			if ($kgross)						
				break;
			$ankunft = "A";			
			continue 2;
		case "bahnhof":
			$stationen[1] = [""];
			continue 2;
		case "nach": case "–": case "t":
			$divide = 1;			
			continue 2;
		case "über": case "via": case "v": case "ü":
			$divide = $divide > 1 ? $divide + 1 : 2;	
			continue 2;
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
	if (($date OR ($uhrzeit AND !$date AND !$minute)) AND ($key = startsWith($k, $months)) !== false) {
		if ($uhrzeit) {		// Bei "22 jan" wird die Uhrzeit "22" in Tag umgewandelt
			$uhrzeit = false;
			$tag = $stunde;
			$stunde = date("H");
			$minute = date("i");
		}
		$monat = $key + 1;
		$jahr = $monat < date("m") ? date("Y")+1 : date("Y");
		continue;
	}
	
	if (in_array($k, $keywords->props) || (substr($k, 0, 1) == "-" && in_array(substr($k, 1), $keywords->props))) {
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
		} elseif (preg_match("#^(\d{1,2}m)|^(\d{1,2}h)(\d{1,2})?#i", $k, $match3)) {
			// Aufenthaltsdauer für "über" und Umstiegszeit
			$aufenthalt[$divide == 1 ? 0 : $divide] = (intval($match3[1]) + intval(@$match3[3])) + intval(@$match3[2]) * 60;
			if ($divide < 2) {
				if (!$uhrzeit && isset($match3[2]) && !isset($match3[3]) && intval($match3[2]) > 1) {	// z.B. "9h"
					$aufenthalt[0] = 0;
					$stunde = intval($match3[2]);
					$minute = $ankunft == "A" ? 15 : 0;
					$uhrzeit = true;
				} else
					$aufenthalt[0] = min(45, ceil($aufenthalt[0]/5)*5);
			}
		} elseif (preg_match("#^(\d{1,2})p$#i", $k, $match3)) {							// Personen
			$personen = $match3[1];
		} elseif (preg_match("#^\d{3,4}$#", $k, $match3)) {			// Uhrzeit ohne : (z.B. 715 oder 1515)
			$stunde = substr($match3[0], 0, -2);
			$minute = substr($match3[0], -2);
			$uhrzeit = true;
		} else {													// Uhrzeit ohne .
			if (isset($m_uhrzeit) && !isset($m_uhrzeit[4]))
				$minute = $match2[0];		// Bei "22 10" wird 10 zu $minute
			else {
				preg_match("#(\d+)(:(\d+))?#", $k, $m_uhrzeit);
				$stunde = $m_uhrzeit[1];
				$minute = isset($m_uhrzeit[3]) ? $m_uhrzeit[3] : 0;
			}
			if ($ankunft == "A")
				$minute += 15;
			$uhrzeit = true;
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

if (!$uhrzeit AND $date AND isset($_COOKIE["station"]["uhrzeit"])) {
	preg_match("/(\d+)([^\d]+)?(\d+)?/", $_COOKIE["station"]["uhrzeit"], $match);
	$stunde = $match[1];
	$minute = isset($match[3]) ? $match[3] : 0;
	echo "*1,";
} else if (!$date AND mktime($stunde, $minute) < $time)
	$tag++;
if ($weekday)		$date = $weekday + $stunde*3600+$minute*60;
else				$date = mktime($stunde, $minute, 0, $monat, $tag, $jahr);