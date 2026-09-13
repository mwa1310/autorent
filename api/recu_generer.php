<?php
/**
 * API - Génération du reçu de paiement (PDF)
 * GET /api/recu_generer.php?id_paie=123
 *
 * Le numéro de reçu (REC-AAAA-0001) est généré une seule fois et stocké dans
 * Paiements.numero_recu (voir migration sql/migration_recu_paiement.sql) -
 * les réimpressions réutilisent toujours le même numéro.
 *
 * ⚠️ Hypothèses de schéma à ajuster si besoin :
 *   - Reservations : id_client, id_vehicule, date_debut, date_fin
 *   - Clients      : prenom, nom, telephone
 *   - Vehicules    : marque, modele, immatriculation
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/middleware_auth.php';
require_once __DIR__ . '/../includes/journal.php';
require_once __DIR__ . '/../../mwa_biblio/assets/fpdf/fpdf.php'; 

$pdo = getPDO();
$utilisateurConnecte = exigerRoleParmi(['admin', 'agent']);

header('Content-Type: application/json; charset=utf-8'); // remplacé par le header PDF plus bas en cas de succès

$idPaie = (int) ($_GET['id_paie'] ?? 0);
if ($idPaie <= 0) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Identifiant de paiement invalide."]);
    exit;
}

// --- Récupération des données du paiement + réservation + client + véhicule ---
$stmt = $pdo->prepare(
    "SELECT
        p.id_paie, p.numero_recu, p.montant, p.mode_paiement, p.statut, p.date_paiement,
        r.id_reservation, r.date_debut, r.date_fin,
        c.prenom AS client_prenom, c.nom AS client_nom, c.telephone AS client_telephone,
        v.marque, v.modele, v.immatriculation
     FROM Paiements p
     JOIN Reservations r ON r.id_reservation = p.id_reservation
     JOIN Clients c ON c.id_client = r.id_client
     JOIN Vehicules v ON v.id_vehicule = r.id_vehicule
     WHERE p.id_paie = :id"
);
$stmt->execute(["id" => $idPaie]);
$paiement = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$paiement) {
    http_response_code(404);
    echo json_encode(["success" => false, "message" => "Paiement introuvable."]);
    exit;
}

// --- Récupération des infos agence ---
$agence = $pdo->query("SELECT nom_agence, devise, adresse, telephone, email FROM Parametres WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
$agence = $agence ?: ['nom_agence' => 'AutoRent', 'devise' => 'FCFA', 'adresse' => '', 'telephone' => '', 'email' => ''];
$devise = $agence['devise'] ?: 'FCFA';

// --- Génération (ou réutilisation) du numéro de reçu ---
$numeroRecu = $paiement['numero_recu'];
if (!$numeroRecu) {
    $annee = (int) date('Y', strtotime($paiement['date_paiement']));
    $pdo->beginTransaction();
    try {
        $stmtVerrou = $pdo->prepare(
            "SELECT numero_recu FROM Paiements WHERE id_paie = :id FOR UPDATE"
        );
        $stmtVerrou->execute(["id" => $idPaie]);
        $numeroExistant = $stmtVerrou->fetchColumn();

        if ($numeroExistant) {
            $numeroRecu = $numeroExistant;
        } else {
            $stmtMax = $pdo->prepare(
                "SELECT MAX(CAST(SUBSTRING(numero_recu, 10) AS UNSIGNED))
                 FROM Paiements WHERE numero_recu LIKE :motif"
            );
            $stmtMax->execute(["motif" => "REC-$annee-%"]);
            $dernierNumero = (int) $stmtMax->fetchColumn();
            $numeroRecu = sprintf('REC-%d-%04d', $annee, $dernierNumero + 1);

            $stmtMaj = $pdo->prepare("UPDATE Paiements SET numero_recu = :numero WHERE id_paie = :id");
            $stmtMaj->execute(["numero" => $numeroRecu, "id" => $idPaie]);

            enregistrerActivite(
                $pdo, $utilisateurConnecte, 'autre', 'paiement', $idPaie,
                "Génération du reçu $numeroRecu"
            );
        }
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Erreur lors de la génération du numéro de reçu : " . $e->getMessage()]);
        exit;
    }
}

// --- Construction du PDF ---
$libellesMode = ['especes' => 'Espèces', 'virement' => 'Virement bancaire', 'mobile_money' => 'Mobile Money'];
$libellesStatut = ['valide' => 'Validé', 'en_attente' => 'En attente', 'rembourse' => 'Remboursé'];

$pdf = new FPDF('P', 'mm', 'A4');
$pdf->AddPage();
$pdf->SetMargins(15, 15, 15);

// En-tête agence
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 8, utf8_decode($agence['nom_agence']), 0, 1);
$pdf->SetFont('Arial', '', 9);
if ($agence['adresse']) $pdf->Cell(0, 5, utf8_decode($agence['adresse']), 0, 1);
$ligneContact = trim(($agence['telephone'] ? 'Tél : ' . $agence['telephone'] : '') . ($agence['email'] ? '   Email : ' . $agence['email'] : ''));
if ($ligneContact) $pdf->Cell(0, 5, utf8_decode($ligneContact), 0, 1);

$pdf->Ln(6);
$pdf->SetDrawColor(220, 220, 220);
$pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
$pdf->Ln(6);

// Titre + numéro
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 8, utf8_decode('REÇU DE PAIEMENT'), 0, 1);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 6, utf8_decode("N° : $numeroRecu"), 0, 1);
$pdf->Cell(0, 6, utf8_decode('Date : ' . date('d/m/Y à H:i', strtotime($paiement['date_paiement']))), 0, 1);
$pdf->Ln(6);

// Bloc client / réservation
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(0, 7, utf8_decode('Client'), 0, 1);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 6, utf8_decode($paiement['client_prenom'] . ' ' . $paiement['client_nom']), 0, 1);
if ($paiement['client_telephone']) $pdf->Cell(0, 6, utf8_decode('Tél : ' . $paiement['client_telephone']), 0, 1);
$pdf->Ln(4);

$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(0, 7, utf8_decode('Location concernée'), 0, 1);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 6, utf8_decode("Véhicule : {$paiement['marque']} {$paiement['modele']} ({$paiement['immatriculation']})"), 0, 1);
$pdf->Cell(0, 6, utf8_decode('Période : ' . date('d/m/Y', strtotime($paiement['date_debut'])) . ' au ' . date('d/m/Y', strtotime($paiement['date_fin']))), 0, 1);
$pdf->Cell(0, 6, utf8_decode('Réservation n° ' . $paiement['id_reservation']), 0, 1);
$pdf->Ln(8);

// Tableau paiement
$pdf->SetFillColor(240, 240, 245);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(90, 9, utf8_decode('Description'), 1, 0, 'L', true);
$pdf->Cell(45, 9, utf8_decode('Mode de paiement'), 1, 0, 'L', true);
$pdf->Cell(45, 9, utf8_decode('Statut'), 1, 1, 'L', true);

$pdf->SetFont('Arial', '', 10);
$pdf->Cell(90, 9, utf8_decode('Paiement location vehicule'), 1, 0, 'L');
$pdf->Cell(45, 9, utf8_decode($libellesMode[$paiement['mode_paiement']] ?? $paiement['mode_paiement']), 1, 0, 'L');
$pdf->Cell(45, 9, utf8_decode($libellesStatut[$paiement['statut']] ?? $paiement['statut']), 1, 1, 'L');

$pdf->Ln(4);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(135, 10, utf8_decode('Montant reçu'), 1, 0, 'R');
$pdf->Cell(45, 10, number_format((float) $paiement['montant'], 0, ',', ' ') . ' ' . utf8_decode($devise), 1, 1, 'R');

$pdf->Ln(15);
$pdf->SetFont('Arial', 'I', 9);
$pdf->SetTextColor(120, 120, 120);
$pdf->MultiCell(0, 5, utf8_decode("Reçu généré automatiquement par le système AutoRent. Merci de votre confiance."), 0, 'C');

ob_end_clean(); // évite qu'un warning PHP antérieur ne corrompe le flux binaire du PDF
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . $numeroRecu . '.pdf"');
$pdf->Output('I', $numeroRecu . '.pdf');
exit;
