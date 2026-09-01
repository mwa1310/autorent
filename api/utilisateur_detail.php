<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/middleware_auth.php';
$pdo = getPDO();

exigerRoleParmi(['admin']);

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) { http_response_code(422); echo json_encode(["success" => false, "message" => "Identifiant invalide."]); exit; }

$stmt = $pdo->prepare("SELECT id_utilisateur, nom, prenom, email, role, etat FROM Utilisateurs WHERE id_utilisateur = :id");
$stmt->execute(["id" => $id]);
$u = $stmt->fetch();

if (!$u) { http_response_code(404); echo json_encode(["success" => false, "message" => "Utilisateur introuvable."]); exit; }

echo json_encode(["success" => true, "data" => $u]);
