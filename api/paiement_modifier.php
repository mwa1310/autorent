<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/middleware_auth.php';
$pdo = getPDO();

exigerRoleParmi(['admin', 'agent']);

$id = (int) ($_GET['id'] ?? 0);
$d = json_decode(file_get_contents('php://input'), true);
$montant = (float) ($d['montant'] ?? 0);
$mode = trim($d['mode_paiement'] ?? '');
$statut = trim($d['statut'] ?? '');

$erreurs = [];
if ($id <= 0) $erreurs[] = "Identifiant invalide.";
if ($montant <= 0) $erreurs[] = "Le montant doit être positif.";
if (!in_array($mode, ['especes', 'virement', 'mobile_money'], true)) $erreurs[] = "Méthode de paiement invalide.";
if (!in_array($statut, ['valide', 'en_attente', 'rembourse'], true)) $erreurs[] = "Statut invalide.";

if (!empty($erreurs)) { http_response_code(422); echo json_encode(["success" => false, "message" => implode(' ', $erreurs)]); exit; }

try {
    $stmt = $pdo->prepare("UPDATE Paiements SET montant = :montant, mode_paiement = :mode, statut = :statut WHERE id_paie = :id");
    $stmt->execute(["montant" => $montant, "mode" => $mode, "statut" => $statut, "id" => $id]);
    echo json_encode(["success" => true, "message" => "Paiement modifié."]);
} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur : " . $e->getMessage()]);
}
