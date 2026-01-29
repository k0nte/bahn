<?php
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
if (is_array($result["Location"]) || !$result["Location"])
    return $nachricht = "Zug nicht erkannt.";
header("Location: ".$result["Location"]);
exit;