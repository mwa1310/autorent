<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/middleware_auth.php';
$pdo = getPDO();

$utilisateur = exigerRoleParmi(['admin', 'agent']);

$d = json_decode(file_get_contents('php://input'), true);
$idReservation = (int) ($d['id_reservation'] ?? 0);
$montant = (float) ($d['montant'] ?? 0);
$mode = trim($d['mode_paiement'] ?? '');
$statut = trim($d['statut'] ?? 'valide');

$erreurs = [];
if ($idReservation <= 0) $erreurs[] = "La réservation est obligatoire.";
if ($montant <= 0) $erreurs[] = "Le montant doit être positif.";
if (!in_array($mode, ['especes', 'virement', 'mobile_money'], true)) $erreurs[] = "Méthode de paiement invalide.";
if (!in_array($statut, ['valide', 'en_attente', 'rembourse'], true)) $erreurs[] = "Statut invalide.";

if (!empty($erreurs)) { http_response_code(422); echo json_encode(["success" => false, "message" => implode(' ', $erreurs)]); exit; }

$stmtVerif = $pdo->prepare("SELECT id_reservation FROM Reservations WHERE id_reservation = :id");
$stmtVerif->execute(["id" => $idReservation]);
if (!$stmtVerif->fetch()) { http_response_code(404); echo json_encode(["success" => false, "message" => "Réservation introuvable."]); exit; }

try {
    $stmt = $pdo->prepare(
        "INSERT INTO Paiements (id_reservation, montant, mode_paiement, statut, date_paiement, id_utilisateur)
         VALUES (:id_reservation, :montant, :mode_paiement, :statut, NOW(), :id_utilisateur)"
    );
    $stmt->execute([
        "id_reservation" => $idReservation, "montant" => $montant, "mode_paiement" => $mode,
        "statut" => $statut, "id_utilisateur" => $utilisateur['id_utilisateur'],
    ]);

    echo json_encode(["success" => true, "message" => "Paiement enregistré.", "id_paie" => $pdo->lastInsertId()]);
} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur : " . $e->getMessage()]);
}
