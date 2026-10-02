<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/middleware_auth.php';
require_once __DIR__ . '/../includes/journal.php';
$pdo = getPDO();

$utilisateur = exigerRoleParmi(['admin']);

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) { http_response_code(422); echo json_encode(["success" => false, "message" => "Identifiant invalide."]); exit; }

try {
    $reference = 'RES-' . str_pad($id, 6, '0', STR_PAD_LEFT);
    $stmt = $pdo->prepare("DELETE FROM Reservations WHERE id_reservation = :id");
    $stmt->execute(["id" => $id]);
    enregistrerActivite($pdo, $utilisateur, 'suppression', 'reservation', $id,
        "Suppression definitive de la reservation $reference");
    echo json_encode(["success" => true, "message" => "Reservation supprimee."]);
} catch (\PDOException $e) {
    // Cas fréquent : la réservation est liée à des paiements ou états des lieux (contrainte de clé étrangère)
    http_response_code(409);
    echo json_encode(["success" => false, "message" => "Impossible de supprimer : cette réservation a des paiements ou états des lieux associés."]);
}
