<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/middleware_auth.php';
$pdo = getPDO();

exigerAuthentification();

$totalCategories = (int) $pdo->query("SELECT COUNT(*) FROM Categories_vehicules WHERE etat = 'actif'")->fetchColumn();
$vehiculesClasses = (int) $pdo->query("SELECT COUNT(*) FROM Vehicules WHERE etat = 'actif'")->fetchColumn();
$prixMoyen = (float) $pdo->query("SELECT AVG(tarif_jour) FROM Categories_vehicules WHERE etat = 'actif'")->fetchColumn();

$stmtMin = $pdo->query("SELECT nom, tarif_jour FROM Categories_vehicules WHERE etat = 'actif' ORDER BY tarif_jour ASC LIMIT 1");
$catMin = $stmtMin->fetch();
$stmtMax = $pdo->query("SELECT nom, tarif_jour FROM Categories_vehicules WHERE etat = 'actif' ORDER BY tarif_jour DESC LIMIT 1");
$catMax = $stmtMax->fetch();

echo json_encode([
    "success" => true,
    "total_categories"   => $totalCategories,
    "vehicules_classes"  => $vehiculesClasses,
    "prix_moyen_jour"    => round($prixMoyen),
    "tarif_min"          => $catMin ? (float) $catMin['tarif_jour'] : 0,
    "categorie_min"      => $catMin['nom'] ?? '',
    "tarif_max"          => $catMax ? (float) $catMax['tarif_jour'] : 0,
    "categorie_max"      => $catMax['nom'] ?? '',
]);
