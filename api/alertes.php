<?php
/**
 * API - Alertes
 * GET /api/alertes.php
 *
 * Limite connue : le schéma actuel n'a pas de table pour les documents
 * (assurance, contrôle technique) - l'alerte "documents expirés" nécessiterait
 * d'ajouter ces champs/cette table au modèle de données (non incluse ici).
 *
 * ⚠️ L'alerte "paiement en retard" suppose un champ montant_total sur
 * Reservations et un champ statut ('valide', ...) sur Paiements. Adaptez les
 * noms de colonnes ci-dessous si votre schéma diffère.
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
        "type"    => "hors_service",
        "titre"   => "$nbHorsService véhicule(s) hors service",
        "sous"    => "Nécessitent une attention",
        "gravite" => "danger",
    ];
}

// --- Maintenances en retard (planifiées, date_prevue dépassée) ---
$nbMaintenancesRetard = (int) $pdo->query(
    "SELECT COUNT(*) FROM Maintenance WHERE statut = 'planifiee' AND date_prevue < CURDATE()"
)->fetchColumn();

if ($nbMaintenancesRetard > 0) {
    $alertes[] = [
        "type"    => "maintenance_en_retard",
        "titre"   => "$nbMaintenancesRetard maintenance(s) en retard",
        "sous"    => "Date prévue dépassée",
        "gravite" => "danger",
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
        "type"    => "maintenance_a_venir",
        "titre"   => "$nbMaintenancesAVenir maintenance(s) à venir",
        "sous"    => "Dans les 7 prochains jours",
        "gravite" => "warning",
    ];
}

// --- Réservations en retard (date_fin dépassée, toujours en_cours) ---
$nbRetards = (int) $pdo->query(
    "SELECT COUNT(*) FROM Reservations WHERE statut = 'en_cours' AND date_fin < NOW()"
)->fetchColumn();

if ($nbRetards > 0) {
    $alertes[] = [
        "type"    => "retour_en_retard",
        "titre"   => "$nbRetards retour(s) en retard",
        "sous"    => "Véhicule non rendu à la date prévue",
        "gravite" => "danger",
    ];
}

// --- Réservations dont la fin approche (dans les 24h, toujours en_cours) ---
$nbFinProche = (int) $pdo->query(
    "SELECT COUNT(*) FROM Reservations
     WHERE statut = 'en_cours' AND date_fin BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 24 HOUR)"
)->fetchColumn();

if ($nbFinProche > 0) {
    $alertes[] = [
        "type"    => "fin_reservation_proche",
        "titre"   => "$nbFinProche réservation(s) se terminent bientôt",
        "sous"    => "Retour prévu dans les 24 heures",
        "gravite" => "warning",
    ];
}

// --- Paiements en retard (réservation clôturée/en cours, solde non soldé) ---
try {
    $nbPaiementsRetard = (int) $pdo->query(
        "SELECT COUNT(*) FROM Reservations r
         WHERE r.statut IN ('en_cours', 'terminee')
           AND r.date_fin < NOW()
           AND r.montant_total > COALESCE((
                SELECT SUM(p.montant) FROM Paiements p
                WHERE p.id_reservation = r.id_reservation AND p.statut = 'valide'
           ), 0)"
    )->fetchColumn();

    if ($nbPaiementsRetard > 0) {
        $alertes[] = [
            "type"    => "paiement_en_retard",
            "titre"   => "$nbPaiementsRetard paiement(s) en retard",
            "sous"    => "Solde non réglé après la fin de location",
            "gravite" => "danger",
        ];
    }
} catch (Throwable $e) {
    // Si les noms de colonnes (montant_total, id_reservation, statut) diffèrent
    // dans votre schéma, cette requête échoue silencieusement plutôt que de
    // casser le reste des alertes. Corrigez les noms ci-dessus puis retirez ce try/catch.
    error_log('Alerte paiement_en_retard : ' . $e->getMessage());
}

echo json_encode(["success" => true, "data" => $alertes, "total" => count($alertes)]);