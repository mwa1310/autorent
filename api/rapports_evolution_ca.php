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

function ventilerParTranches(PDO $pdo, string $debut, string $fin): array {
    $stmt = $pdo->prepare(
        "SELECT FLOOR(DATEDIFF(date_debut, :ref) / 7) AS tranche, SUM(montant_total) AS total
         FROM Reservations
         WHERE date_debut BETWEEN :d AND :f AND statut != 'annulee'
         GROUP BY tranche"
    );
    $stmt->execute(["ref" => $debut, "d" => $debut, "f" => $fin . ' 23:59:59']);
    return array_column($stmt->fetchAll(), 'total', 'tranche');
}

$parTrancheActuelle = ventilerParTranches($pdo, $periodeDebut, $periodeFin);
$parTranchePrecedente = ventilerParTranches($pdo, $prevDebut, $prevFin);

$nbTranches = (int) ceil($duree / 7);
$labels = [];
$donneesActuelles = [];
$donneesPrecedentes = [];

for ($t = 0; $t < $nbTranches; $t++) {
    $debutTranche = (new DateTime($periodeDebut))->modify('+' . ($t * 7) . ' days');
    $finTranche = (clone $debutTranche)->modify('+6 days');
    if ($finTranche > new DateTime($periodeFin)) $finTranche = new DateTime($periodeFin);

    $labels[] = $debutTranche->format('d') . ' - ' . $finTranche->format('d M');
    $donneesActuelles[] = (float) ($parTrancheActuelle[$t] ?? 0);
    $donneesPrecedentes[] = (float) ($parTranchePrecedente[$t] ?? 0);
}

echo json_encode([
    "success" => true,
    "labels" => $labels,
    "periode_actuelle" => $donneesActuelles,
    "periode_precedente" => $donneesPrecedentes,
    "libelle_periode_actuelle" => (new DateTime($periodeDebut))->format('M Y'),
    "libelle_periode_precedente" => (new DateTime($prevDebut))->format('M Y'),
]);
