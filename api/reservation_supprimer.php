<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/middleware_auth.php';
$pdo = getPDO();

exigerRoleParmi(['admin']);

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) { http_response_code(422); echo json_encode(["success" => false, "message" => "Identifiant invalide."]); exit; }

try {
    $stmt = $pdo->prepare("DELETE FROM Reservations WHERE id_reservation = :id");
    $stmt->execute(["id" => $id]);
    echo json_encode(["success" => true, "message" => "Réservation supprimée."]);
} catch (\PDOException $e) {
    // Cas fréquent : la réservation est liée à des paiements ou états des lieux (contrainte de clé étrangère)
    http_response_code(409);
    echo json_encode(["success" => false, "message" => "Impossible de supprimer : cette réservation a des paiements ou états des lieux associés."]);
}
