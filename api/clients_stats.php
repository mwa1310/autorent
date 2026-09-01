<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/middleware_auth.php';
$pdo = getPDO();
exigerAuthentification();

$total = (int) $pdo->query("SELECT COUNT(*) FROM Clients WHERE etat='actif'")->fetchColumn();
$nouveauxMois = (int) $pdo->query("SELECT COUNT(*) FROM Clients WHERE MONTH(date_creation)=MONTH(CURDATE()) AND YEAR(date_creation)=YEAR(CURDATE())")->fetchColumn();
$avecReservationActive = (int) $pdo->query("SELECT COUNT(DISTINCT id_client) FROM Reservations WHERE statut IN ('reservee','en_cours')")->fetchColumn();
$totalToutesTables = (int) $pdo->query("SELECT COUNT(*) FROM Clients")->fetchColumn();

echo json_encode(["success" => true, "total" => $total, "nouveaux_mois" => $nouveauxMois, "avec_reservation_active" => $avecReservationActive, "total_avec_inactifs" => $totalToutesTables]);
