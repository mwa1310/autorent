<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/middleware_auth.php';
$pdo = getPDO();

exigerAuthentification();

$recherche = trim($_GET['recherche'] ?? '');
if (mb_strlen($recherche) < 2) { echo json_encode(["success" => true, "data" => []]); exit; }

$stmt = $pdo->prepare(
    "SELECT id_client, nom, prenom, telephone FROM Clients
     WHERE etat = 'actif' AND (nom LIKE :r1 OR prenom LIKE :r2 OR telephone LIKE :r3)
     ORDER BY nom ASC LIMIT 10"
);
$v = '%' . $recherche . '%';
$stmt->execute(["r1" => $v, "r2" => $v, "r3" => $v]);

$donnees = array_map(fn($c) => [
    "id" => $c['id_client'],
    "label" => $c['prenom'] . ' ' . $c['nom'] . ' — ' . $c['telephone'],
], $stmt->fetchAll());

echo json_encode(["success" => true, "data" => $donnees]);
