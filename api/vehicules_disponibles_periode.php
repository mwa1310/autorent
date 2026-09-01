<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/middleware_auth.php';
$pdo = getPDO();

exigerAuthentification();

$dateDebut = $_GET['date_debut'] ?? '';
$dateFin   = $_GET['date_fin'] ?? '';
$recherche = trim($_GET['recherche'] ?? '');
$exclureId = (int) ($_GET['exclure_reservation'] ?? 0); // utile en édition : exclut la réservation en cours d'édition de la vérification

if (!$dateDebut || !$dateFin) {
    echo json_encode(["success" => true, "data" => []]);
    exit;
}

$sql = "SELECT v.id_vehicule, v.immatriculation, v.marque, v.modele, cv.tarif_jour
        FROM Vehicules v
        JOIN Categories_vehicules cv ON cv.id_categorie = v.id_categorie
        WHERE v.etat = 'actif' AND v.statut_actuel != 'hors_service'
          AND v.id_vehicule NOT IN (
              SELECT r.id_vehicule FROM Reservations r
              WHERE r.statut IN ('reservee', 'en_cours')
                AND r.date_debut < :date_fin AND r.date_fin > :date_debut"
        . ($exclureId ? " AND r.id_reservation != :exclure_id" : "") . "
          )";

if ($recherche !== '') {
    $sql .= " AND (v.immatriculation LIKE :r1 OR v.marque LIKE :r2 OR v.modele LIKE :r3)";
}
$sql .= " ORDER BY v.marque ASC LIMIT 15";

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':date_debut', $dateDebut);
$stmt->bindValue(':date_fin', $dateFin);
if ($exclureId) $stmt->bindValue(':exclure_id', $exclureId, PDO::PARAM_INT);
if ($recherche !== '') {
    $v = '%' . $recherche . '%';
    $stmt->bindValue(':r1', $v); $stmt->bindValue(':r2', $v); $stmt->bindValue(':r3', $v);
}
$stmt->execute();

$donnees = array_map(fn($v) => [
    "id" => $v['id_vehicule'],
    "label" => $v['marque'] . ' ' . $v['modele'] . ' (' . $v['immatriculation'] . ')',
    "tarif_jour" => (float) $v['tarif_jour'],
], $stmt->fetchAll());

echo json_encode(["success" => true, "data" => $donnees]);
