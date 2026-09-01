<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/middleware_auth.php';
$pdo = getPDO();
exigerAuthentification();

$recherche = trim($_GET['recherche'] ?? '');
$statut = trim($_GET['statut'] ?? '');
$type = trim($_GET['type_maintenance'] ?? '');
$idVehicule = (int) ($_GET['id_vehicule'] ?? 0);
$periodeDebut = trim($_GET['periode_debut'] ?? '');
$periodeFin = trim($_GET['periode_fin'] ?? '');
$tri = trim($_GET['tri'] ?? 'date_desc');
$page = max(1, (int) ($_GET['page'] ?? 1));
$parPage = 10;
$offset = ($page - 1) * $parPage;

$where = ["m.etat = 'actif'"];
$params = [];
if ($recherche !== '') {
    $where[] = "(m.id_maintenance LIKE :r1 OR v.marque LIKE :r2 OR v.modele LIKE :r3 OR v.immatriculation LIKE :r4)";
    $val = '%' . $recherche . '%';
    $params['r1'] = $val; $params['r2'] = $val; $params['r3'] = $val; $params['r4'] = $val;
}
if ($statut === 'en_retard') {
    $where[] = "m.statut IN ('planifiee','en_cours') AND m.date_prevue < CURDATE()";
} elseif (in_array($statut, ['planifiee', 'en_cours', 'terminee', 'annulee'], true)) {
    $where[] = "m.statut = :statut"; $params['statut'] = $statut;
}
if ($type !== '') { $where[] = "m.type_maintenance = :type"; $params['type'] = $type; }
if ($idVehicule > 0) { $where[] = "m.id_vehicule = :id_vehicule"; $params['id_vehicule'] = $idVehicule; }
if ($periodeDebut !== '') { $where[] = "m.date_prevue >= :periode_debut"; $params['periode_debut'] = $periodeDebut; }
if ($periodeFin !== '') { $where[] = "m.date_prevue <= :periode_fin"; $params['periode_fin'] = $periodeFin; }

$clauseWhere = implode(' AND ', $where);
$triSql = match ($tri) {
    'cout_desc' => 'm.cout DESC', 'cout_asc' => 'm.cout ASC', 'date_asc' => 'm.date_prevue ASC',
    default => 'm.date_prevue DESC',
};

$jointures = "FROM Maintenance m JOIN Vehicules v ON v.id_vehicule = m.id_vehicule";
$stmtCount = $pdo->prepare("SELECT COUNT(*) $jointures WHERE $clauseWhere");
$stmtCount->execute($params);
$total = (int) $stmtCount->fetchColumn();

$sql = "SELECT m.id_maintenance, m.type_maintenance, m.fournisseur, m.date_prevue, m.date_realisee, m.statut, m.cout,
               v.id_vehicule, v.marque, v.modele, v.immatriculation, cv.nom AS categorie,
               (SELECT MIN(m2.date_prevue) FROM Maintenance m2 WHERE m2.id_vehicule = m.id_vehicule AND m2.statut = 'planifiee' AND m2.date_prevue > CURDATE()) AS prochaine_maintenance
        $jointures
        JOIN Categories_vehicules cv ON cv.id_categorie = v.id_categorie
        WHERE $clauseWhere
        ORDER BY $triSql
        LIMIT :offset, :parPage";
$stmt = $pdo->prepare($sql);
foreach ($params as $k => $v) $stmt->bindValue(':' . $k, $v, PDO::PARAM_STR);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':parPage', $parPage, PDO::PARAM_INT);
$stmt->execute();

$libelles = ['planifiee' => 'En attente', 'en_cours' => 'En cours', 'terminee' => 'Terminée', 'annulee' => 'Annulée'];

$donnees = array_map(function ($m) use ($libelles) {
    $enRetard = in_array($m['statut'], ['planifiee', 'en_cours'], true) && $m['date_prevue'] < date('Y-m-d');
    return [
        "id_maintenance" => $m['id_maintenance'],
        "reference" => 'MAI-' . date('Y') . '-' . str_pad($m['id_maintenance'], 4, '0', STR_PAD_LEFT),
        "vehicule" => $m['marque'] . ' ' . $m['modele'],
        "categorie" => $m['categorie'],
        "immatriculation" => $m['immatriculation'],
        "id_vehicule" => $m['id_vehicule'],
        "type_maintenance" => $m['type_maintenance'],
        "fournisseur" => $m['fournisseur'] ?: '—',
        "cout" => (float) $m['cout'],
        "statut" => $enRetard ? 'en_retard' : $m['statut'],
        "statut_libelle" => $enRetard ? 'En retard' : ($libelles[$m['statut']] ?? $m['statut']),
        "date_prevue" => $m['date_prevue'],
        "date_realisee" => $m['date_realisee'],
        "prochaine_maintenance" => $m['prochaine_maintenance'],
    ];
}, $stmt->fetchAll());

echo json_encode(["success" => true, "data" => $donnees, "total" => $total, "page" => $page, "par_page" => $parPage, "total_pages" => (int) ceil($total / $parPage)]);
