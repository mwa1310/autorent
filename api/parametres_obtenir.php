<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/middleware_auth.php';
$pdo = getPDO();
exigerAuthentification();

$stmt = $pdo->query("SELECT * FROM Parametres WHERE id = 1");
$parametres = $stmt->fetch();

if (!$parametres) {
    // Filet de sécurité si jamais la ligne par défaut n'a pas été insérée
    $pdo->exec("INSERT IGNORE INTO Parametres (id, nom_agence) VALUES (1, 'AutoRent')");
    $stmt = $pdo->query("SELECT * FROM Parametres WHERE id = 1");
    $parametres = $stmt->fetch();
}

echo json_encode(["success" => true, "data" => $parametres]);
