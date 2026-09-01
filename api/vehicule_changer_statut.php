<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/middleware_auth.php';
$pdo = getPDO();

$utilisateur = exigerRoleParmi(['admin']);

$d = json_decode(file_get_contents('php://input'), true);
$id = (int) ($d['id_vehicule'] ?? 0);
$nouveauStatut = trim($d['nouveau_statut'] ?? '');
$raison = trim($d['raison'] ?? '');

if ($id <= 0 || !in_array($nouveauStatut, ['disponible', 'en_location', 'maintenance', 'hors_service'], true)) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Paramètres invalides."]);
    exit;
}

$stmtActuel = $pdo->prepare("SELECT statut_actuel FROM Vehicules WHERE id_vehicule = :id");
$stmtActuel->execute(["id" => $id]);
$vehicule = $stmtActuel->fetch();

if (!$vehicule) {
    http_response_code(404);
    echo json_encode(["success" => false, "message" => "Véhicule introuvable."]);
    exit;
}

$pdo->beginTransaction();
try {
    $stmtMaj = $pdo->prepare("UPDATE Vehicules SET statut_actuel = :statut WHERE id_vehicule = :id");
    $stmtMaj->execute(["statut" => $nouveauStatut, "id" => $id]);

    $stmtHisto = $pdo->prepare(
        "INSERT INTO Historique_statut_vehicule (id_vehicule, ancien_statut, nouveau_statut, id_utilisateur, date, raison)
         VALUES (:id_vehicule, :ancien, :nouveau, :id_utilisateur, NOW(), :raison)"
    );
    $stmtHisto->execute([
        "id_vehicule" => $id, "ancien" => $vehicule['statut_actuel'], "nouveau" => $nouveauStatut,
        "id_utilisateur" => $utilisateur['id_utilisateur'], "raison" => $raison ?: null,
    ]);

    $pdo->commit();
    echo json_encode(["success" => true, "message" => "Statut mis à jour."]);
} catch (\PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur : " . $e->getMessage()]);
}
