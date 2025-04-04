<?php
    session_name('cookie_de_session');
    session_start();

    if (isset($_SESSION['mail'])) {
        echo json_encode([
            "loggedIn" => true,
            "username" => $_SESSION['username'],
            "mail" => $_SESSION['mail'],
            "profile_picture" => $_SESSION['profile_picture']
        ]);
    } else {
        echo json_encode(["loggedIn" => false]);
    }
?>


