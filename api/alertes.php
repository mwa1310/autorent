<?php
/**
 * API - Alertes
 * GET /api/alertes.php
 *
 * Limite connue : le schéma actuel n'a pas de table pour les documents
 * (assurance, contrôle technique), donc l'alerte "documents expirés" de la
 * maquette originale n'est pas incluse ici - elle nécessiterait d'ajouter
 * ces champs/cette table au modèle de données.
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';
$pdo = getPDO();
require_once __DIR__ . '/../includes/middleware_auth.php';

exigerAuthentification();

$alertes = [];

// --- Véhicules hors service ---
$nbHorsService = (int) $pdo->query(
    "SELECT COUNT(*) FROM Vehicules WHERE statut_actuel = 'hors_service' AND etat = 'actif'"
)->fetchColumn();

if ($nbHorsService > 0) {
    $alertes[] = [
        "type"  => "hors_service",
        "titre" => "$nbHorsService véhicule(s) hors service",
        "sous"  => "Nécessitent une attention",
    ];
}

// --- Maintenances planifiées dans les 7 prochains jours ---
$nbMaintenancesAVenir = (int) $pdo->query(
    "SELECT COUNT(*) FROM Maintenance
     WHERE statut = 'planifiee'
       AND date_prevue BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)"
)->fetchColumn();

if ($nbMaintenancesAVenir > 0) {
    $alertes[] = [
        "type"  => "maintenance_a_venir",
        "titre" => "$nbMaintenancesAVenir maintenance(s) à venir",
        "sous"  => "Dans les 7 prochains jours",
    ];
}

// --- Réservations en retard (date_fin dépassée, toujours en_cours) ---
$nbRetards = (int) $pdo->query(
    "SELECT COUNT(*) FROM Reservations WHERE statut = 'en_cours' AND date_fin < NOW()"
)->fetchColumn();

if ($nbRetards > 0) {
    $alertes[] = [
        "type"  => "retour_en_retard",
        "titre" => "$nbRetards retour(s) en retard",
        "sous"  => "Véhicule non rendu à la date prévue",
    ];
}

echo json_encode(["success" => true, "data" => $alertes]);
