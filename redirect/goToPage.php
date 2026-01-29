<?php
require "data/punkte.php";
if ($ql == "kfz")
    $ql = "kennzeichen";
echo '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head>
<body style="background: rgb(255,207,192,1); width:100%;margin:0 0 90px">';
echo '<a href="/" style="float: right; padding-right: 30vw">Zurück</a><h1 style="margin:30px">'.ucfirst($ql).'</h1><dl style="columns: 14em; width: 90%; margin: auto">';
ksort($$ql);
foreach ($$ql as $kurz => $lang) 
    echo "<dt>$kurz</dt><dd>$lang</dd>\n";
echo "</dl></body>";
exit;
?>