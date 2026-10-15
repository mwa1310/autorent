<?php
/**
 * API - Génération de la facture récapitulative d'une réservation (PDF)
 * GET /api/facture_reservation_generer.php?id_reservation=123
 *
 * Contrairement au reçu (api/recu_generer.php), qui couvre UN paiement,
 * cette facture couvre TOUTE la réservation : véhicule, période, tous les
 * paiements liés, et le solde restant.
 *
 * Le numéro de facture (FAC-AAAA-0001) est généré une seule fois et stocké
 * dans Reservations.numero_facture (voir sql/migration_facture_reservation.sql).
 *
 * ⚠️ Suppose Reservations.montant_total (déjà confirmé), id_client, id_vehicule,
 * date_debut, date_fin - ajustez si un nom diffère.
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/middleware_auth.php';
require_once __DIR__ . '/../includes/journal.php';
require_once __DIR__ . '/../../mwa_biblio/assets/fpdf/fpdf.php';

$pdo = getPDO();
$utilisateurConnecte = exigerRoleParmi(['admin', 'agent']);

header('Content-Type: application/json; charset=utf-8'); // remplacé par le header PDF plus bas en cas de succès

$idReservation = (int) ($_GET['id_reservation'] ?? 0);
if ($idReservation <= 0) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Identifiant de réservation invalide."]);
    exit;
}

// --- Réservation + client + véhicule ---
$stmt = $pdo->prepare(
    "SELECT
        r.id_reservation, r.numero_facture, r.date_debut, r.date_fin, r.statut, r.montant_total,
        c.prenom AS client_prenom, c.nom AS client_nom, c.telephone AS client_telephone,
        v.marque, v.modele, v.immatriculation
     FROM Reservations r
     JOIN Clients c ON c.id_client = r.id_client
     JOIN Vehicules v ON v.id_vehicule = r.id_vehicule
     WHERE r.id_reservation = :id"
);
$stmt->execute(["id" => $idReservation]);
$reservation = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reservation) {
    http_response_code(404);
    echo json_encode(["success" => false, "message" => "Réservation introuvable."]);
    exit;
}

// --- Tous les paiements liés (quel que soit leur statut, affichés avec leur statut) ---
$stmtPaiements = $pdo->prepare(
    "SELECT id_paie, numero_recu, montant, mode_paiement, statut, date_paiement
     FROM Paiements WHERE id_reservation = :id ORDER BY date_paiement ASC"
);
$stmtPaiements->execute(["id" => $idReservation]);
$paiements = $stmtPaiements->fetchAll(PDO::FETCH_ASSOC);

$totalPaye = 0.0;
foreach ($paiements as $p) {
    if ($p['statut'] === 'valide') { $totalPaye += (float) $p['montant']; }
}
$montantTotal = (float) $reservation['montant_total'];
$solde = $montantTotal - $totalPaye;

// --- Infos agence ---
$agence = $pdo->query("SELECT nom_agence, devise, adresse, telephone, email FROM Parametres WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
$agence = $agence ?: ['nom_agence' => 'AutoRent', 'devise' => 'FCFA', 'adresse' => '', 'telephone' => '', 'email' => ''];
$devise = $agence['devise'] ?: 'FCFA';

// --- Génération (ou réutilisation) du numéro de facture ---
$numeroFacture = $reservation['numero_facture'];
if (!$numeroFacture) {
    $annee = (int) date('Y', strtotime($reservation['date_debut']));
    $pdo->beginTransaction();
    try {
        $stmtVerrou = $pdo->prepare("SELECT numero_facture FROM Reservations WHERE id_reservation = :id FOR UPDATE");
        $stmtVerrou->execute(["id" => $idReservation]);
        $numeroExistant = $stmtVerrou->fetchColumn();

        if ($numeroExistant) {
            $numeroFacture = $numeroExistant;
        } else {
            $stmtMax = $pdo->prepare(
                "SELECT MAX(CAST(SUBSTRING(numero_facture, 10) AS UNSIGNED))
                 FROM Reservations WHERE numero_facture LIKE :motif"
            );
            $stmtMax->execute(["motif" => "FAC-$annee-%"]);
            $dernierNumero = (int) $stmtMax->fetchColumn();
            $numeroFacture = sprintf('FAC-%d-%04d', $annee, $dernierNumero + 1);

            $stmtMaj = $pdo->prepare("UPDATE Reservations SET numero_facture = :numero WHERE id_reservation = :id");
            $stmtMaj->execute(["numero" => $numeroFacture, "id" => $idReservation]);

            enregistrerActivite(
                $pdo, $utilisateurConnecte, 'autre', 'reservation', $idReservation,
                "Génération de la facture $numeroFacture"
            );
        }
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Erreur lors de la génération du numéro de facture : " . $e->getMessage()]);
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
$pdf->Cell(0, 8, utf8_decode('FACTURE - RÉCAPITULATIF DE LOCATION'), 0, 1);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 6, utf8_decode("N° : $numeroFacture"), 0, 1);
$pdf->Cell(0, 6, utf8_decode('Émise le : ' . date('d/m/Y')), 0, 1);
$pdf->Ln(6);

// Bloc client / véhicule / période
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(0, 7, utf8_decode('Client'), 0, 1);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 6, utf8_decode($reservation['client_prenom'] . ' ' . $reservation['client_nom']), 0, 1);
if ($reservation['client_telephone']) $pdf->Cell(0, 6, utf8_decode('Tél : ' . $reservation['client_telephone']), 0, 1);
$pdf->Ln(4);

$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(0, 7, utf8_decode('Location'), 0, 1);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 6, utf8_decode("Véhicule : {$reservation['marque']} {$reservation['modele']} ({$reservation['immatriculation']})"), 0, 1);
$pdf->Cell(0, 6, utf8_decode('Période : ' . date('d/m/Y', strtotime($reservation['date_debut'])) . ' au ' . date('d/m/Y', strtotime($reservation['date_fin']))), 0, 1);
$pdf->Cell(0, 6, utf8_decode('Réservation n° ' . $reservation['id_reservation']), 0, 1);
$pdf->Ln(8);

// Tableau des paiements
$pdf->SetFillColor(240, 240, 245);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(35, 9, utf8_decode('Date'), 1, 0, 'L', true);
$pdf->Cell(45, 9, utf8_decode('Mode'), 1, 0, 'L', true);
$pdf->Cell(40, 9, utf8_decode('Statut'), 1, 0, 'L', true);
$pdf->Cell(35, 9, utf8_decode('N° reçu'), 1, 0, 'L', true);
$pdf->Cell(25, 9, utf8_decode('Montant'), 1, 1, 'R', true);

$pdf->SetFont('Arial', '', 9);
if (empty($paiements)) {
    $pdf->Cell(180, 9, utf8_decode('Aucun paiement enregistré pour cette réservation.'), 1, 1, 'C');
} else {
    foreach ($paiements as $p) {
        $pdf->Cell(35, 8, date('d/m/Y', strtotime($p['date_paiement'])), 1, 0, 'L');
        $pdf->Cell(45, 8, utf8_decode($libellesMode[$p['mode_paiement']] ?? $p['mode_paiement']), 1, 0, 'L');
        $pdf->Cell(40, 8, utf8_decode($libellesStatut[$p['statut']] ?? $p['statut']), 1, 0, 'L');
        $pdf->Cell(35, 8, utf8_decode($p['numero_recu'] ?? '-'), 1, 0, 'L');
        $pdf->Cell(25, 8, number_format((float) $p['montant'], 0, ',', ' '), 1, 1, 'R');
    }
}

$pdf->Ln(4);
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(140, 9, utf8_decode('Montant total de la location'), 1, 0, 'R');
$pdf->Cell(40, 9, number_format($montantTotal, 0, ',', ' ') . ' ' . utf8_decode($devise), 1, 1, 'R');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(140, 8, utf8_decode('Total payé (paiements validés)'), 1, 0, 'R');
$pdf->Cell(40, 8, number_format($totalPaye, 0, ',', ' ') . ' ' . utf8_decode($devise), 1, 1, 'R');

$pdf->SetFont('Arial', 'B', 11);
if ($solde > 0.01) {
    $pdf->SetTextColor(200, 30, 30);
    $pdf->Cell(140, 9, utf8_decode('Solde restant dû'), 1, 0, 'R');
} elseif ($solde < -0.01) {
    $pdf->SetTextColor(30, 130, 60);
    $pdf->Cell(140, 9, utf8_decode('Trop-perçu'), 1, 0, 'R');
} else {
    $pdf->SetTextColor(30, 130, 60);
    $pdf->Cell(140, 9, utf8_decode('Solde'), 1, 0, 'R');
}
$pdf->Cell(40, 9, number_format(abs($solde), 0, ',', ' ') . ' ' . utf8_decode($devise), 1, 1, 'R');
$pdf->SetTextColor(0, 0, 0);

$pdf->Ln(15);
$pdf->SetFont('Arial', 'I', 9);
$pdf->SetTextColor(120, 120, 120);
$pdf->MultiCell(0, 5, utf8_decode("Document généré automatiquement par le système AutoRent. Merci de votre confiance."), 0, 'C');

ob_end_clean();
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . $numeroFacture . '.pdf"');
$pdf->Output('I', $numeroFacture . '.pdf');
exit;
