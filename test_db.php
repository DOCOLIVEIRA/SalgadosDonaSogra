<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=do_oliveira_salgados', 'root', 'donasogra@2026');
    echo "Conectado PDO!\n";
} catch (PDOException $e) {
    echo 'Erro PDO: ' . $e->getMessage() . "\n";
}
