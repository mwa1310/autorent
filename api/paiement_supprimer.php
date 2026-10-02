<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/middleware_auth.php';
require_once __DIR__ . '/../includes/journal.php';
$pdo = getPDO();

$utilisateurConnecte = exigerRoleParmi(['admin']);

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) { http_response_code(422); echo json_encode(["success" => false, "message" => "Identifiant invalide."]); exit; }

// Récupéré avant suppression, pour garder une description lisible dans le journal.
$stmtVerif = $pdo->prepare("SELECT montant, id_reservation FROM Paiements WHERE id_paie = :id");
$stmtVerif->execute(["id" => $id]);
$paiement = $stmtVerif->fetch();
if (!$paiement) { http_response_code(404); echo json_encode(["success" => false, "message" => "Paiement introuvable."]); exit; }

$stmt = $pdo->prepare("DELETE FROM Paiements WHERE id_paie = :id");
$stmt->execute(["id" => $id]);

enregistrerActivite(
    $pdo,
    $utilisateurConnecte,
    'suppression',
    'paiement',
    $id,
    "Suppression d'un paiement de {$paiement['montant']} FCFA (réservation #{$paiement['id_reservation']})"
);

echo json_encode(["success" => true, "message" => "Paiement supprimé."]);