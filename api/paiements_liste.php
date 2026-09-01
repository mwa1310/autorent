<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/middleware_auth.php';
$pdo = getPDO();

exigerAuthentification();

$recherche    = trim($_GET['recherche'] ?? '');
$statut       = trim($_GET['statut'] ?? '');
$mode         = trim($_GET['mode_paiement'] ?? '');
$periodeDebut = trim($_GET['periode_debut'] ?? '');
$periodeFin   = trim($_GET['periode_fin'] ?? '');
$tri          = trim($_GET['tri'] ?? 'date_desc');
$page         = max(1, (int) ($_GET['page'] ?? 1));
$parPage      = 10;
$offset       = ($page - 1) * $parPage;

$where = [];
$params = [];

if ($recherche !== '') {
    $where[] = "(p.id_paie LIKE :r1 OR r.id_reservation LIKE :r2 OR c.nom LIKE :r3 OR c.prenom LIKE :r4)";
    $v = '%' . $recherche . '%';
    $params['r1'] = $v; $params['r2'] = $v; $params['r3'] = $v; $params['r4'] = $v;
}
if (in_array($statut, ['valide', 'en_attente', 'rembourse'], true)) { $where[] = "p.statut = :statut"; $params['statut'] = $statut; }
if (in_array($mode, ['especes', 'virement', 'mobile_money'], true)) { $where[] = "p.mode_paiement = :mode"; $params['mode'] = $mode; }
if ($periodeDebut !== '') { $where[] = "p.date_paiement >= :periode_debut"; $params['periode_debut'] = $periodeDebut; }
if ($periodeFin !== '') { $where[] = "p.date_paiement <= :periode_fin"; $params['periode_fin'] = $periodeFin . ' 23:59:59'; }

$clauseWhere = empty($where) ? '1=1' : implode(' AND ', $where);

$triSql = match ($tri) {
    'montant_desc' => 'p.montant DESC',
    'montant_asc'  => 'p.montant ASC',
    'date_asc'     => 'p.date_paiement ASC',
    default        => 'p.date_paiement DESC',
};

$jointures = "FROM Paiements p
              JOIN Reservations r ON r.id_reservation = p.id_reservation
              JOIN Clients c ON c.id_client = r.id_client";

$stmtCount = $pdo->prepare("SELECT COUNT(*) $jointures WHERE $clauseWhere");
$stmtCount->execute($params);
$total = (int) $stmtCount->fetchColumn();

$sql = "SELECT p.id_paie, p.montant, p.mode_paiement, p.statut, p.date_paiement,
               r.id_reservation, c.nom AS client_nom, c.prenom AS client_prenom, c.telephone AS client_telephone
        $jointures
        WHERE $clauseWhere
        ORDER BY $triSql
        LIMIT :offset, :parPage";
$stmt = $pdo->prepare($sql);
foreach ($params as $cle => $valeur) { $stmt->bindValue(':' . $cle, $valeur, PDO::PARAM_STR); }
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':parPage', $parPage, PDO::PARAM_INT);
$stmt->execute();

$libellesStatut = ['valide' => 'Payé', 'en_attente' => 'En attente', 'rembourse' => 'Remboursé'];
$libellesMode = ['especes' => 'Espèces', 'virement' => 'Virement bancaire', 'mobile_money' => 'Mobile Money'];

$donnees = array_map(function ($p) use ($libellesStatut, $libellesMode) {
    return [
        "id_paie"           => $p['id_paie'],
        "reference"         => 'PAY-' . date('Y') . '-' . str_pad($p['id_paie'], 4, '0', STR_PAD_LEFT),
        "id_reservation"    => $p['id_reservation'],
        "reference_reservation" => 'RÉS-' . date('Y') . '-' . str_pad($p['id_reservation'], 4, '0', STR_PAD_LEFT),
        "client"            => $p['client_prenom'] . ' ' . $p['client_nom'],
        "client_tel"        => $p['client_telephone'],
        "montant"           => (float) $p['montant'],
        "mode_paiement"     => $p['mode_paiement'],
        "mode_libelle"      => $libellesMode[$p['mode_paiement']] ?? $p['mode_paiement'],
        "statut"            => $p['statut'],
        "statut_libelle"    => $libellesStatut[$p['statut']] ?? $p['statut'],
        "date_paiement"     => $p['date_paiement'],
    ];
}, $stmt->fetchAll());

echo json_encode([
    "success" => true, "data" => $donnees, "total" => $total,
    "page" => $page, "par_page" => $parPage, "total_pages" => (int) ceil($total / $parPage),
]);
