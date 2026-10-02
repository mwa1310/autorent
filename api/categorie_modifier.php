<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/middleware_auth.php';
require_once __DIR__ . '/../includes/journal.php';
$pdo = getPDO();

$utilisateurConnecte = exigerRoleParmi(['admin']);

$id = (int) ($_GET['id'] ?? 0);
$d = json_decode(file_get_contents('php://input'), true);
$nom = trim($d['nom'] ?? '');
$description = trim($d['description'] ?? '');
$tarifJour = (float) ($d['tarif_jour'] ?? 0);
$tarifHebdo = isset($d['tarif_hebdomadaire']) && $d['tarif_hebdomadaire'] !== '' ? (float) $d['tarif_hebdomadaire'] : null;
$tarifMensuel = isset($d['tarif_mensuel']) && $d['tarif_mensuel'] !== '' ? (float) $d['tarif_mensuel'] : null;

$erreurs = [];
if ($id <= 0) $erreurs[] = "Identifiant invalide.";
if ($nom === '') $erreurs[] = "Le nom est obligatoire.";
if ($tarifJour <= 0) $erreurs[] = "Le tarif journalier doit être positif.";

if (!empty($erreurs)) { http_response_code(422); echo json_encode(["success" => false, "message" => implode(' ', $erreurs)]); exit; }

// Idem qu'à la création : on garde des tarifs cohérents plutôt que de laisser vide
// Multiplicateurs configurables depuis la page Paramètres (valeurs par défaut si jamais absentes)
$stmtParams = $pdo->query("SELECT jours_semaine_facturation, jours_mois_facturation FROM Parametres WHERE id = 1");
$reglages = $stmtParams->fetch();
$joursSemaine = $reglages['jours_semaine_facturation'] ?? 6;
$joursMois = $reglages['jours_mois_facturation'] ?? 24;

if ($tarifHebdo === null) $tarifHebdo = round($tarifJour * $joursSemaine, 2);
if ($tarifMensuel === null) $tarifMensuel = round($tarifJour * $joursMois, 2);

try {
    $stmt = $pdo->prepare(
        "UPDATE Categories_vehicules SET nom = :nom, tarif_jour = :tarif_jour,
                tarif_hebdomadaire = :tarif_hebdomadaire, tarif_mensuel = :tarif_mensuel, description = :description
         WHERE id_categorie = :id"
    );
    $stmt->execute([
        "nom" => $nom, "tarif_jour" => $tarifJour, "tarif_hebdomadaire" => $tarifHebdo,
        "tarif_mensuel" => $tarifMensuel, "description" => $description, "id" => $id,
    ]);

    enregistrerActivite(
        $pdo,
        $utilisateurConnecte,
        'modification',
        'categorie',
        $id,
        "Modification de la catégorie \"$nom\" (tarif journalier : $tarifJour FCFA)"
    );

    echo json_encode(["success" => true, "message" => "Catégorie modifiée."]);
} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur : " . $e->getMessage()]);
}