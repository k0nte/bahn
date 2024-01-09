<?php
$nachricht = "";
if (isset($_GET["xq"]))
	require "redirect.php";
?>
<html style="font-family: Raleway, Verdana, Sans">
<head>
	<title>Bahnsuche</title>
	<meta name="viewport" content="width=device-width, initial-scale=0.75, minimum-scale=0.75">
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
		body > div, footer, #opts > p { 
			background: #fff3; 
			margin: 30px auto; 
			padding: 12px; width: 70%; 
			font-size: 1.1em;
		}
		footer a, footer a:visited {	color: #23d }
		a:hover {						color: #34e }
		#breit, #schmal { padding: 10px 50px; display: block }
		#schmal { display: none }
		@media (max-width:900px) { 
			#schmal { display: block }
			#breit  { display: none  }
		}
		span, #flex, a {
			font-size: 1.2em;
			margin: auto;
		}
		#flex {
			display: flex;
			width: min(95%, 800px);
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
			color: #000;
			text-decoration: none;
		}
		#opts > div {
		  text-align: left;
		  column-count: 2;
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
		
		/*<?php if (isset($_COOKIE["session"]) AND (isset($_COOKIE["BPFERN"]) OR isset($_COOKIE["BEST"]))) { ?>onsubmit="close()" <?php } ?>*/
		?>
		<form action="./">
			<input id="input" name="xq" style="width: 90%; max-width: 700px; margin: 10px; font-size: 1.2em; border-radius: 4px; padding: 9px" placeholder="<?php
			$placeholders = [		"Berlin Hamburg Freitag 16 Uhr",
									"München Leipzig morgen um 7",
									"Düsseldorf Bremen Bahncard25",
									"Köln Dortmund 18 Uhr Nahverkehr",
									"Frankfurt Hannover am ".date("d.", time()+3600*24*5),
									"Essen Stuttgart ".date("d.m.", time()+3600*24*7)." Bestpreise",
									"Nürnberg Duisburg auf 19 Uhr",
									"Dresden Leipzig 14:30"		];
			echo $placeholders[array_rand($placeholders)];
			?>" value="<?php if (isset($q)) echo $q ?>" />
			<script>
				// Get the input field and the initial placeholder text
				var inputField = document.getElementById('input');
				setTimeout(function() {
					inputField.setAttribute('autofocus', 'autofocus');
					inputField.click()
					inputField.focus()
				}, 100);
				<?php if (isset($_COOKIE["PROMPT"])) { ?>
					setTimeout(function() {
						answer = prompt("Wo soll die Fahrt hingehen?")
						if (answer != null) 
							window.location = "?xq="+answer
					}, 5)
				<?php } ?>
					
				
				var placeholder = inputField.getAttribute('placeholder');
				// Clear the initial placeholder text
				inputField.setAttribute('placeholder', '');

				// Function to set the placeholder text character by character
				function setPlaceholderText(index) {
					inputField.setAttribute('placeholder', placeholder.substring(0, index));

					if (index < placeholder.length) {
						setTimeout(function () {
							setPlaceholderText(index + 1);
						}, 39); // Adjust the time interval to control the speed
					}
				}

				// Call the function to start displaying the placeholder text character by character
				setPlaceholderText(0);
			</script>
		</form>
		<?php 
		foreach ($_COOKIE as $id => $cookie) {
			if (in_array($id, ["station", "session"]))
				continue;
			echo "<a class='opt' href='?xq=-$id' title='Entfernen'>$id</a>";
		}
		echo '<div style="display:inline-block;width:30px"></div>';
		if (isset($_COOKIE["station"]))
			foreach ($_COOKIE["station"] as $var => $station) {
				echo "<a class='opt' href='?xq=-$var' title='Entfernen'>$var: $station</a>";
			}
		?>
		<div style="display:inline-block;width:30px"></div>
		<a class='opt' href="#" onclick="document.getElementById('opts').hidden = !document.getElementById('opts').hidden; return false">⚙</a>
		<div id="opts">
			<p>Wähle Einstellungen, die für alle zukünftigen Suchen gespeichert werden. Willst du bei einer Suche die Einstellung ignorieren, schreibe „-Einstellung“, also z.B. „-nah“.</p>
			<div>
			<?php 
				$opts = ["BC25" => "Bahncard 25", "NAH" => "Nur Nahverkehr", "BEST" => "Bestpreise", "RAD" => "Mit Fahrrad", "LANG" => "Auch langsame Verbindungen", "KLASSE" => "1. Klasse", "BPFERN" => "Bestpreise ab 120km außer bei NAH", "PROMPT" => "Zeige die Tastatur bei Mobilgeräten sofort"];
				foreach ($opts as $key => $desc) {
					echo "<a class='opt' href='?xq=$key'>$key</a> $desc<br>";
				}
			?>
			</div>
		</div>
		<script>
			document.getElementById('opts').hidden = true
		</script>
	</div>
	<div>
		<h1>Mögliche Eingaben</h1>
		<div id="flex">
			<div>Von A nach B</div>
			<div><code><b>Münster nach Frankfurt
Münster Frankfurt
Münster Nord Frankfurt Süd</b></code></div>
			<div>KFZ-Kennzeichen</div>
			<div><code><b>ms f</b>         <i>= Münster nach Frankfurt</i></code></div>
			<div>Zeitpunkt</div>
			<div>
				<code><b>Köln Bonn Dienstag</b>  <i>= nächsten Dienstag</i>
<b>Köln Bonn am 3.</b>     <i>= am 3. (z.B. 3.10.)</i>
<b>Köln Bonn 3.10.</b>     <i>= am 3. Oktober</i>
<b>Köln Bonn 15:30</b>     <i>= um 15:30 Uhr</i>
<b>Köln Bonn auf 15h</b>   <i>= Ankunft 15 Uhr</i></code>
			</div>
			<div>Einstellungen<!--<br>(werden für nächsten <br>Aufruf gespeichert)--></div>
			<div>
				<code><b>Bahncard25</b> / <b>bc</b>     <i>= mit Bahncard 25</i>
<b>Nahverkehr</b> / <b>nah</b>    <i>= nur Nahverkehr</i>
<b>Bestpreise</b> / <b>best</b>   <i>= Bestpreise anzeigen</i>
<b>Fahrrad</b>    / <b>rad</b>    <i>= mit Fahrrad</i>
<b>Langsam</b>    / <b>lang</b>   <i>= auch langsame Verb.</i>
<b>Klasse</b>              <i>= 1. Klasse</i></code>
			</div>
			<div>Beispiele</div>
			<div><code><b>Rostock nach Hamburg Freitag 16h Nah</b>  <i>= nächsten Freitag um 16 Uhr im Nahverkehr</i>
<b>B S 2. 15 bc</b> <i>= Berlin nach Stuttgart am 2. um 15 Uhr mit Bahncard25</i></code>
			</div>
			<div>Bahnhöfe speichern</div>
			<div><code><b>var = Baunatal Guntershausen</b>
<i>Der Bahnhof „Baunatal Guntershausen“ wird als „var“ für zukünftige Suchen gespeichert.</i></code>
			</div>
			<div>Weitere Suchen</div>
			<div><code><b>ICE 722</b>      <i>= Zuginformationen</i>
<b>Köln</b> / <b>k</b>     <i>= Bahnhofsinformationen</i>
<b>A B Kalender</b> <i>= Bestpreiskalender</i></code>
			</div>
		</div>
	</div>
	<div>
		<h1>Tipps</h1>
		<p><span id="breit" title="URL: https://bahn.ummen.tk/?xq=%s">Klicke Rechtsklick auf das Suchfeld und dann „Suchmaschine hinzufügen“ o.ä. – dadurch kannst du direkt über deinen Browser diese Suche benutzen.</span>
		<span id="schmal">Tippe auf das Suchfeld und halte gedrückt – dort findest du eine Option, um diese Suche als Suchmaschine zu deinem Browser hinzuzufügen.</span></p>
		<p><span>Um herauszufinden, wie pünktlich ein Zug im letzten Monat war, kopiere die Zugkennung aus deinen Bahnergebnissen. Bei Regionalbahnen ist dies die lange Zahl unter Details, z.B. 10266 für RB 38.</span></p> 
	</div>
	<footer>
		<h1>Urheber</h1>
		<a href="http://ummen.tk/">Konstantin Ummen</a> | <a href="https://github.com/k0nte/bahn">Github</a><br>
		<pre style="font-size: 1.1em">http://bahn.ummen.tk/?xq=[Suchwert]</pre>
	</footer>
		
</body>
</html>
<?php
// DB Client Secret
// 9cc94c471e26d163d680f4dae065b264
?>