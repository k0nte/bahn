
# Bahnu
Entwickler: Konstantin Ummen  
[Wenn du mir einen Kaffee ausgeben würdest, würde ich mich freuen!](https://paypal.me/ummen)

## Weitere Eingaben
Die Reihenfolge der Angaben ist grundsätzlich egal, abgesehen von `nach` und `über`. Zeitangaben und Schlüsselwörter können am Anfang oder in der Mitte stehen.  
  
`[leer]`/`start`/`?` etc.: Startseite aufrufen  
`Leitpunkte`, `Betriebsstellen`, `kfz`/`kennzeichen`: Liste aller Leitpunkte bzw. Betriebsstellen bzw. KFZ-Kennzeichen  
`karte`/`radar`/`map` u.ä.: Rufe https://travic.app/ auf (Zug- und Buspositionen in Echtzeit).  
`karte [Bahnhof]`/`radar [Bahnhof]` u.ä.: Zeige https://direkt.bahn.guru (Welche Direktverbindungen gibt es) und https://www.chronotrains.com (Wie weit komme ich in x Stunden)  
`karte [Bahnhof] [Bahnhof]` u.ä.: Zeige Google Maps-Navigation an, leider ohne Zeitangabe. (Die Reihenfolge der Schlüsselwörter ist egal.  
`uhrzeit = 10:30` o.ä.: Gib eine Standarduhrzeit an. Diese wird genutzt, wenn ein Datum angegeben wird, aber keine Uhrzeit (z.B. `Köln Bonn morgen`)  
`bahncard`, `bc`, `bc50`, `bc100`, `nv`, `fv`, `bp`: Bahncards, Nah-/Fernverkehr, Bestpreise  
`mo, di etc.; montag etc.; morgen, mor, übermorgen, gestern, heute; januar, februar etc.; jan, feb etc.`: Zeitangaben  
`über`, `via`, `ü`, `v`: Zwischenhalt  
`nach`, `–` (nicht "-"), `t` (target/termination): Zielort  
`dev`: Details für Entwickler, keine Weiterleitung zu bahn.de

## Bekannte Probleme

Bestpreise funktionieren nur einmal pro Tab-Session. Beim zweiten Mal werden Start und Ziel der vorherigen Suche beibehalten. Ein neuer Tab muss geöffnet werden, um das Problem zu umgehen, oder man klickt beim Bahn-Suchergebnis hin und her.
Vermutlich ist eine bahn.de-interne Weiterleitung nötig.
