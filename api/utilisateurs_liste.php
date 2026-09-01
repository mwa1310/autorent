<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/middleware_auth.php';
$pdo = getPDO();

exigerRoleParmi(['admin']);

$recherche = trim($_GET['recherche'] ?? '');
$role = trim($_GET['role'] ?? '');
$etat = trim($_GET['etat'] ?? '');

$where = [];
$params = [];
if ($recherche !== '') {
    $where[] = "(nom LIKE :r1 OR prenom LIKE :r2 OR email LIKE :r3)";
    $v = '%' . $recherche . '%';
    $params['r1'] = $v; $params['r2'] = $v; $params['r3'] = $v;
}
if (in_array($role, ['admin', 'agent'], true)) { $where[] = "role = :role"; $params['role'] = $role; }
if (in_array($etat, ['actif', 'inactif'], true)) { $where[] = "etat = :etat"; $params['etat'] = $etat; }

$clauseWhere = empty($where) ? '1=1' : implode(' AND ', $where);

$stmt = $pdo->prepare("SELECT id_utilisateur, nom, prenom, email, role, etat, date_creation FROM Utilisateurs WHERE $clauseWhere ORDER BY date_creation DESC");
foreach ($params as $cle => $valeur) { $stmt->bindValue(':' . $cle, $valeur, PDO::PARAM_STR); }
$stmt->execute();

echo json_encode(["success" => true, "data" => $stmt->fetchAll()]);
