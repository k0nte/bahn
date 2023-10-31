<?php
if (isset($_GET["q"]))
	require "redirect.php";
?>
<html style="font-family: Raleway, Verdana, Sans">
<head>
	<title>Bahnsuche</title>
	<meta name="viewport" content="width=device-width, initial-scale=0.7, minimum-scale=0.7">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<style>
		code { background: #eee; display: block; padding: 4px 10px; }
		body > div { background: #fff3; margin: 30px auto; padding: 12px; width: 70%; }
		a, a:visited { color: #23d}
		#schmal { display: none }
		@media (max-width:900px) { 
			#schmal { display: block }
			#breit  { display: none  }
		}
		span, table {
			font-size: 1.2em;
			margin: auto;
		}
	</style>
</head>
<body style="background: linear-gradient(177deg, rgba(198,106,126,1) 0%, rgba(212,160,198,1) 35%, rgba(255,207,192,1) 100%); width:100%;
  margin: 0; display: flex; flex-flow: column; justify-content: center;text-align: center">
	<div style="margin-top: 200px">
		<div>
			<h1>Bahn-Verbindungssuche</h1>
		</div>
		<form action="./">
			<input id="input" name="q" style="width: 90%; max-width: 700px; margin: 10px; font-size: 1.2em; font-family: Cambria; padding: 9px" placeholder="Berlin Hamburg 19 [Uhr]" />
			<script>document.getElementById("input").focus()</script>
		</form>
	</div>
	<div>
		<h1>Mögliche Eingaben</h1>
		<table>
			<tr>
				<th>Von A nach B</th>
				<td><code>Münster nach Frankfurt<br>Münster-Frankfurt<br>Münster Frankfurt</code></td>
			</tr>
			<tr>
				<th>KFZ-Kennzeichen</th>
				<td><code>ms f &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i>= Münster nach Frankfurt</i></code></td>
			</tr>
			<tr>
				<th>Zeitpunkt</th>
				<td><code>Köln Bonn Dienstag  &nbsp;<i>= nächsten Dienstag</i><br>
					Köln Bonn am 3.  &nbsp;&nbsp;&nbsp;&nbsp;<i>= z.B. am 3. Oktober</i><br>
					Köln Bonn 3.10.  &nbsp;&nbsp;&nbsp;&nbsp;<i>= am 3. Oktober</i><br>
					Köln Bonn 15:30  &nbsp;&nbsp;&nbsp;&nbsp;<i>= um 15:30 Uhr</i><br>
					Köln Bonn 15h  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i>oder 15</i><br>
				</td>
			</tr>
			<tr>
				<th>Einstellungen<!--<br>(werden für nächsten <br>Aufruf gespeichert)--></th>
				<td><code>bahncard25&nbsp;&nbsp; <i>= mit Bahncard 25</i><br>
					bc25 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i>= mit Bahncard 25</i><br>
					Nahverkehr &nbsp;&nbsp;<i>= nur Nahverkehr</i><br>
					NAH    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i>= nur Nahverkerh</i><br>
					Klasse &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i>= 1. Klasse</i></code>
				</td>
			</tr>
			<tr>
				<th>Beispiele</th>
				<td><code>Rostock nach Hamburg Freitag 16h NAH<br>
				B S 2. 15 bc <i>= Berlin nach Stuttgart am <br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;2. um 15 Uhr mit Bahncard25</i><br>
				</code></td>
			</tr>
			<tr>
				<th>Weitere Suchen</th>
				<td><code>ICE 722 &nbsp;&nbsp;<i>= Zuginformationen</i><br>
				Köln &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i>= Bahnhofsinformationen</i><br>
				k &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<i>= Bahnhofsinformationen Köln</i></code>
		</table>
	</div>
	<div>
		<h1>Tipp</h1>
		<span id="breit">Klicke Rechtsklick auf das Suchfeld und dann „Suchmaschine hinzufügen“ o.ä. – dadurch kannst du direkt über deinen Browser diese Suche benutzen.</span>
		<span id="schmal">Tippe auf das Suchfeld und halte gedrückt – dort findest du eine Option, um diese Suche als Suchmaschine zu deinem Browser hinzuzufügen.</span>
	</div>
	<div>
		<h1>Urheber</h1>
		<a href="http://ummen.tk/">Konstantin Ummen</a>
	</div>
		
</body>
</html>
<?php
// DB Client Secret
// 9cc94c471e26d163d680f4dae065b264
?>