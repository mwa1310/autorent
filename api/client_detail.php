<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/middleware_auth.php';
$pdo = getPDO();
exigerAuthentification();

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) { http_response_code(422); echo json_encode(["success" => false, "message" => "Identifiant invalide."]); exit; }

$stmt = $pdo->prepare("SELECT * FROM Clients WHERE id_client = :id");
$stmt->execute(["id" => $id]);
$client = $stmt->fetch();
if (!$client) { http_response_code(404); echo json_encode(["success" => false, "message" => "Client introuvable."]); exit; }

echo json_encode(["success" => true, "data" => $client]);
