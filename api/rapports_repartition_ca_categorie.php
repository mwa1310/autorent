<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/middleware_auth.php';
$pdo = getPDO();

exigerAuthentification();

$periodeDebut = $_GET['date_debut'] ?? date('Y-m-01');
$periodeFin   = $_GET['date_fin'] ?? date('Y-m-t');

$stmt = $pdo->prepare(
    "SELECT cv.nom, SUM(r.montant_total) AS ca
     FROM Reservations r
     JOIN Vehicules v ON v.id_vehicule = r.id_vehicule
     JOIN Categories_vehicules cv ON cv.id_categorie = v.id_categorie
     WHERE r.date_debut BETWEEN :d AND :f AND r.statut != 'annulee'
     GROUP BY cv.id_categorie
     ORDER BY ca DESC"
);
$stmt->execute(["d" => $periodeDebut, "f" => $periodeFin . ' 23:59:59']);
$lignes = $stmt->fetchAll();

$total = array_sum(array_column($lignes, 'ca'));
$donnees = array_map(function ($l) use ($total) {
    return [
        "categorie" => $l['nom'],
        "ca" => (float) $l['ca'],
        "pourcentage" => $total > 0 ? round(($l['ca'] / $total) * 100, 1) : 0,
    ];
}, $lignes);

echo json_encode(["success" => true, "total" => (float) $total, "data" => $donnees]);
