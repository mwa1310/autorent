<?php
/**
 * Le CA est calculé sur les réservations dont la période a démarré dans le mois
 * concerné (montant_total), cohérent avec le calcul de ca_mois dans stats_accueil.php.
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';
$pdo = getPDO();
require_once __DIR__ . '/../includes/middleware_auth.php';

exigerAuthentification();

$stmt = $pdo->query(
    "SELECT DATE_FORMAT(date_debut, '%Y-%m') AS mois, SUM(montant_total) AS total
     FROM Reservations
     WHERE date_debut >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
       AND statut != 'annulee'
     GROUP BY DATE_FORMAT(date_debut, '%Y-%m')
     ORDER BY mois ASC"
);
$resultats = $stmt->fetchAll();
$parMois = array_column($resultats, 'total', 'mois');

// On génère les 6 derniers mois glissants (même les mois à 0 apparaissent, pour un graphique continu)
$labels = [];
$donnees = [];
$nomsMois = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'];

for ($i = 5; $i >= 0; $i--) {
    $date = new DateTime("first day of -$i month");
    $cle = $date->format('Y-m');
    $labels[] = $nomsMois[(int)$date->format('n') - 1];
    $donnees[] = isset($parMois[$cle]) ? (float) $parMois[$cle] : 0;
}

echo json_encode(["success" => true, "labels" => $labels, "donnees" => $donnees]);
