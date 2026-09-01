<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/middleware_auth.php';
$pdo = getPDO();

exigerRoleParmi(['admin']);

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) { http_response_code(422); echo json_encode(["success" => false, "message" => "Identifiant invalide."]); exit; }

$stmt = $pdo->prepare("SELECT etat FROM Categories_vehicules WHERE id_categorie = :id");
$stmt->execute(["id" => $id]);
$categorie = $stmt->fetch();
if (!$categorie) { http_response_code(404); echo json_encode(["success" => false, "message" => "Catégorie introuvable."]); exit; }

$nouvelEtat = $categorie['etat'] === 'actif' ? 'inactif' : 'actif';
$stmtMaj = $pdo->prepare("UPDATE Categories_vehicules SET etat = :etat WHERE id_categorie = :id");
$stmtMaj->execute(["etat" => $nouvelEtat, "id" => $id]);

echo json_encode(["success" => true, "message" => "Catégorie " . ($nouvelEtat === 'actif' ? 'activée' : 'désactivée') . ".", "nouvel_etat" => $nouvelEtat]);
