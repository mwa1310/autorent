<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/middleware_auth.php';
require_once __DIR__ . '/../includes/journal.php';

$pdo = getPDO();
$utilisateurConnecte = exigerRoleParmi(['admin']);

$d = json_decode(file_get_contents('php://input'), true);

$nomAgence = trim($d['nom_agence'] ?? '');
$devise = trim($d['devise'] ?? 'FCFA');
$adresse = trim($d['adresse'] ?? '');
$telephone = trim($d['telephone'] ?? '');
$email = trim($d['email'] ?? '');
$joursSemaine = isset($d['jours_semaine_facturation']) ? (int) $d['jours_semaine_facturation'] : 6;
$joursMois = isset($d['jours_mois_facturation']) ? (int) $d['jours_mois_facturation'] : 24;
$joursAlerte = isset($d['jours_alerte_maintenance']) ? (int) $d['jours_alerte_maintenance'] : 7;

if ($nomAgence === '') {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Le nom de l'agence est obligatoire."]);
    exit;
}

try {
    $stmtVerif = $pdo->query("SELECT id FROM Parametres WHERE id = 1");
    if (!$stmtVerif->fetch()) {
        $pdo->exec("INSERT INTO Parametres (id, nom_agence) VALUES (1, 'AutoRent')");
    }

    $stmt = $pdo->prepare(
        "UPDATE Parametres SET
            nom_agence = :nom_agence,
            devise = :devise,
            adresse = :adresse,
            telephone = :telephone,
            email = :email,
            jours_semaine_facturation = :jours_semaine,
            jours_mois_facturation = :jours_mois,
            jours_alerte_maintenance = :jours_alerte
         WHERE id = 1"
    );

    $stmt->execute([
        "nom_agence"   => $nomAgence,
        "devise"       => $devise,
        "adresse"      => $adresse,
        "telephone"    => $telephone,
        "email"        => $email,
        "jours_semaine"=> $joursSemaine,
        "jours_mois"   => $joursMois,
        "jours_alerte" => $joursAlerte,
    ]);

    enregistrerActivite($pdo, $utilisateurConnecte, 'modification', 'autre', 1,
        "Modification des parametres generaux de l'agence ($nomAgence)");

    echo json_encode(["success" => true, "message" => "Parametres enregistres avec succes."]);
} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur : " . $e->getMessage()]);
}
