<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/middleware_auth.php';
$pdo = getPDO();

exigerAuthentification();

$stmt = $pdo->query(
    "SELECT id_categorie, nom, tarif_jour FROM Categories_vehicules WHERE etat = 'actif' ORDER BY tarif_jour ASC"
);
echo json_encode(["success" => true, "data" => $stmt->fetchAll()]);
