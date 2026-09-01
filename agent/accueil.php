<?php
$pageActive = 'accueil';
$roleConnecte = 'agent'; // Affichage uniquement ; la vraie vérification se fait via le JWT côté API
$prefixeRacine = '../';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>AutoRent - Accueil Agent</title>
    <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../assets/toastr/toastr.min.css">
    <link rel="stylesheet" href="../style/style.css">
</head>
<body>

    <?php include __DIR__ . '/../includes/sidebar.php'; ?>

    <div class="contenu-principal">
        <!-- ================= Topbar ================= -->
        <div class="topbar">
            <div class="d-flex align-items-center gap-3">
                
            </div>
            <div class="zone-droite">
                <div class="selecteur-date">
                    <i class="fa-regular fa-calendar"></i> <span id="date-du-jour">-</span> <i class="fa-solid fa-chevron-down" style="font-size:10px;"></i>
                </div>
                <div class="cloche-notif">
                    <i class="fa-regular fa-bell"></i>
                    <span class="badge-notif">5</span>
                </div>
                <div class="profil-utilisateur">
                    <div class="avatar-initiales" id="avatar-initiales">-</div>
                    <div>
                        <div class="nom-utilisateur-topbar" id="topbar-nom-utilisateur">-</div>
                        <div class="role-utilisateur-topbar" id="topbar-role-utilisateur">-</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="entete-page d-flex justify-content-between align-items-start">
            <div>            
                <h1 id="salutation-utilisateur">Bonjour 👋</h1>
                <p class="sous-titre">Voici l'activité du jour</p>
            </div>
                <a href="../reservations.php?action=nouvelle" class="btn btn-connexion" style="width:auto; background:#6366f1; color:#fff; padding:10px 20px; align-items:left; border-radius:8px; text-decoration:none; font-weight:600; font-size:14px;">
                + Nouvelle réservation
                </a>
        </div>

         <!-- Cartes de statistiques rapides -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="carte-stat">
                    <div class="icone-stat" style="background:#e8f0fe; color:#2563eb;"><i class="fa-solid fa-car-side"></i></div>
                    <div class="valeur-stat" id="stat-vehicules-disponibles">-</div>
                    <div class="libelle-stat">Véhicules disponibles maintenant</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="carte-stat">
                    <div class="icone-stat" style="background:#fef3e2; color:#d97706;"><i class="fa-solid fa-arrow-up"></i></div>
                    <div class="valeur-stat" id="stat-departs-jour">-</div>
                    <div class="libelle-stat">Départs prévus aujourd'hui</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="carte-stat">
                    <div class="icone-stat" style="background:#e9f7ef; color:#16a34a;"><i class="fa-solid fa-arrow-down"></i></div>
                    <div class="valeur-stat" id="stat-retours-jour">-</div>
                    <div class="libelle-stat">Retours prévus aujourd'hui</div>
                </div>
            </div>
        </div>
 
        <!-- Réservations du jour -->
        <h5 class="mb-3">Réservations à traiter aujourd'hui</h5>
        <div class="table-responsive" style="background:#fff; border-radius:12px; border:1px solid #edeef2;">
            <table class="table mb-0" id="table-reservations-jour">
                <thead>
                    <tr>
                        <th>Client</th>
                        <th>Véhicule</th>
                        <th>Type</th>
                        <th>Heure prévue</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td colspan="5" class="text-center text-muted py-4">Chargement...</td></tr>
                </tbody>
            </table>
        </div>
 
        <!-- Liens rapides -->
        <!-- <h5 class="mt-4 mb-3">Accès rapide</h5>
        <div class="row g-3">
            <div class="col-md-3">
                <a href="../vehicules.php" class="carte-lien-rapide">
                    <div class="icone-lien"><i class="fa-solid fa-car-side"></i></div>
                    <div class="titre-lien">Véhicules</div>
                </a>
            </div>
            <div class="col-md-3">
                <a href="../reservations.php" class="carte-lien-rapide">
                    <div class="icone-lien"><i class="fa-solid fa-calendar-days"></i></div>
                    <div class="titre-lien">Réservations</div>
                </a>
            </div>
            <div class="col-md-3">
                <a href="../clients.php" class="carte-lien-rapide">
                    <div class="icone-lien"><i class="fa-solid fa-user"></i></div>
                    <div class="titre-lien">Clients</div>
                </a>
            </div>
            <div class="col-md-3">
                <a href="../paiements.php" class="carte-lien-rapide">
                    <div class="icone-lien"><i class="fa-solid fa-money-bill-wave"></i></div>
                    <div class="titre-lien">Paiements</div>
                </a>
            </div>
        </div>
    </div> -->

    <script src="../assets/jquery/jquery.min.js"></script>
    <script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/toastr/toastr.min.js"></script>
    <script>const URL_CONNEXION = '../index.php';</script>
    <script src="../assets/session.js"></script>

    <script>
        exigerConnexionSinonRediriger();

        const utilisateurConnecte = JSON.parse(localStorage.getItem('utilisateur') || '{}');
        if (utilisateurConnecte.role === 'admin') {
        }

        // -----------------------------------------------------------
        // En-tête : identité de l'utilisateur connecté
        // -----------------------------------------------------------
        const prenomUtilisateur = utilisateurConnecte.prenom || 'Agent';
        const nomUtilisateur = utilisateurConnecte.nom || '';
        const initiales = (prenomUtilisateur[0] || 'A') + (nomUtilisateur[0] || 'G');

        document.getElementById('salutation-utilisateur').textContent = 'Bonjour, ' + prenomUtilisateur + ' 👋';
        document.getElementById('avatar-initiales').textContent = initiales.toUpperCase();
        document.getElementById('topbar-nom-utilisateur').textContent = prenomUtilisateur;
        document.getElementById('topbar-role-utilisateur').textContent = 'Agent';

        // Date du jour affichée dans la topbar
        document.getElementById('date-du-jour').textContent =
            new Date().toLocaleDateString('fr-FR', { day: '2-digit', month: 'long', year: 'numeric' });



        $(function () {
            // -----------------------------------------------------------
            // Statistiques du jour
            // -----------------------------------------------------------
            $.getJSON('../api/stats_accueil.php', function (reponse) {
                $('#stat-vehicules-disponibles').text(reponse.vehicules_disponibles);
                $('#stat-departs-jour').text(reponse.departs_jour);
                $('#stat-retours-jour').text(reponse.retours_jour);
            }).fail(() => toastr.error("Impossible de charger les statistiques.", 'Erreur'));
 
            // -----------------------------------------------------------
            // Réservations à traiter aujourd'hui
            // -----------------------------------------------------------
            $.getJSON('../api/reservations_du_jour.php', function (reponse) {
                const $corpsTable = $('#table-reservations-jour tbody').empty();
 
                if (reponse.data.length === 0) {
                    $corpsTable.append('<tr><td colspan="5" class="text-center text-muted py-4">Aucun départ ni retour prévu aujourd\'hui.</td></tr>');
                    return;
                }
 
                reponse.data.forEach(r => {
                    const icone = r.type_classe === 'depart' ? 'fa-arrow-up-from-bracket' : 'fa-arrow-down-to-bracket';
                    $corpsTable.append(`
                        <tr>
                            <td>${r.client}</td>
                            <td>${r.vehicule}</td>
                            <td><i class="fa-solid ${icone}"></i> ${r.type}</td>
                            <td>${r.heure_prevue}</td>
                            <td><span class="badge-statut ${r.statut}">${r.statut_libelle}</span></td>
                        </tr>
                    `);
                });
            }).fail(() => {
                $('#table-reservations-jour tbody').html('<tr><td colspan="5" class="text-center text-muted py-4">Erreur de chargement.</td></tr>');
                toastr.error("Impossible de charger les réservations du jour.", 'Erreur');
            });
        });
    </script>
</body>
</html>
