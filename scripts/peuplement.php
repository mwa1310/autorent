<?php
/**
 * Peuplement de la base AutoRent avec des données de test réalistes.
 * Nécessite : composer require fakerphp/faker
 * Usage : php peuplement.php   (depuis la racine du projet, à côté de vendor/)
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/db.php';

ini_set('memory_limit', '512M');
set_time_limit(0);

$faker = Faker\Factory::create('fr_FR');
$faker->seed(42); // reproductible

// ---
// Connexion
// ---
$pdo = getPDO();

// ---
// Paramètres du volume de données
// ---
$NB_AGENTS     = 6;
$NB_CLIENTS     = 300;
$NB_VEHICULES     = 40;
$RESA_MIN_PAR_VEH   = 4;
$RESA_MAX_PAR_VEH   = 12;

$marquesModeles = [
    'Toyota'   => ['Corolla', 'Yaris', 'RAV4', 'Hilux'],
    'Renault'  => ['Clio', 'Megane', 'Duster', 'Kangoo'],
    'Peugeot'  => ['208', '308', '3008', 'Partner'],
    'Hyundai'  => ['i10', 'Tucson', 'Accent'],
    'Kia'      => ['Picanto', 'Sportage', 'Rio'],
    'Volkswagen' => ['Golf', 'Polo', 'Tiguan'],
    'Ford'     => ['Fiesta', 'Focus', 'Ranger'],
];

$typesMaintenance = ['Vidange', 'Révision générale', 'Changement pneus', 'Freinage', 'Climatisation', 'Réparation carrosserie'];

$pdo->beginTransaction();

try {
    // ===
    // 1. Utilisateurs
    // ===
    $stmtUtilisateur = $pdo->prepare(
        "INSERT INTO Utilisateurs (nom, prenom, email, mot_de_passe, role, etat)
         VALUES (:nom, :prenom, :email, :mot_de_passe, 'agent', 'actif')"
    );

    $motDePasseTest = password_hash('password123', PASSWORD_DEFAULT);

    // Récupère tous les admins déjà existants (au moins un est requis)
    $idsUtilisateurs = array_column(
        $pdo->query("SELECT id_utilisateur FROM Utilisateurs WHERE role = 'admin'")->fetchAll(),
        'id_utilisateur'
    );
    if (empty($idsUtilisateurs)) {
        throw new \Exception("Aucun compte admin trouvé en base. Crée le premier admin avant de lancer ce script.");
    }
    $idsUtilisateurs = array_map('intval', $idsUtilisateurs);

    for ($i = 0; $i < $NB_AGENTS; $i++) {
        $stmtUtilisateur->execute([
            "nom" => $faker->lastName(),
            "prenom" => $faker->firstName(),
            "email" => $faker->unique()->safeEmail(),
            "mot_de_passe" => $motDePasseTest,
        ]);
        $idsUtilisateurs[] = (int) $pdo->lastInsertId();
    }
    echo "Agents de test générés (" . $NB_AGENTS . "), admin(s) existant(s) réutilisé(s) (" . count($idsUtilisateurs) . " utilisateurs au total)\n";

    // ===
    // 2. Categories_vehicules -> doivent déjà exister (voir ajout_donnees_reference.sql)
    // ===
    $categories = $pdo->query("SELECT id_categorie, tarif_jour FROM Categories_vehicules")->fetchAll();
    if (empty($categories)) {
        throw new \Exception("Aucune catégorie trouvée : exécute d'abord ajout_donnees_reference.sql dans phpMyAdmin.");
    }

    // ===
    // 3. Clients
    // ===
    $stmtClient = $pdo->prepare(
        "INSERT INTO Clients (nom, prenom, email, numero_permis, date_delivrance_permis, numero_CNI, adresse, telephone, date_creation, etat)
         VALUES (:nom, :prenom, :email, :numero_permis, :date_delivrance_permis, :numero_CNI, :adresse, :telephone, :date_creation, 'actif')"
    );

    $idsClients = [];
    for ($i = 0; $i < $NB_CLIENTS; $i++) {
        $dateCreation = $faker->dateTimeBetween('-2 years', 'now');
        $stmtClient->execute([
            "nom" => $faker->lastName(),
            "prenom" => $faker->firstName(),
            "email" => $faker->unique()->safeEmail(),
            "numero_permis" => strtoupper($faker->bothify('PC-#####??')),
            "date_delivrance_permis" => $faker->dateTimeBetween('-15 years', '-1 year')->format('Y-m-d'),
            "numero_CNI" => strtoupper($faker->bothify('CNI#########')),
            "adresse" => $faker->address(),
            "telephone" => $faker->phoneNumber(),
            "date_creation" => $dateCreation->format('Y-m-d H:i:s'),
        ]);
        $idsClients[] = (int) $pdo->lastInsertId();
    }
    echo "Clients générés (" . count($idsClients) . ")\n";

    // ===
    // 4. Vehicules
    // ===
    $stmtVehicule = $pdo->prepare(
        "INSERT INTO Vehicules (immatriculation, marque, modele, id_categorie, annee, carburant, kilometrage, date_ajout, statut_actuel, etat)
         VALUES (:immatriculation, :marque, :modele, :id_categorie, :annee, :carburant, :kilometrage, :date_ajout, 'disponible', 'actif')"
    );

    $carburants = ['essence', 'diesel', 'hybride', 'electrique'];
    $vehicules = []; // stocke id_vehicule + tarif_jour de sa catégorie, pour le calcul des réservations

    for ($i = 0; $i < $NB_VEHICULES; $i++) {
        $marque = array_rand($marquesModeles);
        $modele = $faker->randomElement($marquesModeles[$marque]);
        $categorie = $faker->randomElement($categories);

        $stmtVehicule->execute([
            "immatriculation" => strtoupper($faker->bothify('??-###-??')),
            "marque" => $marque,
            "modele" => $modele,
            "id_categorie" => $categorie['id_categorie'],
            "annee" => $faker->numberBetween(2016, 2025),
            "carburant" => $faker->randomElement($carburants),
            "kilometrage" => $faker->numberBetween(5000, 120000),
            "date_ajout" => $faker->dateTimeBetween('-2 years', '-1 month')->format('Y-m-d H:i:s'),
        ]);

        $vehicules[] = [
            "id_vehicule" => (int) $pdo->lastInsertId(),
            "tarif_jour" => (float) $categorie['tarif_jour'],
        ];
    }
    echo "Véhicules générés (" . count($vehicules) . ")\n";

    // ===
    // 5. Reservations (chaîne séquentielle NON chevauchante par véhicule)
    //    + Paiements + Etats_des_lieux associés, générés dans la foulée
    // ===
    $stmtReservation = $pdo->prepare(
        "INSERT INTO Reservations (id_client, id_vehicule, id_utilisateur, date_debut, date_fin, date_reservation, statut, montant_total)
         VALUES (:id_client, :id_vehicule, :id_utilisateur, :date_debut, :date_fin, :date_reservation, :statut, :montant_total)"
    );
    $stmtPaiement = $pdo->prepare(
        "INSERT INTO Paiements (id_reservation, montant, mode_paiement, statut, date_paiement, id_utilisateur)
         VALUES (:id_reservation, :montant, :mode_paiement, 'valide', :date_paiement, :id_utilisateur)"
    );
    $stmtEtatDesLieux = $pdo->prepare(
        "INSERT INTO Etats_des_lieux (id_reservation, id_utilisateur, type, date, kilometrage, etat_general)
         VALUES (:id_reservation, :id_utilisateur, :type, :date, :kilometrage, :etat_general)"
    );
    $stmtHistoriqueStatut = $pdo->prepare(
        "INSERT INTO Historique_statut_vehicule (id_vehicule, ancien_statut, nouveau_statut, id_utilisateur, date, raison)
         VALUES (:id_vehicule, :ancien_statut, :nouveau_statut, :id_utilisateur, :date, :raison)"
    );

    $modesPaiement = ['especes', 'virement', 'mobile_money'];
    $maintenant = new DateTime();
    $totalReservations = 0;

    foreach ($vehicules as $vehicule) {
        $nbReservations = $faker->numberBetween($RESA_MIN_PAR_VEH, $RESA_MAX_PAR_VEH);

        // Point de départ de la chaîne : quelque part dans les 6 derniers mois
        $curseurDate = $faker->dateTimeBetween('-6 months', '-1 month');

        for ($j = 0; $j < $nbReservations; $j++) {
            // Un petit délai aléatoire entre deux réservations du même véhicule (0 à 5 jours de battement)
            $curseurDate = (clone $curseurDate)->modify('+' . $faker->numberBetween(0, 5) . ' days');

            $dureeJours = $faker->numberBetween(1, 10);
            $dateDebut = clone $curseurDate;
            $dateFin = (clone $dateDebut)->modify("+{$dureeJours} days");

            // Statut déterminé par rapport à la date du jour
            if ($dateFin < $maintenant) {
                $statut = $faker->boolean(92) ? 'terminee' : 'annulee'; // 8% d'annulations
            } elseif ($dateDebut <= $maintenant && $dateFin >= $maintenant) {
                $statut = 'en_cours';
            } else {
                $statut = $faker->boolean(85) ? 'reservee' : 'annulee';
            }

            $montantTotal = round($dureeJours * $vehicule['tarif_jour'], 2);
            $idClient = $faker->randomElement($idsClients);
            $idUtilisateur = $faker->randomElement($idsUtilisateurs);

            $stmtReservation->execute([
                "id_client" => $idClient,
                "id_vehicule" => $vehicule['id_vehicule'],
                "id_utilisateur" => $idUtilisateur,
                "date_debut" => $dateDebut->format('Y-m-d H:i:s'),
                "date_fin" => $dateFin->format('Y-m-d H:i:s'),
                "date_reservation" => (clone $dateDebut)->modify('-' . $faker->numberBetween(1, 10) . ' days')->format('Y-m-d H:i:s'),
                "statut" => $statut,
                "montant_total" => $montantTotal,
            ]);
            $idReservation = (int) $pdo->lastInsertId();
            $totalReservations++;

            // --- Paiements : seulement pour les réservations non annulées ---
            if ($statut === 'terminee') {
                // Paiement intégral, parfois en 2 fois (acompte + solde)
                if ($faker->boolean(30)) {
                    $acompte = round($montantTotal * 0.3, 2);
                    $stmtPaiement->execute([
                        "id_reservation" => $idReservation, "montant" => $acompte,
                        "mode_paiement" => $faker->randomElement($modesPaiement),
                        "date_paiement" => (clone $dateDebut)->format('Y-m-d H:i:s'),
                        "id_utilisateur" => $idUtilisateur,
                    ]);
                    $stmtPaiement->execute([
                        "id_reservation" => $idReservation, "montant" => round($montantTotal - $acompte, 2),
                        "mode_paiement" => $faker->randomElement($modesPaiement),
                        "date_paiement" => (clone $dateFin)->format('Y-m-d H:i:s'),
                        "id_utilisateur" => $idUtilisateur,
                    ]);
                } else {
                    $stmtPaiement->execute([
                        "id_reservation" => $idReservation, "montant" => $montantTotal,
                        "mode_paiement" => $faker->randomElement($modesPaiement),
                        "date_paiement" => (clone $dateFin)->format('Y-m-d H:i:s'),
                        "id_utilisateur" => $idUtilisateur,
                    ]);
                }

                // États des lieux départ + retour
                $stmtEtatDesLieux->execute([
                    "id_reservation" => $idReservation, "id_utilisateur" => $idUtilisateur, "type" => "depart",
                    "date" => $dateDebut->format('Y-m-d H:i:s'),
                    "kilometrage" => $faker->numberBetween(5000, 120000),
                    "etat_general" => "Véhicule propre, aucun dommage constaté.",
                ]);
                $stmtEtatDesLieux->execute([
                    "id_reservation" => $idReservation, "id_utilisateur" => $idUtilisateur, "type" => "retour",
                    "date" => $dateFin->format('Y-m-d H:i:s'),
                    "kilometrage" => $faker->numberBetween(5000, 120000),
                    "etat_general" => $faker->boolean(85) ? "RAS, retour conforme." : "Rayure légère sur portière avant droite.",
                ]);
            } elseif ($statut === 'en_cours') {
                // Acompte versé, état des lieux de départ uniquement
                $stmtPaiement->execute([
                    "id_reservation" => $idReservation, "montant" => round($montantTotal * 0.5, 2),
                    "mode_paiement" => $faker->randomElement($modesPaiement),
                    "date_paiement" => $dateDebut->format('Y-m-d H:i:s'),
                    "id_utilisateur" => $idUtilisateur,
                ]);
                $stmtEtatDesLieux->execute([
                    "id_reservation" => $idReservation, "id_utilisateur" => $idUtilisateur, "type" => "depart",
                    "date" => $dateDebut->format('Y-m-d H:i:s'),
                    "kilometrage" => $faker->numberBetween(5000, 120000),
                    "etat_general" => "Véhicule propre, aucun dommage constaté.",
                ]);
            }

            $curseurDate = $dateFin; // la prochaine réservation démarrera après celle-ci
        }
    }
    echo "Réservations générées ($totalReservations), avec paiements et états des lieux associés\n";

    // ===
    // 6. Maintenance (sur ~20% des véhicules)
    // ===
    $stmtMaintenance = $pdo->prepare(
        "INSERT INTO Maintenance (id_vehicule, type_maintenance, date_prevue, date_realisee, statut, cout, description, id_utilisateur, etat)
         VALUES (:id_vehicule, :type_maintenance, :date_prevue, :date_realisee, :statut, :cout, :description, :id_utilisateur, 'actif')"
    );

    $nbMaintenances = 0;
    foreach ($vehicules as $vehicule) {
        if (!$faker->boolean(35)) continue; // ~35% des véhicules ont un historique de maintenance

        $nbEnregistrements = $faker->numberBetween(1, 3);
        for ($k = 0; $k < $nbEnregistrements; $k++) {
            $estPassee = $faker->boolean(70);
            $datePrevue = $estPassee
                ? $faker->dateTimeBetween('-6 months', '-1 week')
                : $faker->dateTimeBetween('now', '+2 months');

            $statut = $estPassee ? 'terminee' : $faker->randomElement(['planifiee', 'planifiee', 'en_cours']);

            $stmtMaintenance->execute([
                "id_vehicule" => $vehicule['id_vehicule'],
                "type_maintenance" => $faker->randomElement($typesMaintenance),
                "date_prevue" => $datePrevue->format('Y-m-d'),
                "date_realisee" => $statut === 'terminee' ? $datePrevue->format('Y-m-d') : null,
                "statut" => $statut,
                "cout" => $statut === 'terminee' ? $faker->numberBetween(15000, 150000) : 0,
                "description" => $faker->sentence(8),
                "id_utilisateur" => $faker->randomElement($idsUtilisateurs),
            ]);
            $nbMaintenances++;
        }
    }
    echo "Maintenances générées ($nbMaintenances)\n";

    // ===
    // 7. Historique_statut_vehicule (quelques transitions par véhicule)
    // ===
    $statutsPossibles = ['disponible', 'en_location', 'maintenance', 'hors_service'];
    $nbHistorique = 0;
    foreach ($vehicules as $vehicule) {
        $nbTransitions = $faker->numberBetween(1, 4);
        $ancienStatut = 'disponible';
        for ($k = 0; $k < $nbTransitions; $k++) {
            $nouveauStatut = $faker->randomElement($statutsPossibles);
            $stmtHistoriqueStatut->execute([
                "id_vehicule" => $vehicule['id_vehicule'],
                "ancien_statut" => $ancienStatut,
                "nouveau_statut" => $nouveauStatut,
                "id_utilisateur" => $faker->randomElement($idsUtilisateurs),
                "date" => $faker->dateTimeBetween('-6 months', 'now')->format('Y-m-d H:i:s'),
                "raison" => $faker->boolean(50) ? $faker->sentence(6) : null,
            ]);
            $ancienStatut = $nouveauStatut;
            $nbHistorique++;
        }
    }
    echo "Historique de statut généré ($nbHistorique)\n";

    $pdo->commit();
    echo "\n✅ Peuplement terminé avec succès !\n";
    echo "Comptes agents de test créés avec le mot de passe : password123\n";

} catch (\Exception $e) {
    $pdo->rollBack();
    echo "\n❌ Erreur pendant le peuplement, rollback effectué : " . $e->getMessage() . "\n";
}
