<?php
if (!$ziel) {
    // ob_clean();
    $id = $start->data->L;
    header("Location: https://www.chronotrains.com/de/station/$id-/5");
    // $x =  $start->data->X;
    // $y =  $start->data->Y;
    //require "maps.php";
} else {
    header("Location: https://maps.google.com?saddr=$start->name&daddr=$ziel->name&dirflg=r");
}
exit;