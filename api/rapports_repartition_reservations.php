<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/middleware_auth.php';
$pdo = getPDO();

exigerAuthentification();

$periodeDebut = $_GET['date_debut'] ?? date('Y-m-01');
$periodeFin   = $_GET['date_fin'] ?? date('Y-m-t');

$stmt = $pdo->prepare(
    "SELECT statut, COUNT(*) AS nb FROM Reservations
     WHERE date_reservation BETWEEN :d AND :f
     GROUP BY statut"
);
$stmt->execute(["d" => $periodeDebut, "f" => $periodeFin . ' 23:59:59']);
$brut = array_column($stmt->fetchAll(), 'nb', 'statut');

$repartition = [
    "a_venir"  => (int) ($brut['reservee'] ?? 0),
    "en_cours" => (int) ($brut['en_cours'] ?? 0),
    "terminee" => (int) ($brut['terminee'] ?? 0),
    "annulee"  => (int) ($brut['annulee'] ?? 0),
];

echo json_encode(["success" => true, "total" => array_sum($repartition), "repartition" => $repartition]);
