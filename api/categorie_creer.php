<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/middleware_auth.php';
$pdo = getPDO();

exigerRoleParmi(['admin']);

$d = json_decode(file_get_contents('php://input'), true);
$nom = trim($d['nom'] ?? '');
$description = trim($d['description'] ?? '');
$tarifJour = (float) ($d['tarif_jour'] ?? 0);
$tarifHebdo = isset($d['tarif_hebdomadaire']) && $d['tarif_hebdomadaire'] !== '' ? (float) $d['tarif_hebdomadaire'] : null;
$tarifMensuel = isset($d['tarif_mensuel']) && $d['tarif_mensuel'] !== '' ? (float) $d['tarif_mensuel'] : null;

$erreurs = [];
if ($nom === '') $erreurs[] = "Le nom est obligatoire.";
if ($tarifJour <= 0) $erreurs[] = "Le tarif journalier doit être positif.";

if (!empty($erreurs)) { http_response_code(422); echo json_encode(["success" => false, "message" => implode(' ', $erreurs)]); exit; }

// Si les tarifs hebdo/mensuel ne sont pas précisés, on les déduit du tarif journalier
// (6 jours facturés/semaine, 24 jours facturés/mois) plutôt que de les laisser vides.
if ($tarifHebdo === null) $tarifHebdo = round($tarifJour * 6, 2);
if ($tarifMensuel === null) $tarifMensuel = round($tarifJour * 24, 2);

try {
    $stmtVerif = $pdo->prepare("SELECT id_categorie FROM Categories_vehicules WHERE nom = :nom");
    $stmtVerif->execute(["nom" => $nom]);
    if ($stmtVerif->fetch()) {
        http_response_code(409);
        echo json_encode(["success" => false, "message" => "Cette catégorie existe déjà."]);
        exit;
    }

    $stmt = $pdo->prepare(
        "INSERT INTO Categories_vehicules (nom, tarif_jour, tarif_hebdomadaire, tarif_mensuel, description, etat)
         VALUES (:nom, :tarif_jour, :tarif_hebdomadaire, :tarif_mensuel, :description, 'actif')"
    );
    $stmt->execute([
        "nom" => $nom, "tarif_jour" => $tarifJour, "tarif_hebdomadaire" => $tarifHebdo,
        "tarif_mensuel" => $tarifMensuel, "description" => $description,
    ]);

    echo json_encode(["success" => true, "message" => "Catégorie \"$nom\" créée.", "id_categorie" => $pdo->lastInsertId()]);
} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur : " . $e->getMessage()]);
}
