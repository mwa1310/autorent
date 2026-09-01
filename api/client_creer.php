<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/middleware_auth.php';
$pdo = getPDO();
exigerRoleParmi(['admin', 'agent']);

$d = json_decode(file_get_contents('php://input'), true);
$nom = trim($d['nom'] ?? ''); $prenom = trim($d['prenom'] ?? '');
$email = trim($d['email'] ?? ''); $telephone = trim($d['telephone'] ?? '');
$numeroPermis = trim($d['numero_permis'] ?? '');
$dateDelivrancePermis = trim($d['date_delivrance_permis'] ?? '') ?: null;
$numeroCni = trim($d['numero_CNI'] ?? '');
$adresse = trim($d['adresse'] ?? '');

$erreurs = [];
if ($nom === '') $erreurs[] = "Le nom est obligatoire.";
if ($prenom === '') $erreurs[] = "Le prénom est obligatoire.";
if ($telephone === '') $erreurs[] = "Le téléphone est obligatoire.";
if ($numeroPermis === '') $erreurs[] = "Le numéro de permis est obligatoire.";
if (!empty($erreurs)) { http_response_code(422); echo json_encode(["success" => false, "message" => implode(' ', $erreurs)]); exit; }

try {
    $stmt = $pdo->prepare(
        "INSERT INTO Clients (nom, prenom, email, numero_permis, date_delivrance_permis, numero_CNI, adresse, telephone, date_creation, etat)
         VALUES (:nom, :prenom, :email, :numero_permis, :date_delivrance_permis, :numero_CNI, :adresse, :telephone, NOW(), 'actif')"
    );
    $stmt->execute([
        "nom" => $nom, "prenom" => $prenom, "email" => $email, "numero_permis" => $numeroPermis,
        "date_delivrance_permis" => $dateDelivrancePermis, "numero_CNI" => $numeroCni, "adresse" => $adresse, "telephone" => $telephone,
    ]);
    echo json_encode(["success" => true, "message" => "Client \"$prenom $nom\" ajouté.", "id_client" => $pdo->lastInsertId()]);
} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur : " . $e->getMessage()]);
}
