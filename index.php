<?php
$nachricht = "";
if (isset($_GET["q"]))
	require "redirect.php";
?>
<html style="font-family: Raleway, Verdana, Sans">
<head>
	<title>Bahnsuche</title>
	<meta name="viewport" content="width=device-width, initial-scale=0.7, minimum-scale=0.7">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<style>
		code { 
			background: #eee; 
			display: block; 
			padding: 4px 10px; 
			white-space: pre-wrap;
			max-width: calc(95vw - 42px);
			margin: auto;
		}
		body > div { 
			background: #fff3; 
			margin: 30px auto; 
			padding: 12px; width: 70%; 
		}
		a, a:visited { color: #23d}
		#schmal { display: none }
		@media (max-width:900px) { 
			#schmal { display: block }
			#breit  { display: none  }
		}
		span, #flex {
			font-size: 1.2em;
			margin: auto;
		}
		#flex {
			display: flex;
			width: min(95%, 750px);
			flex-wrap: wrap;
			align-items: center;
			justify-content: center;
			gap: 10px;
		}
		#flex > div {
			min-width: 700px;
		}
		#flex > div:nth-child(2n+1) {
			flex-basis: 30%;
			font-weight: bold;
			min-width: 200px;
		}
		#flex > div:nth-child(2n) {
			flex-basis: 60%;
			text-align: left;
			min-width: 23em;
		}
		.opt {
			border-radius: 5px;
			border: 1px solid #95c;
			background: #e9d5f5;
			margin: 4px;
			padding: 4px 8px;
			display: inline-block;
		}
	</style>
</head>
<body style="background: linear-gradient(177deg, rgba(198,106,126,1) 0%, rgba(212,160,198,1) 35%, rgba(255,207,192,1) 100%); width:100%;
  margin: 0; display: flex; flex-flow: column; justify-content: center;text-align: center">
	<div style="margin-top: 200px">
		<div>
			<h1>Bahn-Verbindungssuche</h1>
		</div>
		<?php 
		if ($nachricht)
			echo "<span style='font-size:1.4em; color:#800'>$nachricht</span>";
		?>
		<form action="./">
			<input id="input" name="q" style="width: 90%; max-width: 700px; margin: 10px; font-size: 1.2em; border-radius: 4px; padding: 9px" placeholder="Berlin Hamburg 19 Uhr" value="<?php if (isset($q)) echo $q ?>" />
			<script>document.getElementById("input").focus()</script>
		</form>
		<?php 
		foreach ($_COOKIE as $id => $cookie) {
			if ($id == "station") 
				continue;
			echo "<span class='opt'>$id</span>";
		}
		echo '<div style="display:inline-block;width:30px"></div>';
		if (isset($_COOKIE["station"]))
			foreach ($_COOKIE["station"] as $var => $station) {
				echo "<span class='opt'>$var: $station</span>";
			}
		?>
	</div>
	<div>
		<h1>Mögliche Eingaben</h1>
		<div id="flex">
			<div>Von A nach B</div>
			<div><code><b>Münster nach Frankfurt
Münster Nord-Frankfurt Süd
Münster Frankfurt</b></code></div>
			<div>KFZ-Kennzeichen</div>
			<div><code><b>ms f</b>         <i>= Münster nach Frankfurt</i></code></div>
			<div>Zeitpunkt</div>
			<div>
				<code><b>Köln Bonn Dienstag</b>  <i>= nächsten Dienstag</i>
<b>Köln Bonn am 3.</b>     <i>= am 3. (z.B. 3.10.)</i>
<b>Köln Bonn 3.10.</b>     <i>= am 3. Oktober</i>
<b>Köln Bonn 15:30</b>     <i>= um 15:30 Uhr</i>
<b>Köln Bonn 15h</b>       <i>(oder 15)</i>
<b>Köln Bonn auf 15h</b>   <i>= Ankunft 15 Uhr</i></code>
			</div>
			<div>Einstellungen<!--<br>(werden für nächsten <br>Aufruf gespeichert)--></div>
			<div>
				<code><b>bahncard25</b> / <b>bc25</b>   <i>= mit Bahncard 25</i>
<b>Nahverkehr</b> / <b>NAH</b>    <i>= nur Nahverkehr</i>
<b>Fahrrad</b> / <b>RAD</b>       <i>= mit Fahrrad</i><!--<b>Bestpreis</b> / <b>BEST</b>-->
<b>Langsam</b> / <b>LANG</b>      <i>= auch langsame Verb.</i>
<b>Klasse</b>              <i>= 1. Klasse</i></code>
			</div>
			<div>Beispiele</div>
			<div><code><b>Rostock nach Hamburg Freitag 16h NAH</b>
<b>B S 2. 15 bc</b> <i>= Berlin nach Stuttgart am 2. um 15 Uhr mit Bahncard25</i></code>
			</div>
			<div>Speichern für zukünftige Suchen</div>
			<div><code><b>var = Lüchtringen</b>
<i>Der Bahnhof wird unter „var“ gespeichert und kann bei zukünftigen Suchen verwendet werden.</i>
<b>BC25</b> / <b>NAH</b> / <b>RAD</b> / <b>LANG</b> / <b>KLASSE</B>
<i>= Merke BC25 etc. für zukünftige Suchen</i>
<b>-BC25</b> / …    <i>= Einstellung/Bahnhof entfernen</i>
</code>
			</div>
			<div>Weitere Suchen</div>
			<div><code><b>ICE 722</b>      <i>= Zuginformationen</i>
<b>Köln</b> / <b>k</b>     <i>= Bahnhofsinformationen</i>
<b>A B Kalender</b> <i>= Bestpreiskalender</i></code>
			</div>
		</div>
	</div>
	<div>
		<h1>Tipp</h1>
		<span id="breit">Klicke Rechtsklick auf das Suchfeld und dann „Suchmaschine hinzufügen“ o.ä. – dadurch kannst du direkt über deinen Browser diese Suche benutzen.</span>
		<span id="schmal">Tippe auf das Suchfeld und halte gedrückt – dort findest du eine Option, um diese Suche als Suchmaschine zu deinem Browser hinzuzufügen.</span>
	</div>
	<div>
		<h1>Urheber</h1>
		<a href="http://ummen.tk/">Konstantin Ummen</a> | <a href="https://github.com/k0nte/bahn" target="_blank">Github</a>
	</div>
		
</body>
</html>
<?php
// DB Client Secret
// 9cc94c471e26d163d680f4dae065b264
?>