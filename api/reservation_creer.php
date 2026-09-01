<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/middleware_auth.php';
$pdo = getPDO();

$utilisateur = exigerRoleParmi(['admin', 'agent']);

$d = json_decode(file_get_contents('php://input'), true);
$idClient = (int) ($d['id_client'] ?? 0);
$idVehicule = (int) ($d['id_vehicule'] ?? 0);
$dateDebut = trim($d['date_debut'] ?? '');
$dateFin = trim($d['date_fin'] ?? '');
$statut = trim($d['statut'] ?? 'reservee');
$montantTotal = isset($d['montant_total']) ? (float) $d['montant_total'] : null;

$erreurs = [];
if ($idClient <= 0) $erreurs[] = "Le client est obligatoire.";
if ($idVehicule <= 0) $erreurs[] = "Le véhicule est obligatoire.";
if (!$dateDebut || !$dateFin) $erreurs[] = "Les dates de début et de fin sont obligatoires.";
elseif (strtotime($dateFin) <= strtotime($dateDebut)) $erreurs[] = "La date de fin doit être postérieure à la date de début.";
if (!in_array($statut, ['reservee', 'en_cours', 'terminee', 'annulee'], true)) $erreurs[] = "Statut invalide.";
if ($montantTotal === null || $montantTotal < 0) $erreurs[] = "Montant total invalide.";

if (!empty($erreurs)) { http_response_code(422); echo json_encode(["success" => false, "message" => implode(' ', $erreurs)]); exit; }

// --- Re-vérification serveur du chevauchement (ne jamais faire confiance au seul filtrage de l'autocomplétion) ---
$stmtChevauchement = $pdo->prepare(
    "SELECT COUNT(*) FROM Reservations
     WHERE id_vehicule = :id_vehicule AND statut IN ('reservee', 'en_cours')
       AND date_debut < :date_fin AND date_fin > :date_debut"
);
$stmtChevauchement->execute(["id_vehicule" => $idVehicule, "date_debut" => $dateDebut, "date_fin" => $dateFin]);
if ((int) $stmtChevauchement->fetchColumn() > 0) {
    http_response_code(409);
    echo json_encode(["success" => false, "message" => "Ce véhicule est déjà réservé sur une période qui chevauche celle demandée."]);
    exit;
}

try {
    $stmt = $pdo->prepare(
        "INSERT INTO Reservations (id_client, id_vehicule, id_utilisateur, date_debut, date_fin, date_reservation, statut, montant_total)
         VALUES (:id_client, :id_vehicule, :id_utilisateur, :date_debut, :date_fin, NOW(), :statut, :montant_total)"
    );
    $stmt->execute([
        "id_client" => $idClient, "id_vehicule" => $idVehicule, "id_utilisateur" => $utilisateur['id_utilisateur'],
        "date_debut" => $dateDebut, "date_fin" => $dateFin, "statut" => $statut, "montant_total" => $montantTotal,
    ]);

    echo json_encode(["success" => true, "message" => "Réservation créée avec succès.", "id_reservation" => $pdo->lastInsertId()]);
} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur : " . $e->getMessage()]);
}
