<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/middleware_auth.php';
$pdo = getPDO();

exigerAuthentification();

$recherche    = trim($_GET['recherche'] ?? '');
$statut       = trim($_GET['statut'] ?? '');
$idClient     = (int) ($_GET['id_client'] ?? 0);
$idCategorie  = (int) ($_GET['id_categorie'] ?? 0);
$periodeDebut = trim($_GET['periode_debut'] ?? '');
$periodeFin   = trim($_GET['periode_fin'] ?? '');
$tri          = trim($_GET['tri'] ?? 'creation_desc');
$page         = max(1, (int) ($_GET['page'] ?? 1));
$parPage      = 10;
$offset       = ($page - 1) * $parPage;

$where = [];
$params = [];

if ($recherche !== '') {
    $where[] = "(r.id_reservation LIKE :r1 OR c.nom LIKE :r2 OR c.prenom LIKE :r3 OR v.immatriculation LIKE :r4)";
    $v = '%' . $recherche . '%';
    $params['r1'] = $v; $params['r2'] = $v; $params['r3'] = $v; $params['r4'] = $v;
}
if (in_array($statut, ['reservee', 'en_cours', 'terminee', 'annulee'], true)) {
    $where[] = "r.statut = :statut"; $params['statut'] = $statut;
}
if ($idClient > 0) { $where[] = "r.id_client = :id_client"; $params['id_client'] = $idClient; }
if ($idCategorie > 0) { $where[] = "v.id_categorie = :id_categorie"; $params['id_categorie'] = $idCategorie; }
if ($periodeDebut !== '') { $where[] = "r.date_debut >= :periode_debut"; $params['periode_debut'] = $periodeDebut; }
if ($periodeFin !== '') { $where[] = "r.date_fin <= :periode_fin"; $params['periode_fin'] = $periodeFin . ' 23:59:59'; }

$clauseWhere = empty($where) ? '1=1' : implode(' AND ', $where);

$triSql = match ($tri) {
    'montant_desc' => 'r.montant_total DESC',
    'montant_asc'  => 'r.montant_total ASC',
    'debut_asc'    => 'r.date_debut ASC',
    'debut_desc'   => 'r.date_debut DESC',
    default        => 'r.date_reservation DESC', // creation_desc
};

$jointures = "FROM Reservations r
              JOIN Clients c ON c.id_client = r.id_client
              JOIN Vehicules v ON v.id_vehicule = r.id_vehicule";

$stmtCount = $pdo->prepare("SELECT COUNT(*) $jointures WHERE $clauseWhere");
$stmtCount->execute($params);
$total = (int) $stmtCount->fetchColumn();

$sql = "SELECT r.id_reservation, r.date_debut, r.date_fin, r.date_reservation, r.statut, r.montant_total,
               c.nom AS client_nom, c.prenom AS client_prenom, c.telephone AS client_telephone,
               v.marque, v.modele, v.immatriculation
        $jointures
        WHERE $clauseWhere
        ORDER BY $triSql
        LIMIT :offset, :parPage";

$stmt = $pdo->prepare($sql);
foreach ($params as $cle => $valeur) { $stmt->bindValue(':' . $cle, $valeur, PDO::PARAM_STR); }
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':parPage', $parPage, PDO::PARAM_INT);
$stmt->execute();

$libelles = [
    'reservee' => 'À venir', 'en_cours' => 'En cours', 'terminee' => 'Terminée', 'annulee' => 'Annulée',
];

$donnees = array_map(function ($r) use ($libelles) {
    return [
        "id_reservation" => $r['id_reservation'],
        "reference"      => 'RÉS-' . date('Y') . '-' . str_pad($r['id_reservation'], 4, '0', STR_PAD_LEFT),
        "client"         => $r['client_prenom'] . ' ' . $r['client_nom'],
        "client_tel"     => $r['client_telephone'],
        "vehicule"       => $r['marque'] . ' ' . $r['modele'],
        "vehicule_immat" => $r['immatriculation'],
        "date_debut"     => $r['date_debut'],
        "date_fin"       => $r['date_fin'],
        "montant_total"  => (float) $r['montant_total'],
        "statut"         => $r['statut'],
        "statut_libelle" => $libelles[$r['statut']] ?? $r['statut'],
        "date_reservation" => $r['date_reservation'],
    ];
}, $stmt->fetchAll());

echo json_encode([
    "success" => true, "data" => $donnees, "total" => $total,
    "page" => $page, "par_page" => $parPage, "total_pages" => (int) ceil($total / $parPage),
]);
