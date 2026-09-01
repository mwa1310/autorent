<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/middleware_auth.php';
$pdo = getPDO();

exigerRoleParmi(['admin']);

$d = json_decode(file_get_contents('php://input'), true);

$erreurs = [];
$immatriculation = trim($d['immatriculation'] ?? '');
$marque = trim($d['marque'] ?? '');
$modele = trim($d['modele'] ?? '');
$idCategorie = (int) ($d['id_categorie'] ?? 0);
$annee = (int) ($d['annee'] ?? 0);
$carburant = trim($d['carburant'] ?? '');
$kilometrage = (int) ($d['kilometrage'] ?? 0);

if ($immatriculation === '') $erreurs[] = "L'immatriculation est obligatoire.";
if ($marque === '') $erreurs[] = "La marque est obligatoire.";
if ($modele === '') $erreurs[] = "Le modèle est obligatoire.";
if ($idCategorie <= 0) $erreurs[] = "La catégorie est obligatoire.";
if ($annee < 1990 || $annee > (int) date('Y') + 1) $erreurs[] = "Année invalide.";
if (!in_array($carburant, ['essence', 'diesel', 'hybride', 'electrique'], true)) $erreurs[] = "Carburant invalide.";
if ($kilometrage < 0) $erreurs[] = "Le kilométrage ne peut pas être négatif.";

if (!empty($erreurs)) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => implode(' ', $erreurs)]);
    exit;
}

try {
    $stmtVerif = $pdo->prepare("SELECT id_vehicule FROM Vehicules WHERE immatriculation = :i");
    $stmtVerif->execute(["i" => $immatriculation]);
    if ($stmtVerif->fetch()) {
        http_response_code(409);
        echo json_encode(["success" => false, "message" => "Cette immatriculation existe déjà."]);
        exit;
    }

    $stmt = $pdo->prepare(
        "INSERT INTO Vehicules (immatriculation, marque, modele, id_categorie, annee, carburant, kilometrage, date_ajout, statut_actuel, etat)
         VALUES (:immatriculation, :marque, :modele, :id_categorie, :annee, :carburant, :kilometrage, NOW(), 'disponible', 'actif')"
    );
    $stmt->execute([
        "immatriculation" => $immatriculation, "marque" => $marque, "modele" => $modele,
        "id_categorie" => $idCategorie, "annee" => $annee, "carburant" => $carburant, "kilometrage" => $kilometrage,
    ]);

    echo json_encode(["success" => true, "message" => "Véhicule \"$marque $modele\" ajouté avec succès.", "id_vehicule" => $pdo->lastInsertId()]);
} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur : " . $e->getMessage()]);
}