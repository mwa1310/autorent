<?php

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/middleware_auth.php';
$pdo = getPDO();

exigerAuthentification();

$recherche   = trim($_GET['recherche'] ?? '');
$idCategorie = isset($_GET['id_categorie']) && $_GET['id_categorie'] !== '' ? (int) $_GET['id_categorie'] : null;
$statut    = trim($_GET['statut'] ?? '');
$carburant   = trim($_GET['carburant'] ?? '');
$tri      = trim($_GET['tri'] ?? 'recents');
$page      = max(1, (int) ($_GET['page'] ?? 1));
$parPage     = 10;
$offset      = ($page - 1) * $parPage;

$where = ["v.etat = 'actif'"];
$params = [];

if ($recherche !== '') {
    $where[] = "(v.immatriculation LIKE :r1 OR v.marque LIKE :r2 OR v.modele LIKE :r3)";
    $valeur = '%' . $recherche . '%';
    $params['r1'] = $valeur; $params['r2'] = $valeur; $params['r3'] = $valeur;
}
if ($idCategorie) {
    $where[] = "v.id_categorie = :id_categorie";
    $params['id_categorie'] = $idCategorie;
}
if (in_array($statut, ['disponible', 'en_location', 'maintenance', 'hors_service'], true)) {
    $where[] = "v.statut_actuel = :statut";
    $params['statut'] = $statut;
}
if (in_array($carburant, ['essence', 'diesel', 'hybride', 'electrique'], true)) {
    $where[] = "v.carburant = :carburant";
    $params['carburant'] = $carburant;
}

$clauseWhere = implode(' AND ', $where);

$triSql = match ($tri) {
    'anciens'   => 'v.date_ajout ASC',
    'km_asc'    => 'v.kilometrage ASC',
    'km_desc'   => 'v.kilometrage DESC',
    'annee_desc'=> 'v.annee DESC',
    'annee_asc' => 'v.annee ASC',
    default     => 'v.date_ajout DESC', // 'recents'
};

// Total
$stmtCount = $pdo->prepare("SELECT COUNT(*) FROM Vehicules v WHERE $clauseWhere");
$stmtCount->execute($params);
$total = (int) $stmtCount->fetchColumn();

// Données de la page
$sql = "SELECT v.id_vehicule, v.immatriculation, v.marque, v.modele, v.annee, v.carburant,
               v.kilometrage, v.statut_actuel, v.id_categorie, cv.nom AS categorie_nom,
               COALESCE(
                   (SELECT MAX(h.date) FROM Historique_statut_vehicule h WHERE h.id_vehicule = v.id_vehicule),
                   v.date_ajout
               ) AS derniere_maj
        FROM Vehicules v
        JOIN Categories_vehicules cv ON cv.id_categorie = v.id_categorie
        WHERE $clauseWhere
        ORDER BY $triSql
        LIMIT :offset, :parPage";

$stmt = $pdo->prepare($sql);
foreach ($params as $cle => $valeur) {
    $stmt->bindValue(':' . $cle, $valeur, PDO::PARAM_STR);
}
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':parPage', $parPage, PDO::PARAM_INT);
$stmt->execute();

echo json_encode([
    "success"     => true,
    "data"     => $stmt->fetchAll(),
    "total"     => $total,
    "page"     => $page,
    "par_page"    => $parPage,
    "total_pages" => (int) ceil($total / $parPage),
]);
