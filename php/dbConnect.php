<?php
/*
    Connessione al DB consegne.
    Espone:
    - $database  : istanza mysqli (oppure null se la connessione fallisce)
    - $dbConnect : bool, true se la connessione e' aperta
*/
mysqli_report(MYSQLI_REPORT_OFF);

$database  = @new mysqli('localhost', 'root', '', 'consegne');
$dbConnect = !$database->connect_error;

if ($dbConnect) 
    $database->set_charset('utf8mb4');
else 
    $database = null;
