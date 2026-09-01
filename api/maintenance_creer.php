<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/middleware_auth.php';
$pdo = getPDO();
$utilisateur = exigerRoleParmi(['admin', 'agent']);

$d = json_decode(file_get_contents('php://input'), true);
$idVehicule = (int) ($d['id_vehicule'] ?? 0);
$type = trim($d['type_maintenance'] ?? '');
$fournisseur = trim($d['fournisseur'] ?? '');
$datePrevue = trim($d['date_prevue'] ?? '');
$cout = (float) ($d['cout'] ?? 0);
$description = trim($d['description'] ?? '');
$statut = trim($d['statut'] ?? 'planifiee');

$erreurs = [];
if ($idVehicule <= 0) $erreurs[] = "Le véhicule est obligatoire.";
if ($type === '') $erreurs[] = "Le type d'intervention est obligatoire.";
if ($datePrevue === '') $erreurs[] = "La date prévue est obligatoire.";
if (!in_array($statut, ['planifiee', 'en_cours', 'terminee', 'annulee'], true)) $erreurs[] = "Statut invalide.";
if (!empty($erreurs)) { http_response_code(422); echo json_encode(["success" => false, "message" => implode(' ', $erreurs)]); exit; }

try {
    $stmt = $pdo->prepare(
        "INSERT INTO Maintenance (id_vehicule, type_maintenance, fournisseur, date_prevue, date_realisee, statut, cout, description, id_utilisateur, etat)
         VALUES (:id_vehicule, :type, :fournisseur, :date_prevue, :date_realisee, :statut, :cout, :description, :id_utilisateur, 'actif')"
    );
    $stmt->execute([
        "id_vehicule" => $idVehicule, "type" => $type, "fournisseur" => $fournisseur ?: null, "date_prevue" => $datePrevue,
        "date_realisee" => $statut === 'terminee' ? $datePrevue : null, "statut" => $statut, "cout" => $cout,
        "description" => $description, "id_utilisateur" => $utilisateur['id_utilisateur'],
    ]);

    // Si l'intervention est créée directement en cours/terminée, on bascule le véhicule en maintenance
    if (in_array($statut, ['planifiee', 'en_cours'], true) && $statut === 'en_cours') {
        $pdo->prepare("UPDATE Vehicules SET statut_actuel = 'maintenance' WHERE id_vehicule = :id")->execute(["id" => $idVehicule]);
    }

    echo json_encode(["success" => true, "message" => "Intervention créée.", "id_maintenance" => $pdo->lastInsertId()]);
} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur : " . $e->getMessage()]);
}
