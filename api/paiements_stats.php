<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/middleware_auth.php';
$pdo = getPDO();

exigerAuthentification();

function variationMensuelle(PDO $pdo, string $sqlBase): ?float {
    $moisActuel = (float) $pdo->query("$sqlBase AND MONTH(date_paiement) = MONTH(CURDATE()) AND YEAR(date_paiement) = YEAR(CURDATE())")->fetchColumn();
    $moisDernier = (float) $pdo->query("$sqlBase AND MONTH(date_paiement) = MONTH(CURDATE() - INTERVAL 1 MONTH) AND YEAR(date_paiement) = YEAR(CURDATE() - INTERVAL 1 MONTH)")->fetchColumn();
    if ($moisDernier <= 0) return null;
    return round((($moisActuel - $moisDernier) / $moisDernier) * 100, 1);
}

$total = (int) $pdo->query("SELECT COUNT(*) FROM Paiements")->fetchColumn();
$montantValide = (float) $pdo->query("SELECT COALESCE(SUM(montant),0) FROM Paiements WHERE statut = 'valide'")->fetchColumn();
$montantAttente = (float) $pdo->query("SELECT COALESCE(SUM(montant),0) FROM Paiements WHERE statut = 'en_attente'")->fetchColumn();
$montantRembourse = (float) $pdo->query("SELECT COALESCE(SUM(montant),0) FROM Paiements WHERE statut = 'rembourse'")->fetchColumn();

echo json_encode([
    "success" => true,
    "total_paiements"   => $total,
    "variation_total"    => variationMensuelle($pdo, "SELECT COUNT(*) FROM Paiements WHERE 1=1"),
    "montant_recu"       => $montantValide,
    "variation_recu"     => variationMensuelle($pdo, "SELECT COALESCE(SUM(montant),0) FROM Paiements WHERE statut = 'valide'"),
    "montant_attente"    => $montantAttente,
    "variation_attente"  => variationMensuelle($pdo, "SELECT COALESCE(SUM(montant),0) FROM Paiements WHERE statut = 'en_attente'"),
    "montant_rembourse"  => $montantRembourse,
    "variation_rembourse"=> variationMensuelle($pdo, "SELECT COALESCE(SUM(montant),0) FROM Paiements WHERE statut = 'rembourse'"),
]);
