<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/middleware_auth.php';
$pdo = getPDO();

exigerRoleParmi(['admin']);

$id = (int) ($_GET['id'] ?? 0);
$d = json_decode(file_get_contents('php://input'), true);

$erreurs = [];
$marque = trim($d['marque'] ?? '');
$modele = trim($d['modele'] ?? '');
$idCategorie = (int) ($d['id_categorie'] ?? 0);
$annee = (int) ($d['annee'] ?? 0);
$carburant = trim($d['carburant'] ?? '');
$kilometrage = (int) ($d['kilometrage'] ?? 0);

if ($id <= 0) $erreurs[] = "Identifiant invalide.";
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
    $stmt = $pdo->prepare(
        "UPDATE Vehicules SET marque = :marque, modele = :modele, id_categorie = :id_categorie,
                               annee = :annee, carburant = :carburant, kilometrage = :kilometrage
         WHERE id_vehicule = :id"
    );
    $stmt->execute([
        "marque" => $marque, "modele" => $modele, "id_categorie" => $idCategorie,
        "annee" => $annee, "carburant" => $carburant, "kilometrage" => $kilometrage, "id" => $id,
    ]);

    echo json_encode(["success" => true, "message" => "Véhicule modifié avec succès."]);
} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur : " . $e->getMessage()]);
}
