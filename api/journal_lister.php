<?php
/**
 * API - Journal d'activité
 * GET /api/journal_lister.php?page=1&type_entite=vehicule&action=creation
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';
$pdo = getPDO();
require_once __DIR__ . '/../includes/middleware_auth.php';

exigerAuthentification();

$page = max(1, (int) ($_GET['page'] ?? 1));
$parPage = 15;
$offset = ($page - 1) * $parPage;

$typeEntite = trim($_GET['type_entite'] ?? '');
$action = trim($_GET['action'] ?? '');

$conditions = [];
$params = [];
if ($typeEntite !== '') {
    $conditions[] = "type_entite = :type_entite";
    $params[':type_entite'] = $typeEntite;
}
if ($action !== '') {
    $conditions[] = "action = :action";
    $params[':action'] = $action;
}
$whereSql = $conditions ? ('WHERE ' . implode(' AND ', $conditions)) : '';

$stmtCount = $pdo->prepare("SELECT COUNT(*) FROM Journal_activite $whereSql");
$stmtCount->execute($params);
$total = (int) $stmtCount->fetchColumn();

$stmt = $pdo->prepare(
    "SELECT id_journal, nom_utilisateur, role_utilisateur, action, type_entite, id_entite, description, cree_le
     FROM Journal_activite
     $whereSql
     ORDER BY cree_le DESC
     LIMIT :limite OFFSET :decalage"
);
foreach ($params as $cle => $valeur) {
    $stmt->bindValue($cle, $valeur);
}
$stmt->bindValue(':limite', $parPage, PDO::PARAM_INT);
$stmt->bindValue(':decalage', $offset, PDO::PARAM_INT);
$stmt->execute();
$lignes = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    "success"     => true,
    "data"        => $lignes,
    "page"        => $page,
    "par_page"    => $parPage,
    "total"       => $total,
    "total_pages" => (int) ceil($total / max(1, $parPage)),
]);
