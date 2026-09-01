<?php
/**
 * Répartition par statut (réservations + véhicules), pour les graphiques en anneau
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';
$pdo = getPDO();
require_once __DIR__ . '/../includes/middleware_auth.php';

exigerAuthentification();

// --- Réservations par statut (toutes, tous historiques confondus) ---
$stmtReservations = $pdo->query(
    "SELECT statut, COUNT(*) AS nb FROM Reservations GROUP BY statut"
);
$brutReservations = array_column($stmtReservations->fetchAll(), 'nb', 'statut');

$reservationsParStatut = [
    "a_venir"  => (int) ($brutReservations['reservee'] ?? 0),
    "en_cours" => (int) ($brutReservations['en_cours'] ?? 0),
    "terminee" => (int) ($brutReservations['terminee'] ?? 0),
    "annulee"  => (int) ($brutReservations['annulee'] ?? 0),
];
$totalReservations = array_sum($reservationsParStatut);

// --- Véhicules par statut ---
$stmtVehicules = $pdo->query(
    "SELECT statut_actuel, COUNT(*) AS nb FROM Vehicules WHERE etat = 'actif' GROUP BY statut_actuel"
);
$brutVehicules = array_column($stmtVehicules->fetchAll(), 'nb', 'statut_actuel');

$vehiculesParStatut = [
    "disponibles"    => (int) (($brutVehicules['disponible'] ?? 0) + ($brutVehicules['en_location'] ?? 0)),
    "en_maintenance" => (int) ($brutVehicules['maintenance'] ?? 0),
    "hors_service"   => (int) ($brutVehicules['hors_service'] ?? 0),
];
$totalVehicules = array_sum($vehiculesParStatut);

echo json_encode([
    "success" => true,
    "reservations" => [
        "total" => $totalReservations,
        "repartition" => $reservationsParStatut,
    ],
    "vehicules" => [
        "total" => $totalVehicules,
        "repartition" => $vehiculesParStatut,
    ],
]);
