<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/middleware_auth.php';
$pdo = getPDO();

exigerAuthentification();

$periodeDebut = $_GET['date_debut'] ?? date('Y-m-01');
$periodeFin   = $_GET['date_fin'] ?? date('Y-m-t');

$duree = (new DateTime($periodeDebut))->diff(new DateTime($periodeFin))->days + 1;
$prevFin = (new DateTime($periodeDebut))->modify('-1 day')->format('Y-m-d');
$prevDebut = (new DateTime($prevFin))->modify('-' . ($duree - 1) . ' days')->format('Y-m-d');

function indicateurs(PDO $pdo, string $debut, string $fin): array {
    // Durée moyenne de location (réservations terminées ayant démarré dans la période)
    $stmt = $pdo->prepare(
        "SELECT AVG(DATEDIFF(date_fin, date_debut)) FROM Reservations
         WHERE statut = 'terminee' AND date_debut BETWEEN :d AND :f"
    );
    $stmt->execute(["d" => $debut, "f" => $fin . ' 23:59:59']);
    $dureeMoyenne = (float) $stmt->fetchColumn();

    // Distance moyenne par location : différence kilométrage retour - départ (Etats_des_lieux)
    $stmt = $pdo->prepare(
        "SELECT AVG(edl_retour.kilometrage - edl_depart.kilometrage)
         FROM Reservations r
         JOIN Etats_des_lieux edl_depart ON edl_depart.id_reservation = r.id_reservation AND edl_depart.type = 'depart'
         JOIN Etats_des_lieux edl_retour ON edl_retour.id_reservation = r.id_reservation AND edl_retour.type = 'retour'
         WHERE r.statut = 'terminee' AND r.date_debut BETWEEN :d AND :f"
    );
    $stmt->execute(["d" => $debut, "f" => $fin . ' 23:59:59']);
    $distanceMoyenne = (float) $stmt->fetchColumn();

    // Panier moyen (montant_total des réservations non annulées démarrées dans la période)
    $stmt = $pdo->prepare(
        "SELECT AVG(montant_total) FROM Reservations WHERE statut != 'annulee' AND date_debut BETWEEN :d AND :f"
    );
    $stmt->execute(["d" => $debut, "f" => $fin . ' 23:59:59']);
    $panierMoyen = (float) $stmt->fetchColumn();

    // Taux d'annulation
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM Reservations WHERE date_reservation BETWEEN :d AND :f");
    $stmt->execute(["d" => $debut, "f" => $fin . ' 23:59:59']);
    $total = (int) $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM Reservations WHERE statut = 'annulee' AND date_reservation BETWEEN :d AND :f");
    $stmt->execute(["d" => $debut, "f" => $fin . ' 23:59:59']);
    $annulees = (int) $stmt->fetchColumn();

    $tauxAnnulation = $total > 0 ? round(($annulees / $total) * 100, 1) : 0;

    return compact('dureeMoyenne', 'distanceMoyenne', 'panierMoyen', 'tauxAnnulation');
}

$actuel = indicateurs($pdo, $periodeDebut, $periodeFin);
$precedent = indicateurs($pdo, $prevDebut, $prevFin);

$nbVehiculesMaintenance = (int) $pdo->query("SELECT COUNT(*) FROM Vehicules WHERE statut_actuel = 'maintenance' AND etat = 'actif'")->fetchColumn();
$nbVehiculesTotal = (int) $pdo->query("SELECT COUNT(*) FROM Vehicules WHERE etat = 'actif'")->fetchColumn();

echo json_encode([
    "success" => true,
    "duree_moyenne_jours"     => round($actuel['dureeMoyenne'], 1),
    "delta_duree"             => round($actuel['dureeMoyenne'] - $precedent['dureeMoyenne'], 1),
    "distance_moyenne_km"     => round($actuel['distanceMoyenne']),
    "delta_distance"          => round($actuel['distanceMoyenne'] - $precedent['distanceMoyenne']),
    "panier_moyen"            => round($actuel['panierMoyen']),
    "delta_panier"            => round($actuel['panierMoyen'] - $precedent['panierMoyen']),
    "taux_annulation"         => $actuel['tauxAnnulation'],
    "delta_annulation"        => round($actuel['tauxAnnulation'] - $precedent['tauxAnnulation'], 1),
    "vehicules_maintenance"   => $nbVehiculesMaintenance,
    "pct_parc_maintenance"    => $nbVehiculesTotal > 0 ? round(($nbVehiculesMaintenance / $nbVehiculesTotal) * 100) : 0,
]);
