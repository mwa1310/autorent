<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/middleware_auth.php';
$pdo = getPDO();

exigerRoleParmi(['admin', 'agent']);

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) { http_response_code(422); echo json_encode(["success" => false, "message" => "Identifiant invalide."]); exit; }

$stmt = $pdo->prepare("SELECT statut FROM Reservations WHERE id_reservation = :id");
$stmt->execute(["id" => $id]);
$reservation = $stmt->fetch();

if (!$reservation) { http_response_code(404); echo json_encode(["success" => false, "message" => "Réservation introuvable."]); exit; }
if ($reservation['statut'] === 'terminee') { http_response_code(409); echo json_encode(["success" => false, "message" => "Une réservation déjà terminée ne peut pas être annulée."]); exit; }

$stmtMaj = $pdo->prepare("UPDATE Reservations SET statut = 'annulee' WHERE id_reservation = :id");
$stmtMaj->execute(["id" => $id]);

echo json_encode(["success" => true, "message" => "Réservation annulée."]);
