<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/middleware_auth.php';
$pdo = getPDO();

exigerAuthentification();

// Exemples de véhicules par catégorie : jusqu'à 3 couples marque+modèle distincts,
// calculés dynamiquement (pas stockés) pour rester toujours synchronisés avec le vrai parc.
$sqlExemples = "
    SELECT id_categorie, GROUP_CONCAT(vehicule_label ORDER BY vehicule_label SEPARATOR ', ') AS exemples
    FROM (
        SELECT id_categorie, CONCAT(marque, ' ', modele) AS vehicule_label,
               ROW_NUMBER() OVER (PARTITION BY id_categorie ORDER BY marque, modele) AS rn
        FROM (SELECT DISTINCT id_categorie, marque, modele FROM Vehicules WHERE etat = 'actif') d
    ) t
    WHERE rn <= 3
    GROUP BY id_categorie
";
$exemplesParCategorie = array_column($pdo->query($sqlExemples)->fetchAll(), 'exemples', 'id_categorie');

$stmt = $pdo->query(
    "SELECT cv.id_categorie, cv.nom, cv.description, cv.tarif_jour, cv.tarif_hebdomadaire, cv.tarif_mensuel, cv.etat,
            (SELECT COUNT(*) FROM Vehicules v WHERE v.id_categorie = cv.id_categorie AND v.etat = 'actif') AS nb_vehicules
     FROM Categories_vehicules cv
     ORDER BY cv.tarif_jour ASC"
);
$categories = $stmt->fetchAll();

$donnees = array_map(function ($c) use ($exemplesParCategorie) {
    return [
        "id_categorie"        => $c['id_categorie'],
        "code"                => 'CAT-' . str_pad($c['id_categorie'], 3, '0', STR_PAD_LEFT),
        "nom"                 => $c['nom'],
        "description"         => $c['description'],
        "exemples"            => $exemplesParCategorie[$c['id_categorie']] ?? '—',
        "tarif_jour"          => (float) $c['tarif_jour'],
        "tarif_hebdomadaire"  => $c['tarif_hebdomadaire'] !== null ? (float) $c['tarif_hebdomadaire'] : null,
        "tarif_mensuel"       => $c['tarif_mensuel'] !== null ? (float) $c['tarif_mensuel'] : null,
        "nb_vehicules"        => (int) $c['nb_vehicules'],
        "etat"                => $c['etat'],
    ];
}, $categories);

echo json_encode(["success" => true, "data" => $donnees, "total" => count($donnees)]);
