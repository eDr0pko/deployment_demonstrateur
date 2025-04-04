<?php
    session_name('cookie_de_session');
    session_start();
    session_unset();
    session_destroy();
    echo "Déconnexion réussie";
?>


