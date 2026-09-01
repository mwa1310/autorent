<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/middleware_auth.php';
$pdo = getPDO();
exigerAuthentification();

$types = $pdo->query("SELECT DISTINCT type_maintenance FROM Maintenance ORDER BY type_maintenance")->fetchAll(PDO::FETCH_COLUMN);
echo json_encode(["success" => true, "data" => $types]);
