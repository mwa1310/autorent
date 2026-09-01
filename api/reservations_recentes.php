<?php

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';
$pdo = getPDO();
require_once __DIR__ . '/../includes/middleware_auth.php';

exigerAuthentification();

$stmt = $pdo->query(
    "SELECT r.id_reservation, c.nom AS nom_client, c.prenom AS prenom_client,
            v.marque, v.modele, r.date_debut, r.date_fin, r.statut
     FROM Reservations r
     JOIN Clients c ON c.id_client = r.id_client
     JOIN Vehicules v ON v.id_vehicule = r.id_vehicule
     ORDER BY r.date_reservation DESC
     LIMIT 5"
);

$libellesStatut = [
    'reservee'  => ['classe' => 'a-venir',  'texte' => 'À venir'],
    'en_cours'  => ['classe' => 'en-cours', 'texte' => 'En cours'],
    'terminee'  => ['classe' => 'terminee', 'texte' => 'Terminée'],
    'annulee'   => ['classe' => 'annulee',  'texte' => 'Annulée'],
];

$donnees = array_map(function ($r) use ($libellesStatut) {
    $statutInfo = $libellesStatut[$r['statut']] ?? ['classe' => 'terminee', 'texte' => $r['statut']];
    return [
        "id"        => 'RÉS-' . str_pad($r['id_reservation'], 6, '0', STR_PAD_LEFT),
        "client"    => $r['prenom_client'] . ' ' . $r['nom_client'],
        "vehicule"  => $r['marque'] . ' ' . $r['modele'],
        "periode"   => date('d/m', strtotime($r['date_debut'])) . ' → ' . date('d/m', strtotime($r['date_fin'])),
        "statut"    => $statutInfo['classe'],
        "libelle"   => $statutInfo['texte'],
    ];
}, $stmt->fetchAll());

echo json_encode(["success" => true, "data" => $donnees]);
