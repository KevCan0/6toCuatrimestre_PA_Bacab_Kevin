<?php

header("Content-Type: application/json; charset=UTF-8");

date_default_timezone_set("America/Merida");

$respuesta = array(
    "fecha" => date("Y-m-d"),
    "hora" => date("H:i:s")
);

echo json_encode($respuesta);

?>
