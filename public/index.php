<?php

require __DIR__ . '/../init.php';

$pdo = db(); // baut die Verbindung beim ersten Aufruf auf

echo "DB verbunden";
