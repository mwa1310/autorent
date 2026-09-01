<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/middleware_auth.php';
$pdo = getPDO();

exigerAuthentification();

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) { http_response_code(422); echo json_encode(["success" => false, "message" => "Identifiant invalide."]); exit; }

$stmt = $pdo->prepare(
    "SELECT p.*, c.nom AS client_nom, c.prenom AS client_prenom
     FROM Paiements p
     JOIN Reservations r ON r.id_reservation = p.id_reservation
     JOIN Clients c ON c.id_client = r.id_client
     WHERE p.id_paie = :id"
);
$stmt->execute(["id" => $id]);
$paiement = $stmt->fetch();

if (!$paiement) { http_response_code(404); echo json_encode(["success" => false, "message" => "Paiement introuvable."]); exit; }

echo json_encode(["success" => true, "data" => $paiement]);
