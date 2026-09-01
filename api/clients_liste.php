<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/middleware_auth.php';
$pdo = getPDO();
exigerAuthentification();

$recherche = trim($_GET['recherche'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));
$parPage = 10;
$offset = ($page - 1) * $parPage;

$where = ["etat = 'actif'"];
$params = [];
if ($recherche !== '') {
    $where[] = "(nom LIKE :r1 OR prenom LIKE :r2 OR telephone LIKE :r3 OR email LIKE :r4)";
    $v = '%' . $recherche . '%';
    $params['r1'] = $v; $params['r2'] = $v; $params['r3'] = $v; $params['r4'] = $v;
}
$clauseWhere = implode(' AND ', $where);

$stmtCount = $pdo->prepare("SELECT COUNT(*) FROM Clients WHERE $clauseWhere");
$stmtCount->execute($params);
$total = (int) $stmtCount->fetchColumn();

$sql = "SELECT c.*, (SELECT COUNT(*) FROM Reservations r WHERE r.id_client = c.id_client) AS nb_reservations
        FROM Clients c WHERE $clauseWhere ORDER BY c.date_creation DESC LIMIT :offset, :parPage";
$stmt = $pdo->prepare($sql);
foreach ($params as $k => $v) $stmt->bindValue(':' . $k, $v, PDO::PARAM_STR);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':parPage', $parPage, PDO::PARAM_INT);
$stmt->execute();

echo json_encode(["success" => true, "data" => $stmt->fetchAll(), "total" => $total, "page" => $page, "par_page" => $parPage, "total_pages" => (int) ceil($total / $parPage)]);
