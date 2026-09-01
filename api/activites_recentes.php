<?php
/**
 * Combine 4 sources : nouvelles réservations, paiements reçus, maintenances
 * programmées, changements de statut véhicule. Trié par date décroissante.
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';
$pdo = getPDO();
require_once __DIR__ . '/../includes/middleware_auth.php';

exigerAuthentification();

$sql = "
    (SELECT 'reservation' AS type_evenement,
            CONCAT('Nouvelle réservation RÉS-', LPAD(r.id_reservation, 6, '0'), ' créée') AS titre,
            CONCAT('Par ', u.prenom, ' ', u.nom) AS sous_titre,
            r.date_reservation AS date_evt
     FROM Reservations r
     JOIN Utilisateurs u ON u.id_utilisateur = r.id_utilisateur
     ORDER BY r.date_reservation DESC LIMIT 5)

    UNION ALL

    (SELECT 'paiement' AS type_evenement,
            CONCAT('Paiement de ', FORMAT(p.montant, 0), ' FCFA reçu') AS titre,
            CONCAT('Réservation RÉS-', LPAD(p.id_reservation, 6, '0')) AS sous_titre,
            p.date_paiement AS date_evt
     FROM Paiements p
     ORDER BY p.date_paiement DESC LIMIT 5)

    UNION ALL

    (SELECT 'maintenance' AS type_evenement,
            CONCAT('Maintenance programmée pour ', v.marque, ' ', v.modele, ' (', v.immatriculation, ')') AS titre,
            CONCAT('Prévue le ', DATE_FORMAT(m.date_prevue, '%d/%m/%Y')) AS sous_titre,
            m.date_prevue AS date_evt
     FROM Maintenance m
     JOIN Vehicules v ON v.id_vehicule = m.id_vehicule
     ORDER BY m.date_prevue DESC LIMIT 5)

    UNION ALL

    (SELECT 'statut_vehicule' AS type_evenement,
            CONCAT(v.marque, ' ', v.modele, ' (', v.immatriculation, ') : ', h.ancien_statut, ' → ', h.nouveau_statut) AS titre,
            CONCAT('Par ', u.prenom, ' ', u.nom) AS sous_titre,
            h.date AS date_evt
     FROM Historique_statut_vehicule h
     JOIN Vehicules v ON v.id_vehicule = h.id_vehicule
     JOIN Utilisateurs u ON u.id_utilisateur = h.id_utilisateur
     ORDER BY h.date DESC LIMIT 5)

    ORDER BY date_evt DESC
    LIMIT 5
";

$stmt = $pdo->query($sql);
$activites = $stmt->fetchAll();

// Calcule un temps relatif ("il y a 10 min", "il y a 2 h"...) côté serveur
function tempsRelatif($dateEvt)
{
    $diffSecondes = time() - strtotime($dateEvt);
    if ($diffSecondes < 60) return "À l'instant";
    if ($diffSecondes < 3600) return "Il y a " . floor($diffSecondes / 60) . " min";
    if ($diffSecondes < 86400) return "Il y a " . floor($diffSecondes / 3600) . " h";
    return "Il y a " . floor($diffSecondes / 86400) . " j";
}

$donnees = array_map(function ($a) {
    return [
        "type"   => $a['type_evenement'],
        "titre"  => $a['titre'],
        "sous"   => $a['sous_titre'],
        "temps"  => tempsRelatif($a['date_evt']),
    ];
}, $activites);

echo json_encode(["success" => true, "data" => $donnees]);
