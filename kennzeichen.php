<?php
$ersetzungen = [
	"höxter" => "Höxter Rathaus",
	// "rothenburg" => "Rothenburg ob der Tauber bahnhof",
];
$uc_only = ["bad", "ob", "hb"];
$kennzeichen = ["A" => "Augsburg",
"AA" => "Aalen Ostalbkreis",
"AB" => "Aschaffenburg",
"ABI" => "Anhalt-Bitterfeld",
"ABG" => "Altenburger Land",
"AC" => "Aachen",
"AE" => "Auerbach",
"AIC" => "Aichach-Friedberg",
"AK" => "Altenkirchen/Westerwald",
"AM" => "Amberg",
"AN" => "Ansbach",
"AÖ" => "Altötting",
"AP" => "Apolda - Weimarer Land",
"AS" => "Amberg-Sulzbach",
"AUR" => "Aurich",
"AW" => "Bad Neuenahr-Ahrweiler",
"AZ" => "Alzey-Worms",
"B" => "Berlin",
"BA" => "Bamberg",
"BAD" => "Baden-Baden",
"BAR" => "Barnim",
"BB" => "Böblingen",
"BC" => "Biberach/Riß",
"BGL" => "Berchtesgadener Land",
"BI" => "Bielefeld",
"BIR" => "Birkenfeld/Nahe und Idar-Oberstein",
"BIT" => "Bitburg-Prüm",
"BK" => "Bochum",
"BL" => "Zollernalbkreis / Balingen",
"BLK" => "Burgenlandkreis",
"BM" => "Erftkreis / Bergheim",
"BN" => "Bonn",
"BO" => "Bochum",
"BOR" => "Borken / Ahaus",
"BOT" => "Bottrop",
"BRA" => "Wesermarsch / Brake",
"BS" => "Braunschweig",
"BT" => "Bayreuth",
"BTF" => "Bitterfeld",
"BÜS" => "Büsingen am Hochrhein",
"BZ" => "Bautzen",
"C" => "Chemnitz",
"CB" => "Cottbus",
"CE" => "Celle",
"CHA" => "Cham",
"CO" => "Coburg",
"COC" => "Cochem-Zell",
"COE" => "Coesfeld",
"CUX" => "Cuxhaven",
"CW" => "Calw",
"D" => "Düsseldorf",
"DA" => "Darmstadt-Dieburg",
"DAH" => "Dachau",
"DAN" => "Lüchow-Dannenberg",
"DAU" => "Daun (Eifel)",
"DBR" => "Bad Doberan",
"DD" => "Dresden",
"DE" => "Dessau-Roßlau",
"DEG" => "Deggendorf",
"DEL" => "Delmenhorst",
"DGF" => "Dingolfing-Landau",
"DH" => "Diepholz-Syke",
"DL" => "Döbeln",
"DLG" => "Dillingen",
"DN" => "Düren",
"Do" => "Dortmund",
"DON" => "Donauwörth",
"DU" => "Duisburg",
"DÜW" => "Bad Dürkheim / Neustadt/Weinstraße",
"E" => "Essen",
"EA" => "Eisenach",
"EB" => "Eilenburg",
"EBE" => "Ebersberg",
"ED" => "Erding",
"EE" => "Elbe-Elsterkreis",
"EF" => "Erfurt",
"EI" => "Eichstätt",
"EIC" => "Eichsfeld",
"EL" => "Emsland",
"EM" => "Emmendingen",
"EMD" => "Emden",
"EMS" => "Bad Ems/Lahn-Kreis",
"EN" => "Ennepe-Ruhr-Kreis",
"ER" => "Erlangen",
"ERB" => "Erbach/Odenwaldkreis",
"ERH" => "Erlangen-Höchstadt",
"ERZ" => "Erzgebirgskreis",
"ES" => "Esslingen",
"ESW" => "Eschwege",
"EU" => "Euskirchen",
"F" => "Frankfurt/Main",
"FFM" => "FRANKFURT(MAIN)",
"FB" => "Friedberg/Wetteraukreis",
"FD" => "Fulda",
"FDS" => "Freudenstadt",
"FF" => "Frankfurt/Oder",
"FFB" => "Fürstenfeldbruck",
"FG" => "Freiberg",
"FL" => "Flensburg",
"FN" => "Friedrichshafen/Bodenseekreis",
"FO" => "Forchheim",
"FR" => "Freiburg",
"FRG" => "Freyung-Grafenau",
"FRI" => "Friesland",
"FS" => "Freising",
"FT" => "Frankenthal",
"FÜ" => "Fürth",
"G" => "Gera",
"GAP" => "Garmisch-Partenkirchen",
"GE" => "Gelsenkirchen",
"GER" => "Germersheim",
"GF" => "Gifhorn",
"GG" => "Groß-Gerau",
"GI" => "Gießen",
"GL" => "Bergisch Gladbach",
"GM" => "Gummersbach",
"GÖ" => "Göttingen",
"GP" => "Göppingen",
"GR" => "Görlitz",
"GRZ" => "Greiz",
"GS" => "Goslar",
"GT" => "Gütersloh",
"GTH" => "Gotha",
"GÜ" => "Güstrow",
"GZ" => "Günzburg",
"H" => "Hannover",
"HA" => "Hagen",
"HAL" => "Halle",
"HAM" => "Hamm",
"HAS" => "Haßberge / Haßfurt",
"HB" => "Bremen",
"BH" => "Bremerhaven",
"HBH" => "Bremerhaven",
"BRV" => "Bremerhaven",
"HBN" => "Hildburghausen",
"HD" => "Heidelberg",
"HDH" => "Heidenheim",
"HE" => "Helmstedt",
"HEF" => "Bad Hersfeld",
"HEI" => "Heide",
"HER" => "Herne",
"HF" => "Herford",
"HG" => "Bad Homburg",
"HGW" => "Greifswald",
"HH" => "Hamburg",
"HI" => "Hildesheim",
"HL" => "Lübeck",
"HM" => "Hameln-Pyrmont",
"HN" => "Heilbronn",
"HO" => "Hof",
"HOL" => "Holzminden",
"HOM" => "Homburg",
"HP" => "Heppenheim",
"HR" => "Homberg",
"HRO" => "Rostock",
"HS" => "Heinsberg",
"HSK" => "Hochsauerlandkreis",
"HST" => "Stralsund",
"HU" => "Hanau",
"HVL" => "Havelland",
"HWI" => "Wismar",
"HX" => "Höxter Rathaus",
"HZ" => "Harz",
"IGB" => "St. Ingbert",
"IK" => "Ilm-Kreis",
"IN" => "Ingolstadt",
"IZ" => "Itzehoe",
"J" => "Jena",
"JL" => "Jerichower Land",
"K" => "Köln",
"KA" => "Karlsruhe",
"KB" => "Korbach",
"KC" => "Kronach",
"KE" => "Kempten",
"KEH" => "Kelheim",
"KF" => "Kaufbeuren",
"KG" => "Bad Kissingen",
"KH" => "Bad Kreuznach",
"KI" => "Kiel",
"KIB" => "Kirchheimbolanden",
"KL" => "Kaiserslautern",
"KLE" => "Kleve",
"KN" => "Konstanz",
"KO" => "Koblenz",
"KR" => "Krefeld",
"KS" => "Kassel",
"KT" => "Kitzingen",
"KU" => "Kulmbach",
"KÜN" => "Künzelsau",
"KUS" => "Kusel",
"KYF" => "Kyffhäuserkreis",
"L" => "Leipzig",
"LA" => "Landshut",
"LAU" => "Lauf/Nürnberger Land",
"LB" => "Ludwigsburg",
"LD" => "Landau",
"LDK" => "Lahn-Dill-Kreis",
"LDS" => "Dahme-Spreewald",
"LER" => "Leer/Ostfriesland",
"LEV" => "Leverkusen",
"LG" => "Lüneburg",
"LI" => "Lindau",
"LIF" => "Lichtenfels",
"LIP" => "Lippe",
"LL" => "Landsberg/Lech",
"LM" => "Limburg-Weilburg",
"LÖ" => "Lörrach",
"LOS" => "Oder-Spree-Kreis Beeskow",
"LRO" => "Landkreis Rostock",
"LU" => "Ludwigshafen",
"LÜ" => "Höxter-Lüchtringen",
"LWL" => "Ludwigslust",
"M" => "München",
"MA" => "Mannheim",
"MB" => "Miesbach",
"MD" => "Magdeburg",
"ME" => "Mettmann",
"MEI" => "Meißen",
"MG" => "Mönchengladbach",
"MI" => "Minden-Lübbecke",
"MIL" => "Miltenberg",
"MK" => "Märkischer Kreis",
"MKK" => "Main-Kinzig-Kreis",
"MM" => "Memmingen",
"MN" => "Mindelheim",
"MOL" => "Märkisch-Oderland",
"MOS" => "Mosbach",
"MR" => "Marburg",
"MS" => "Münster",
"MSH" => "Mansfeld-Südharz",
"MSP" => "Main-Spessart-Kreis",
"MST" => "Mecklenburg-Strelitz",
"MTK" => "Main-Taunus-Kreis",
"MÜ" => "Mühldorf am Inn",
"MÜR" => "Müritz",
// "MVL" => "Landesregierung und Landtag",
"MYK" => "Mayen-Koblenz",
"MZ" => "Mainz",
"MZG" => "Merzig-Wadern",
"N" => "Nürnberg",
"NB" => "Neubrandenburg",
"ND" => "Neuburg-Schrobenhausen",
"NDH" => "Nordhausen",
"NE" => "Neuss",
"NEA" => "Neustadt-Bad Windsheim/Aisch",
"NES" => "Bad Neustadt/Saale",
"NEW" => "Neustadt/Waldnaab",
"NF" => "Nordfriesland",
"NI" => "Nienburg",
"NK" => "Neunkirchen",
"NM" => "Neumarkt/Oberpfalz",
"NMS" => "Neumünster",
"NOH" => "Nordhorn",
"NOM" => "Northeim",
"NR" => "Neuwied",
"NU" => "Neu-Ulm",
"NVP" => "Nordvorpommern",
"NW" => "Neustadt/Weinstraße",
"NWM" => "Nordwestmecklenburg",
"OA" => "Oberallgäu / Sonthofen",
"OAL" => "Ostallgäu",
"OB" => "Oberhausen",
"OD" => "Bad Oldesloe",
"OE" => "Olpe",
"OF" => "Offenbach/Main",
"OG" => "Offenburg",
"OH" => "Ostholstein / Eutin",
"OHA" => "Osterode/Harz",
"OHV" => "Oberhavel",
"OHZ" => "Osterholz-Scharmbeck",
"OL" => "Oldenburg",
"OPR" => "Ostprignitz-Ruppin",
"OS" => "Osnabrück",
"OSL" => "Oberspreewald-Lausitz",
"OVP" => "Ostvorpommern",
"P" => "Potsdam",
"PA" => "Passau",
"PAF" => "Pfaffenhofen",
"PAN" => "Pfarrkirchen",
"PB" => "Paderborn",
"PCH" => "Parchim",
"PE" => "Peine",
"PF" => "Pforzheim",
"PI" => "Pinneberg",
"PIR" => "Pirna/Sächsische Schweiz",
"PLÖ" => "Plön",
"PM" => "Potsdam-Mittelmark",
"PR" => "Prignitz / Perleberg",
"PS" => "Pirmasens",
"R" => "Regensburg",
"RA" => "Rastatt",
"RD" => "Rendsburg-Eckernförde",
"RE" => "Recklinghausen",
"REG" => "Regen",
"RO" => "Rosenheim",
"ROS" => "Rostock/Landkreis",
"ROW" => "Rotenburg/Wümme",
"RP" => "Rhein-Pfalz-Kreis",
// "RPL" => "Landesregierung und Landtag",
"RS" => "Remscheid",
"RT" => "Reutlingen",
"RÜD" => "Rüdesheim",
"RÜG" => "Rügen",
"RV" => "Ravensburg",
"RW" => "Rottweil",
"RZ" => "Ratzeburg",
"S" => "Stuttgart",
"SAD" => "Schwandorf",
// "SAL Landesregierung und Landtag",
"SAW" => "Salzwedel",
"SB" => "Saarbrücken",
"SC" => "Schwabach",
"SDL" => "Stendal",
"SE" => "Bad Segeberg",
"SG" => "Solingen",
// "SH Landesregierung und Landtag",
"SHA" => "Schwäbisch Hall",
"SHG" => "Stadthagen",
"SHK" => "Saale-Holzlandkreis",
"SHL" => "Suhl",
"SI" => "Siegen-Wittgenstein",
"SIG" => "Sigmaringen",
"SIM" => "Rhein-Hunsrück-Kreis",
"SK" => "Saalkreis",
"SL" => "Schleswig-Flensburg",
"SLF" => "Saalfeld-Rudolstadt",
"SLK" => "Salzlandkreis",
"SLS" => "Saarlouis",
"SM" => "Schmalkalden-Meiningen",
"SN" => "Schwerin",
"SO" => "Soest",
"SOK" => "Saale-Orla-Kreis",
"SÖM" => "Sömmerda",
"SON" => "Sonneberg",
"SP" => "Speyer",
"SPN" => "Spree-Neiße",
"SR" => "Straubing-Bogen",
"ST" => "Steinfurt",
"STA" => "Starnberg",
"STD" => "Stade",
"SU" => "Siegburg",
"SÜW" => "Südliche Weinstraße",
"SW" => "Schweinfurt",
"SZ" => "Salzgitter",
"TDO" => "Nordsachsen",
"TBB" => "Tauberbischofsheim",
"TF" => "Teltow-Fläming",
"TG" => "Torgau",
// "THL Landesregierung und Landtag",
"TIR" => "Tirschenreuth",
"TÖL" => "Bad Tölz",
"TR" => "Trier",
"TS" => "Traunstein",
"TÜ" => "Tübingen",
"TUT" => "Tuttlingen",
"UE" => "Uelzen",
"UL" => "Ulm",
"UM" => "Uckermark",
"UN" => "Unna",
"V" => "Vogtlandkreis",
"VB" => "Vogelsbergkreis",
"VEC" => "Vechta",
"VER" => "Verden",
"VIE" => "Viersen",
"VK" => "Völklingen",
"VR" => "Vorpommern-Rügen",
"VS" => "Villingen-Schwenningen",
"W" => "Wuppertal",
"WAF" => "Warendorf",
"WAK" => "Wartburgkreis",
"WB" => "Wittenberg",
"WE" => "Weimar",
"WEN" => "Weiden",
"WES" => "Wesel",
"WF" => "Wolfenbüttel",
"WHV" => "Wilhelmshaven",
"WI" => "Wiesbaden",
"WIL" => "Wittlich",
"WL" => "Winsen/Luhe",
"WM" => "Weilheim-Schongau",
"WN" => "Waiblingen",
"WND" => "St. Wendel",
"WO" => "Worms",
"WOB" => "Wolfsburg",
"WST" => "Westerstede",
"WT" => "Waldshut-Tiengen",
"WTM" => "Wittmund",
"WÜ" => "Würzburg",
"WUG" => "Weißenburg-Gunzenhausen",
"WUN" => "Wunsiedel",
"WW" => "Westerwald",
"WZ" => "Wetzlar",
"Z" => "Zwickau",
"ZW" => "Zweibrücken"];
?>