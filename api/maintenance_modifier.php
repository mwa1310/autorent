<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/middleware_auth.php';
$pdo = getPDO();
exigerRoleParmi(['admin', 'agent']);

$id = (int) ($_GET['id'] ?? 0);
$d = json_decode(file_get_contents('php://input'), true);
$type = trim($d['type_maintenance'] ?? '');
$fournisseur = trim($d['fournisseur'] ?? '');
$datePrevue = trim($d['date_prevue'] ?? '');
$cout = (float) ($d['cout'] ?? 0);
$description = trim($d['description'] ?? '');
$statut = trim($d['statut'] ?? '');

$erreurs = [];
if ($id <= 0) $erreurs[] = "Identifiant invalide.";
if ($type === '') $erreurs[] = "Le type d'intervention est obligatoire.";
if ($datePrevue === '') $erreurs[] = "La date prévue est obligatoire.";
if (!in_array($statut, ['planifiee', 'en_cours', 'terminee', 'annulee'], true)) $erreurs[] = "Statut invalide.";
if (!empty($erreurs)) { http_response_code(422); echo json_encode(["success" => false, "message" => implode(' ', $erreurs)]); exit; }

$stmtVehicule = $pdo->prepare("SELECT id_vehicule FROM Maintenance WHERE id_maintenance = :id");
$stmtVehicule->execute(["id" => $id]);
$row = $stmtVehicule->fetch();
if (!$row) { http_response_code(404); echo json_encode(["success" => false, "message" => "Intervention introuvable."]); exit; }

try {
    $stmt = $pdo->prepare(
        "UPDATE Maintenance SET type_maintenance=:type, fournisseur=:fournisseur, date_prevue=:date_prevue,
                date_realisee=:date_realisee, statut=:statut, cout=:cout, description=:description
         WHERE id_maintenance=:id"
    );
    $stmt->execute([
        "type" => $type, "fournisseur" => $fournisseur ?: null, "date_prevue" => $datePrevue,
        "date_realisee" => $statut === 'terminee' ? ($d['date_realisee'] ?? date('Y-m-d')) : null,
        "statut" => $statut, "cout" => $cout, "description" => $description, "id" => $id,
    ]);

    // Synchronise le statut du véhicule
    if ($statut === 'en_cours') {
        $pdo->prepare("UPDATE Vehicules SET statut_actuel = 'maintenance' WHERE id_vehicule = :id")->execute(["id" => $row['id_vehicule']]);
    } elseif (in_array($statut, ['terminee', 'annulee'], true)) {
        $pdo->prepare("UPDATE Vehicules SET statut_actuel = 'disponible' WHERE id_vehicule = :id AND statut_actuel = 'maintenance'")->execute(["id" => $row['id_vehicule']]);
    }

    echo json_encode(["success" => true, "message" => "Intervention modifiée."]);
} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur : " . $e->getMessage()]);
}
