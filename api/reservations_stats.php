<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/middleware_auth.php';
$pdo = getPDO();

exigerAuthentification();

$stmt = $pdo->query("SELECT statut, COUNT(*) AS nb FROM Reservations GROUP BY statut");
$brut = array_column($stmt->fetchAll(), 'nb', 'statut');

$total = array_sum($brut);
$moisActuel = (int) $pdo->query("SELECT COUNT(*) FROM Reservations WHERE MONTH(date_reservation) = MONTH(CURDATE()) AND YEAR(date_reservation) = YEAR(CURDATE())")->fetchColumn();
$moisDernier = (int) $pdo->query("SELECT COUNT(*) FROM Reservations WHERE MONTH(date_reservation) = MONTH(CURDATE() - INTERVAL 1 MONTH) AND YEAR(date_reservation) = YEAR(CURDATE() - INTERVAL 1 MONTH)")->fetchColumn();

$variation = $moisDernier > 0 ? round((($moisActuel - $moisDernier) / $moisDernier) * 100, 1) : null;

echo json_encode([
    "success" => true,
    "total"    => $total,
    "en_cours" => (int) ($brut['en_cours'] ?? 0),
    "terminee" => (int) ($brut['terminee'] ?? 0),
    "annulee"  => (int) ($brut['annulee'] ?? 0),
    "variation_mensuelle" => $variation,
]);
