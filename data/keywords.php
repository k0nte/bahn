<?php
$keywords = new stdClass();
$keywords->pages =		["leitpunkte", "betriebsstellen", "kfz", "kennzeichen"];
$keywords->months =		["jan", "feb", "mär", "apr", "mai", "juni", "juli", "aug", "sep", "okt", "nov", "dez"];
$keywords->karte =		["map", "karte", "radar", "zugradar", "zugverfolgung", "echtzeit"];
$keywords->kalender =	["kalender", "kalendar", "calendar", "guru"];
$keywords->props =		["bc", "bc25", "bc50", "bc100", "bahncard", "bahncard25", "bahncard50", "bahncard100", "nah", "nv", "nahverkehr", "klasse", "1.klasse", "klasse1", "klasse2", "rad", "lang", "langsam", "best", "bestpreis", "bestpreise", "bp", "fern", "fernverkehr", "fv", "direkt", "prompt", "dev", "dev2", "kalender", "kalendar", "calendar", "vergleich", "leitpunkt", "l:", "betriebsstelle", "b:", "dnah", "ticket", "dticket", "deutschlandticket", "dti", "kind", "jung", "normal", "senior"];
$keywords->props = array_merge($keywords->props, $keywords->karte, $keywords->kalender);

$keywords->redirects = [
	"https://travic.app/?ol=orm_infra"					=> $keywords->karte,
	"https://abo.bahn.de/#/home"						=> ["abo", "abos", "aboportal"],
	"https://www.bahn.de/buchung/reiseuebersicht" 		=> ["reise", "reisen", "meine reise", "tickets", "ticket", "tic", "rei", "buchungen", "entschädigung", "storno", "stornieren", "ticket stornieren", "buchung stornieren"],
	"https://www.bahn.de"								=> ["bahn", "bahn.de"],
	"https://www.bahn.de/service/ueber-uns/fundservice" => ["fundsachen", "fundsache", "fundbüro", "fundservice"]
];

$keywords->replacements = 
		["bahncard25" => "bc25",		"bahncard" => "bc25",		"bc" => "bc25",
		"bahncard50" => "bc50",			"bahncard100"=>"bc100",		"best" => "bestpreise",
		"bestpreis" => "bestpreise",	"lang" => "langsam",		"fahrrad" => "rad",			
		"nah" => "nahverkehr",			"nv" => "nahverkehr",
		"fern" => "fernverkehr", 		"fv" => "fernverkehr"];

$keywords->pairs = [["bc25", "bc50", "bc100"],
		["nahverkehr", "fernverkehr", "dnah"],
		["kind", "jung", "normal", "senior"]];