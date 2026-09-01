<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/middleware_auth.php';
$pdo = getPDO();

exigerAuthentification();

$recherche = trim($_GET['recherche'] ?? '');
if (mb_strlen($recherche) < 1) { echo json_encode(["success" => true, "data" => []]); exit; }

$stmt = $pdo->prepare(
    "SELECT r.id_reservation, r.montant_total, c.nom, c.prenom, v.marque, v.modele
     FROM Reservations r
     JOIN Clients c ON c.id_client = r.id_client
     JOIN Vehicules v ON v.id_vehicule = r.id_vehicule
     WHERE r.id_reservation LIKE :r1 OR c.nom LIKE :r2 OR c.prenom LIKE :r3
     ORDER BY r.date_reservation DESC LIMIT 10"
);
$v = '%' . $recherche . '%';
$stmt->execute(["r1" => $v, "r2" => $v, "r3" => $v]);

$donnees = array_map(function ($r) {
    $ref = 'RÉS-' . date('Y') . '-' . str_pad($r['id_reservation'], 4, '0', STR_PAD_LEFT);
    return [
        "id" => $r['id_reservation'],
        "label" => $ref . ' — ' . $r['prenom'] . ' ' . $r['nom'] . ' (' . $r['marque'] . ' ' . $r['modele'] . ')',
        "montant_total" => (float) $r['montant_total'],
    ];
}, $stmt->fetchAll());

echo json_encode(["success" => true, "data" => $donnees]);
