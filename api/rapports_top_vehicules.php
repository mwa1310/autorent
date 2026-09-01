<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/middleware_auth.php';
$pdo = getPDO();

exigerAuthentification();

$periodeDebut = $_GET['date_debut'] ?? date('Y-m-01');
$periodeFin   = $_GET['date_fin'] ?? date('Y-m-t');

$stmt = $pdo->prepare(
    "SELECT v.marque, v.modele, cv.nom AS categorie, COUNT(*) AS nb_locations, SUM(r.montant_total) AS ca
     FROM Reservations r
     JOIN Vehicules v ON v.id_vehicule = r.id_vehicule
     JOIN Categories_vehicules cv ON cv.id_categorie = v.id_categorie
     WHERE r.date_debut BETWEEN :d AND :f AND r.statut != 'annulee'
     GROUP BY r.id_vehicule
     ORDER BY nb_locations DESC
     LIMIT 5"
);
$stmt->execute(["d" => $periodeDebut, "f" => $periodeFin . ' 23:59:59']);

$donnees = array_map(fn($v) => [
    "vehicule"    => $v['marque'] . ' ' . $v['modele'],
    "categorie"   => $v['categorie'],
    "locations"   => (int) $v['nb_locations'],
    "ca_genere"   => (float) $v['ca'],
], $stmt->fetchAll());

echo json_encode(["success" => true, "data" => $donnees]);
