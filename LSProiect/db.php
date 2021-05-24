<?php
// Deschiderea conexiunii cu MySQL
$connection=mysqli_connect ("localhost", 'root', '','demo');
if (!$connection) {
    die('Not connected : ' . mysqli_connect_error());
}

