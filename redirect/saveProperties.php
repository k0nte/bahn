<?php
$data = explode("=", $ql);
$true = (count($data) == 1 || in_array($data[1], ["true", "wahr", "1"])) ? true : 
        (in_array($data[1], ["false", "falsch", "0", ""]) ? false : null);
if (strpos($ql, "-") === 0)
    $true = false;
$key = str_replace(["-", " "], "", $data[0]);
$value = isset($data[1]) ? trim($data[1]) : null;
if (in_array($key, $keywords->props)) {
    foreach ($replacements as $a => $b)
        if ($key == $a)
            $key = $b;
    if ($true) {
        foreach ($pairs as $pair) {
            if (array_search($key, $pair) !== false) 
                foreach ($pair as $cookieToDelete)
                    cookie(strtoupper($cookieToDelete));
        }
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
        $value = str_replace("-", " ", $value);
        cookie("station[$key]", $value);
        $nachricht = "Bahnhof \"$value\" als \"$key\" abgespeichert.";
    } else {
        cookie("station[$key]");
        cookie(strtoupper($key));
        $nachricht = "Abkürzung \"$key\" gelöscht.";
    }
}
?>