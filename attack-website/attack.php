<?php
$fichier = "cookies.txt";
if (file_exists($fichier)) {
    header("Content-Type: text/plain; charset=UTF-8");
    echo file_get_contents($fichier);
} else {
    http_response_code(404);
    echo "Erreur : fichier introuvable.";
}
?>
