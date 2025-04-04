<?php
    session_name('session');
    session_start();
    session_unset();
    session_destroy();
    echo "Déconnexion réussie";
?>


