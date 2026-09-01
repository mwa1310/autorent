<?php
/**
 * Renvoie des chiffres différents selon le rôle de l'utilisateur connecté :
 * - admin : vue globale (CA, paiements, nouveaux clients, véhicules, réservations en cours)
 * - agent : vue opérationnelle du jour (véhicules dispo, départs/retours prévus aujourd'hui)
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';
$pdo = getPDO();
require_once __DIR__ . '/../includes/middleware_auth.php';

$utilisateur = exigerAuthentification();

// ---
// Commun aux deux rôles
// ---
$vehiculesDisponibles = (int) $pdo->query(
    "SELECT COUNT(*) FROM Vehicules WHERE statut_actuel = 'disponible' AND etat = 'actif'"
)->fetchColumn();

if ($utilisateur['role'] === 'admin') {

    // Réservations en cours
    $reservationsEnCours = (int) $pdo->query(
        "SELECT COUNT(*) FROM Reservations WHERE statut = 'en_cours'"
    )->fetchColumn();

    // Véhicules en maintenance
    $vehiculesMaintenance = (int) $pdo->query(
        "SELECT COUNT(*) FROM Vehicules WHERE statut_actuel = 'maintenance'"
    )->fetchColumn();

    // Chiffre d'affaires : réservations facturables (en cours/terminées) du mois en cours vs mois dernier
    $stmtCaMoisActuel = $pdo->query(
        "SELECT COALESCE(SUM(montant_total), 0) FROM Reservations
         WHERE statut IN ('terminee', 'en_cours')
         AND MONTH(date_debut) = MONTH(CURDATE()) AND YEAR(date_debut) = YEAR(CURDATE())"
    );
    $caMoisActuel = (float) $stmtCaMoisActuel->fetchColumn();

    $stmtCaMoisDernier = $pdo->query(
        "SELECT COALESCE(SUM(montant_total), 0) FROM Reservations
         WHERE statut IN ('terminee', 'en_cours')
         AND MONTH(date_debut) = MONTH(CURDATE() - INTERVAL 1 MONTH)
         AND YEAR(date_debut) = YEAR(CURDATE() - INTERVAL 1 MONTH)"
    );
    $caMoisDernier = (float) $stmtCaMoisDernier->fetchColumn();

    // Paiements réellement reçus ce mois vs mois dernier
    $stmtPaiementsMoisActuel = $pdo->query(
        "SELECT COALESCE(SUM(montant), 0) FROM Paiements
         WHERE statut = 'valide'
         AND MONTH(date_paiement) = MONTH(CURDATE()) AND YEAR(date_paiement) = YEAR(CURDATE())"
    );
    $paiementsMoisActuel = (float) $stmtPaiementsMoisActuel->fetchColumn();

    $stmtPaiementsMoisDernier = $pdo->query(
        "SELECT COALESCE(SUM(montant), 0) FROM Paiements
         WHERE statut = 'valide'
         AND MONTH(date_paiement) = MONTH(CURDATE() - INTERVAL 1 MONTH)
         AND YEAR(date_paiement) = YEAR(CURDATE() - INTERVAL 1 MONTH)"
    );
    $paiementsMoisDernier = (float) $stmtPaiementsMoisDernier->fetchColumn();

    // Nouveaux clients ce mois vs mois dernier
    $nouveauxClientsMoisActuel = (int) $pdo->query(
        "SELECT COUNT(*) FROM Clients
         WHERE MONTH(date_creation) = MONTH(CURDATE()) AND YEAR(date_creation) = YEAR(CURDATE())"
    )->fetchColumn();

    $nouveauxClientsMoisDernier = (int) $pdo->query(
        "SELECT COUNT(*) FROM Clients
         WHERE MONTH(date_creation) = MONTH(CURDATE() - INTERVAL 1 MONTH)
         AND YEAR(date_creation) = YEAR(CURDATE() - INTERVAL 1 MONTH)"
    )->fetchColumn();

    $totalVehicules = (int) $pdo->query("SELECT COUNT(*) FROM Vehicules WHERE etat = 'actif'")->fetchColumn();

    // Calcule une variation en %, en évitant une division par zéro
    $calculerVariation = function (float $actuel, float $precedent): ?float {
        if ($precedent <= 0) return null; // pas de comparaison possible
        return round((($actuel - $precedent) / $precedent) * 100, 1);
    };

    echo json_encode([
        "success" => true,
        "role"    => "admin",
        "vehicules_disponibles"   => $vehiculesDisponibles,
        "total_vehicules"   => $totalVehicules,
        "reservations_en_cours"   => $reservationsEnCours,
        "vehicules_maintenance"   => $vehiculesMaintenance,
        "ca_mois"    => round($caMoisActuel, 2),
        "ca_mois_variation"       => $calculerVariation($caMoisActuel, $caMoisDernier),
        "paiements_mois"    => round($paiementsMoisActuel, 2),
        "paiements_mois_variation"=> $calculerVariation($paiementsMoisActuel, $paiementsMoisDernier),
        "nouveaux_clients_mois"   => $nouveauxClientsMoisActuel,
        "nouveaux_clients_variation" => $calculerVariation($nouveauxClientsMoisActuel, $nouveauxClientsMoisDernier),
    ]);

} else {
    // ---
    // Vue agent : activité du jour
    // ---
    $departsJour = (int) $pdo->query(
        "SELECT COUNT(*) FROM Reservations
         WHERE DATE(date_debut) = CURDATE() AND statut IN ('reservee', 'en_cours')"
    )->fetchColumn();

    $retoursJour = (int) $pdo->query(
        "SELECT COUNT(*) FROM Reservations
         WHERE DATE(date_fin) = CURDATE() AND statut IN ('en_cours', 'terminee')"
    )->fetchColumn();

    echo json_encode([
        "success" => true,
        "role"  => "agent",
        "vehicules_disponibles" => $vehiculesDisponibles,
        "departs_jour"    => $departsJour,
        "retours_jour"    => $retoursJour,
    ]);
}
