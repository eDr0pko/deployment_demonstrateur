<?php
$servername = "mysql-db"; // Nom du service MySQL dans docker-compose
$username = "root";
$password = "superpass";
$dbname = "musicDB";

// Connexion à MySQL
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérification de la connexion
if ($conn->connect_error) {
  die("Échec de la connexion : " . $conn->connect_error);
}
?>
