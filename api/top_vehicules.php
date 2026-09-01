<?php

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';
$pdo = getPDO();
require_once __DIR__ . '/../includes/middleware_auth.php';

exigerAuthentification();

$stmt = $pdo->query(
    "SELECT v.marque, v.modele, COUNT(*) AS nb_locations
     FROM Reservations r
     JOIN Vehicules v ON v.id_vehicule = r.id_vehicule
     WHERE MONTH(r.date_debut) = MONTH(CURDATE()) AND YEAR(r.date_debut) = YEAR(CURDATE())
       AND r.statut != 'annulee'
     GROUP BY r.id_vehicule
     ORDER BY nb_locations DESC
     LIMIT 5"
);

$donnees = array_map(function ($v) {
    return [
        "nom"    => $v['marque'] . ' ' . $v['modele'],
        "locations" => (int) $v['nb_locations'],
    ];
}, $stmt->fetchAll());

echo json_encode(["success" => true, "data" => $donnees]);
