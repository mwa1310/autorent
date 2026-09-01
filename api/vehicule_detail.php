<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/middleware_auth.php';
$pdo = getPDO();

exigerAuthentification();

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Identifiant invalide."]);
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM Vehicules WHERE id_vehicule = :id");
$stmt->execute(["id" => $id]);
$vehicule = $stmt->fetch();

if (!$vehicule) {
    http_response_code(404);
    echo json_encode(["success" => false, "message" => "Véhicule introuvable."]);
    exit;
}

echo json_encode(["success" => true, "data" => $vehicule]);
