<?php

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/middleware_auth.php';
$pdo = getPDO();

exigerAuthentification();

$sql = "
    (SELECT r.id_reservation, c.nom, c.prenom, v.marque, v.modele, r.statut,
            'depart' AS type_mouvement, TIME(r.date_debut) AS heure_prevue
     FROM Reservations r
     JOIN Clients c ON c.id_client = r.id_client
     JOIN Vehicules v ON v.id_vehicule = r.id_vehicule
     WHERE DATE(r.date_debut) = CURDATE() AND r.statut = 'reservee')

    UNION ALL

    (SELECT r.id_reservation, c.nom, c.prenom, v.marque, v.modele, r.statut,
            'retour' AS type_mouvement, TIME(r.date_fin) AS heure_prevue
     FROM Reservations r
     JOIN Clients c ON c.id_client = r.id_client
     JOIN Vehicules v ON v.id_vehicule = r.id_vehicule
     WHERE DATE(r.date_fin) = CURDATE() AND r.statut = 'en_cours')

    ORDER BY heure_prevue ASC
";

$stmt = $pdo->query($sql);

$libellesStatut = [
    'reservee' => ['classe' => 'a-venir',  'texte' => 'À venir'],
    'en_cours' => ['classe' => 'en-cours', 'texte' => 'En cours'],
];

$donnees = array_map(function ($r) use ($libellesStatut) {
    $statutInfo = $libellesStatut[$r['statut']] ?? ['classe' => 'terminee', 'texte' => $r['statut']];
    return [
        "client"        => $r['prenom'] . ' ' . $r['nom'],
        "vehicule"      => $r['marque'] . ' ' . $r['modele'],
        "type"          => $r['type_mouvement'] === 'depart' ? 'Départ' : 'Retour',
        "type_classe"   => $r['type_mouvement'],
        "heure_prevue"  => substr($r['heure_prevue'], 0, 5), // HH:MM
        "statut"        => $statutInfo['classe'],
        "statut_libelle"=> $statutInfo['texte'],
    ];
}, $stmt->fetchAll());

echo json_encode(["success" => true, "data" => $donnees]);
