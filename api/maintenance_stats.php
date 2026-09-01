<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/middleware_auth.php';
$pdo = getPDO();
exigerAuthentification();

$total = (int) $pdo->query("SELECT COUNT(*) FROM Maintenance WHERE etat='actif'")->fetchColumn();
$enCours = (int) $pdo->query("SELECT COUNT(*) FROM Maintenance WHERE etat='actif' AND statut='en_cours'")->fetchColumn();
$terminees = (int) $pdo->query("SELECT COUNT(*) FROM Maintenance WHERE etat='actif' AND statut='terminee'")->fetchColumn();
$enRetard = (int) $pdo->query("SELECT COUNT(*) FROM Maintenance WHERE etat='actif' AND statut IN ('planifiee','en_cours') AND date_prevue < CURDATE()")->fetchColumn();

$moisActuel = (int) $pdo->query("SELECT COUNT(*) FROM Maintenance WHERE MONTH(date_prevue)=MONTH(CURDATE()) AND YEAR(date_prevue)=YEAR(CURDATE())")->fetchColumn();
$moisDernier = (int) $pdo->query("SELECT COUNT(*) FROM Maintenance WHERE MONTH(date_prevue)=MONTH(CURDATE() - INTERVAL 1 MONTH) AND YEAR(date_prevue)=YEAR(CURDATE() - INTERVAL 1 MONTH)")->fetchColumn();
$variation = $moisDernier > 0 ? round((($moisActuel - $moisDernier) / $moisDernier) * 100, 1) : null;

echo json_encode([
    "success" => true, "total" => $total, "en_cours" => $enCours, "terminees" => $terminees, "en_retard" => $enRetard,
    "variation_total" => $variation,
    "pct_en_cours" => $total > 0 ? round($enCours/$total*100, 1) : 0,
    "pct_terminees" => $total > 0 ? round($terminees/$total*100, 1) : 0,
    "pct_en_retard" => $total > 0 ? round($enRetard/$total*100, 1) : 0,
]);
