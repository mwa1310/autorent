<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/middleware_auth.php';
$pdo = getPDO();

$utilisateurConnecte = exigerRoleParmi(['admin']);

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) { http_response_code(422); echo json_encode(["success" => false, "message" => "Identifiant invalide."]); exit; }

if ($id === (int) $utilisateurConnecte['id_utilisateur']) {
    http_response_code(403);
    echo json_encode(["success" => false, "message" => "Vous ne pouvez pas désactiver votre propre compte."]);
    exit;
}

$stmt = $pdo->prepare("SELECT etat FROM Utilisateurs WHERE id_utilisateur = :id");
$stmt->execute(["id" => $id]);
$u = $stmt->fetch();
if (!$u) { http_response_code(404); echo json_encode(["success" => false, "message" => "Utilisateur introuvable."]); exit; }

$nouvelEtat = $u['etat'] === 'actif' ? 'inactif' : 'actif';
$stmtMaj = $pdo->prepare("UPDATE Utilisateurs SET etat = :etat WHERE id_utilisateur = :id");
$stmtMaj->execute(["etat" => $nouvelEtat, "id" => $id]);

echo json_encode(["success" => true, "message" => "Compte " . ($nouvelEtat === 'actif' ? 'activé' : 'désactivé') . ".", "nouvel_etat" => $nouvelEtat]);
