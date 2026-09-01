<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/middleware_auth.php';
$pdo = getPDO();

exigerAuthentification();

$periodeDebut = $_GET['date_debut'] ?? date('Y-m-01');
$periodeFin   = $_GET['date_fin'] ?? date('Y-m-t');

// Période précédente, de durée identique, juste avant la période sélectionnée
$duree = (new DateTime($periodeDebut))->diff(new DateTime($periodeFin))->days + 1;
$prevFin = (new DateTime($periodeDebut))->modify('-1 day')->format('Y-m-d');
$prevDebut = (new DateTime($prevFin))->modify('-' . ($duree - 1) . ' days')->format('Y-m-d');

function statsPeriode(PDO $pdo, string $debut, string $fin): array {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM Reservations WHERE date_reservation BETWEEN :d AND :f");
    $stmt->execute(["d" => $debut, "f" => $fin . ' 23:59:59']);
    $totalReservations = (int) $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM Reservations WHERE statut = 'terminee' AND date_fin BETWEEN :d AND :f");
    $stmt->execute(["d" => $debut, "f" => $fin . ' 23:59:59']);
    $locationsRealisees = (int) $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COALESCE(SUM(montant_total),0) FROM Reservations WHERE statut != 'annulee' AND date_debut BETWEEN :d AND :f");
    $stmt->execute(["d" => $debut, "f" => $fin . ' 23:59:59']);
    $chiffreAffaires = (float) $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COALESCE(SUM(montant),0) FROM Paiements WHERE statut = 'valide' AND date_paiement BETWEEN :d AND :f");
    $stmt->execute(["d" => $debut, "f" => $fin . ' 23:59:59']);
    $paiementsRecus = (float) $stmt->fetchColumn();

    // Taux d'occupation = jours-véhicule occupés (locations en cours/terminées chevauchant la période) / (nb véhicules actifs * durée période)
    $nbVehicules = (int) $pdo->query("SELECT COUNT(*) FROM Vehicules WHERE etat = 'actif'")->fetchColumn();
    $dureeJours = max(1, (new DateTime($debut))->diff(new DateTime($fin))->days + 1);
    $stmt = $pdo->prepare(
        "SELECT COALESCE(SUM(DATEDIFF(LEAST(date_fin, :f2), GREATEST(date_debut, :d2)) + 1), 0)
         FROM Reservations
         WHERE statut IN ('en_cours', 'terminee') AND date_debut <= :f AND date_fin >= :d"
    );
    $stmt->execute(["d" => $debut, "f" => $fin, "d2" => $debut, "f2" => $fin]);
    $joursOccupes = (float) $stmt->fetchColumn();
    $tauxOccupation = $nbVehicules > 0 ? round(($joursOccupes / ($nbVehicules * $dureeJours)) * 100, 1) : 0;

    return compact('totalReservations', 'locationsRealisees', 'chiffreAffaires', 'paiementsRecus', 'tauxOccupation');
}

$actuel = statsPeriode($pdo, $periodeDebut, $periodeFin);
$precedent = statsPeriode($pdo, $prevDebut, $prevFin);

function variation($actuel, $precedent) {
    if ($precedent == 0) return null;
    return round((($actuel - $precedent) / $precedent) * 100, 1);
}

echo json_encode([
    "success" => true,
    "reservations_totales" => $actuel['totalReservations'],
    "variation_reservations" => variation($actuel['totalReservations'], $precedent['totalReservations']),
    "locations_realisees" => $actuel['locationsRealisees'],
    "variation_locations" => variation($actuel['locationsRealisees'], $precedent['locationsRealisees']),
    "chiffre_affaires" => $actuel['chiffreAffaires'],
    "variation_ca" => variation($actuel['chiffreAffaires'], $precedent['chiffreAffaires']),
    "paiements_recus" => $actuel['paiementsRecus'],
    "variation_paiements" => variation($actuel['paiementsRecus'], $precedent['paiementsRecus']),
    "taux_occupation" => $actuel['tauxOccupation'],
    "variation_occupation" => $precedent['tauxOccupation'] > 0 ? round($actuel['tauxOccupation'] - $precedent['tauxOccupation'], 1) : null,
    "periode_precedente_libelle" => (new DateTime($prevDebut))->format('d/m') . ' - ' . (new DateTime($prevFin))->format('d/m'),
]);
