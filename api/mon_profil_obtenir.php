<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/middleware_auth.php';
$pdo = getPDO();

$utilisateur = exigerAuthentification();

$stmt = $pdo->prepare("SELECT id_utilisateur, nom, prenom, email, role, date_creation FROM Utilisateurs WHERE id_utilisateur = :id");
$stmt->execute(["id" => $utilisateur['id_utilisateur']]);
echo json_encode(["success" => true, "data" => $stmt->fetch()]);
