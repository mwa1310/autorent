<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/middleware_auth.php';
$pdo = getPDO();

exigerRoleParmi(['admin']);

$total = (int) $pdo->query("SELECT COUNT(*) FROM Utilisateurs")->fetchColumn();
$admins = (int) $pdo->query("SELECT COUNT(*) FROM Utilisateurs WHERE role = 'admin'")->fetchColumn();
$agents = (int) $pdo->query("SELECT COUNT(*) FROM Utilisateurs WHERE role = 'agent'")->fetchColumn();
$actifs = (int) $pdo->query("SELECT COUNT(*) FROM Utilisateurs WHERE etat = 'actif'")->fetchColumn();

echo json_encode(["success" => true, "total" => $total, "admins" => $admins, "agents" => $agents, "actifs" => $actifs]);
