<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/middleware_auth.php';
$pdo = getPDO();

exigerAuthentification();

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) { http_response_code(422); echo json_encode(["success" => false, "message" => "Identifiant invalide."]); exit; }

$stmt = $pdo->prepare(
    "SELECT r.*, c.nom AS client_nom, c.prenom AS client_prenom, c.telephone AS client_telephone,
            v.marque, v.modele, v.immatriculation
     FROM Reservations r
     JOIN Clients c ON c.id_client = r.id_client
     JOIN Vehicules v ON v.id_vehicule = r.id_vehicule
     WHERE r.id_reservation = :id"
);
$stmt->execute(["id" => $id]);
$reservation = $stmt->fetch();

if (!$reservation) { http_response_code(404); echo json_encode(["success" => false, "message" => "Réservation introuvable."]); exit; }

echo json_encode(["success" => true, "data" => $reservation]);
