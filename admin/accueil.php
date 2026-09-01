<?php
$pageActive = 'accueil';
$roleConnecte = 'admin'; // Affichage uniquement ; la vraie vérification se fait via le JWT côté API
$prefixeRacine = '../';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>AutoRent - Accueil Admin</title>
    <link rel="stylesheet" href="../assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../assets/toastr/toastr.min.css">
    <link rel="stylesheet" href="../style/style.css">
    <style>
        .zone-graphique-donut { position: relative; width: 160px; height: 160px; margin: 0 auto; }
        .centre-donut {
            position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);
            text-align: center; pointer-events: none;
        }
        .centre-donut .total-donut { font-size: 22px; font-weight: 700; color: #1e2233; }
        .centre-donut .libelle-donut { font-size: 11px; color: #9ca3af; }
    </style>
</head>
<body>

    <?php include __DIR__ . '/../includes/sidebar.php'; ?>

    <div class="contenu-principal">

        <!--   Topbar   -->
        <div class="topbar">
            <div class="d-flex align-items-center gap-3">
                <!-- <div class="btn-icone" id="btn-toggle-sidebar"><i class="fa-solid fa-bars"></i></div> -->
            </div>
            <div class="zone-droite">
                <div class="selecteur-date">
                    <i class="fa-regular fa-calendar"></i> <span id="date-du-jour">-</span> 
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

        <div class="entete-page">
            <h1 id="salutation-utilisateur">Bonjour 👋</h1>
            <p class="sous-titre">Voici un aperçu complet de l'activité de votre agence aujourd'hui.</p>
        </div>

         <!--   Cartes statistiques   -->
        <div class="row row-cols-1 row-cols-md-5 g-3 mb-4">
            <div class="col">
                <div class="carte-stat">
                    <div class="icone-stat" style="background:#e8f0fe; color:#2563eb;"><i class="fa-solid fa-car-side"></i></div>
                    <div class="valeur-stat" id="stat-vehicules-disponibles">-</div>
                    <div class="libelle-stat">Véhicules disponibles</div>
                    <div class="tendance" id="tendance-vehicules-disponibles"></div>
                </div>
            </div>
            <div class="col">
                <div class="carte-stat">
                    <div class="icone-stat" style="background:#e9f7ef; color:#16a34a;"><i class="fa-solid fa-calendar-check"></i></div>
                    <div class="valeur-stat" id="stat-reservations-cours">-</div>
                    <div class="libelle-stat">Réservations en cours</div>
                    <div class="tendance"><span class="contexte">en cours aujourd'hui</span></div>
                </div>
            </div>
            <div class="col">
                <div class="carte-stat">
                    <div class="icone-stat" style="background:#fef3e2; color:#d97706;"><i class="fa-solid fa-sack-dollar"></i></div>
                    <div class="valeur-stat" id="stat-ca-mois">-</div>
                    <div class="libelle-stat">Chiffre d'affaires (mois)</div>
                    <div class="tendance" id="tendance-ca-mois"></div>
                </div>
            </div>
            <div class="col">
                <div class="carte-stat">
                    <div class="icone-stat" style="background:#f1eafd; color:#7c3aed;"><i class="fa-solid fa-building-columns"></i></div>
                    <div class="valeur-stat" id="stat-paiements-mois">-</div>
                    <div class="libelle-stat">Paiements reçus (mois)</div>
                    <div class="tendance" id="tendance-paiements-mois"></div>
                </div>
            </div>
            <div class="col">
                <div class="carte-stat">
                    <div class="icone-stat" style="background:#fdeaea; color:#dc2626;"><i class="fa-solid fa-users"></i></div>
                    <div class="valeur-stat" id="stat-nouveaux-clients">-</div>
                    <div class="libelle-stat">Nouveaux clients (mois)</div>
                    <div class="tendance" id="tendance-nouveaux-clients"></div>
                </div>
            </div>
        </div>
 
        <!--   Graphiques   -->
        <div class="row g-3 mb-4">
            <div class="col-lg-5">
                <div class="panneau">
                    <div class="entete-panneau">
                        <h6>Évolution du chiffre d'affaires</h6>
                        <select class="form-select form-select-sm" style="width:auto; font-size:12.5px;">
                            <option>6 derniers mois</option>
                            <option>12 derniers mois</option>
                        </select>
                    </div>
                    <canvas id="graphique-ca" height="180"></canvas>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="panneau">
                    <div class="entete-panneau"><h6>Réservations par statut</h6></div>
                    <div class="zone-graphique-donut">
                        <canvas id="graphique-reservations-statut"></canvas>
                        <div class="centre-donut">
                            <div class="total-donut" id="total-reservations">186</div>
                            <div class="libelle-donut"></div>
                        </div>
                    </div>
                    <ul class="legende-donut">
                        <li><span><span class="puce" style="background:#2563eb;"></span>À venir</span><span class="valeur" id="legende-a-venir">-</span></li>
                        <li><span><span class="puce" style="background:#16a34a;"></span>En cours</span><span class="valeur" id="legende-en-cours">-</span></li>
                        <li><span><span class="puce" style="background:#f59e0b;"></span>Terminées</span><span class="valeur" id="legende-terminee">-</span></li>
                        <li><span><span class="puce" style="background:#9ca3af;"></span>Annulées</span><span class="valeur" id="legende-annulee">-</span></li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="panneau">
                    <div class="entete-panneau"><h6>Véhicules par statut</h6></div>
                    <div class="zone-graphique-donut" style="width:130px; height:130px;">
                        <canvas id="graphique-vehicules-statut"></canvas>
                        <div class="centre-donut">
                            <div class="total-donut" id="total-vehicules">128</div>
                            <div class="libelle-donut"></div>
                        </div>
                    </div>
                    <ul class="legende-donut">
                        <li><span><span class="puce" style="background:#16a34a;"></span>Disponibles</span><span class="valeur" id="legende-disponibles">-</span></li>
                        <li><span><span class="puce" style="background:#f59e0b;"></span>En maintenance</span><span class="valeur" id="legende-en-maintenance">-</span></li>
                        <li><span><span class="puce" style="background:#dc2626;"></span>Hors service</span><span class="valeur" id="legende-hors-service">-</span></li>
                    </ul>
                    <a href="../vehicules.php" class="lien-voir-tout d-inline-block mt-2">Voir la liste des véhicules <i class="fa-solid fa-arrow-right"></i></a>
                </div>
            </div>
        </div>
 
        <!--   Activité   -->
        <div class="row g-3">
            <div class="col-lg-5">
                <div class="panneau">
                    <div class="entete-panneau">
                        <h6>Réservations récentes</h6>
                        <a href="../reservations.php" class="lien-voir-tout">Voir toutes <i class="fa-solid fa-arrow-right"></i></a>
                    </div>
                    <table class="table-simple">
                        <thead>
                            <tr><th>Réservation</th><th>Client</th><th>Véhicule</th><th>Période</th><th>Statut</th></tr>
                        </thead>
                        <tbody id="corps-table-reservations-recentes">
                            <!-- Rempli en JS avec des données d'exemple ; à remplacer par un appel Ajax -->
                        </tbody>
                    </table>
                </div>
            </div>
 
            <div class="col-lg-4">
                <div class="panneau">
                    <div class="entete-panneau">
                        <h6>Activités récentes</h6>
                        <a href="#" class="lien-voir-tout">Voir toutes <i class="fa-solid fa-arrow-right"></i></a>
                    </div>
                    <div id="liste-activites-recentes"></div>
                </div>
            </div>
 
            <div class="col-lg-3 d-flex flex-column gap-3">
                <div class="panneau">
                    <div class="entete-panneau">
                        <h6>Alertes</h6>
                        <a href="#" class="lien-voir-tout">Voir toutes <i class="fa-solid fa-arrow-right"></i></a>
                    </div>
                    <div id="liste-alertes"></div>
                </div>
 
                <div class="panneau">
                    <div class="entete-panneau">
                        <h6>Top véhicules loués (mois)</h6>
                        <a href="#" class="lien-voir-tout">Voir toutes <i class="fa-solid fa-arrow-right"></i></a>
                    </div>
                    <ul class="liste-top-vehicules" id="liste-top-vehicules"></ul>
                </div>
            </div>
        </div>
 
    </div>

    <script src="../assets/jquery/jquery.min.js"></script>
    <script src="../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/toastr/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <script>
        if (typeof Chart === 'undefined') {
            console.error("Chart.js ne s'est pas chargé depuis le CDN. Vérifie ta connexion internet ou un éventuel bloqueur de script.");
        }
    </script>
    <script>const URL_CONNEXION = '../index.php';</script>
    <script src="../assets/session.js"></script>

    <script>
        exigerConnexionSinonRediriger();

        const utilisateurConnecte = JSON.parse(localStorage.getItem('utilisateur') || '{}');
        if (utilisateurConnecte.role !== 'admin') {
            window.location.href = '../agent/accueil.php';
        }

        // -----------------------------------------------------------
        // En-tête : identité de l'utilisateur connecté
        // -----------------------------------------------------------
        const prenomUtilisateur = utilisateurConnecte.prenom || 'Admin';
        const nomUtilisateur = utilisateurConnecte.nom || '';
        const initiales = (prenomUtilisateur[0] || 'A') + (nomUtilisateur[0] || 'D');

        document.getElementById('salutation-utilisateur').textContent = 'Bonjour, ' + prenomUtilisateur + ' 👋';
        document.getElementById('avatar-initiales').textContent = initiales.toUpperCase();
        document.getElementById('topbar-nom-utilisateur').textContent = prenomUtilisateur;
        document.getElementById('topbar-role-utilisateur').textContent = 'Administrateur';

        // Date du jour affichée dans la topbar
        document.getElementById('date-du-jour').textContent =
            new Date().toLocaleDateString('fr-FR', { day: '2-digit', month: 'long', year: 'numeric' });

        // -----------------------------------------------------------
        // Cartes de statistiques : chargées depuis la base de données
        // -----------------------------------------------------------
        function formaterMontant(valeur) {
            return new Intl.NumberFormat('fr-FR').format(Math.round(valeur)) + ' FCFA';
        }
 
        function afficherTendance($conteneur, variation, contexte) {
            if (variation === null || variation === undefined) {
                $conteneur.html(`<span class="contexte">${contexte}</span>`);
                return;
            }
            const hausse = variation >= 0;
            $conteneur
                .toggleClass('hausse', hausse)
                .toggleClass('baisse', !hausse)
                .html(`<i class="fa-solid fa-arrow-${hausse ? 'up' : 'down'}"></i> ${Math.abs(variation)}% <span class="contexte">${contexte}</span>`);
        }
 
        $.getJSON('../api/stats_accueil.php', function (reponse) {
            $('#stat-vehicules-disponibles').text(reponse.vehicules_disponibles);
            $('#tendance-vehicules-disponibles').html(`<span class="contexte">sur ${reponse.total_vehicules} véhicules</span>`);
 
            $('#stat-reservations-cours').text(reponse.reservations_en_cours);
 
            $('#stat-ca-mois').text(formaterMontant(reponse.ca_mois));
            afficherTendance($('#tendance-ca-mois'), reponse.ca_mois_variation, 'vs mois dernier');
 
            $('#stat-paiements-mois').text(formaterMontant(reponse.paiements_mois));
            afficherTendance($('#tendance-paiements-mois'), reponse.paiements_mois_variation, 'vs mois dernier');
 
            $('#stat-nouveaux-clients').text(reponse.nouveaux_clients_mois);
            afficherTendance($('#tendance-nouveaux-clients'), reponse.nouveaux_clients_variation, 'vs mois dernier');
        }).fail(function (xhr) {
            if (xhr.status !== 401) {
                toastr.error("Impossible de charger les statistiques.", 'Erreur');
            }
        });
 
        // -----------------------------------------------------------
        // Correspondance type d'activité/alerte -> icône + couleur (affichage uniquement)
        // -----------------------------------------------------------
        const stylesActivite = {
            reservation:     { icone: 'fa-car-side',            couleur: '#e8f0fe', iconeCouleur: '#2563eb' },
            paiement:        { icone: 'fa-money-bill-wave',     couleur: '#e9f7ef', iconeCouleur: '#16a34a' },
            maintenance:     { icone: 'fa-screwdriver-wrench',  couleur: '#fef3e2', iconeCouleur: '#d97706' },
            statut_vehicule: { icone: 'fa-arrows-rotate',       couleur: '#f1eafd', iconeCouleur: '#7c3aed' },
        };
        const stylesAlerte = {
            hors_service:        { icone: 'fa-ban',                 couleur: '#fdeaea', iconeCouleur: '#dc2626' },
            maintenance_a_venir: { icone: 'fa-triangle-exclamation', couleur: '#fef3e2', iconeCouleur: '#d97706' },
            retour_en_retard:    { icone: 'fa-clock',                couleur: '#fdeaea', iconeCouleur: '#dc2626' },
        };
 
        // -----------------------------------------------------------
        // Graphique : évolution du chiffre d'affaires (ligne)
        // -----------------------------------------------------------
        if (typeof Chart === 'undefined') {
            toastr.error("La librairie de graphiques n'a pas pu se charger (vérifie ta connexion internet).", 'Erreur');
        } else {
        $.getJSON('../api/graphique_ca.php', function (reponse) {
            new Chart(document.getElementById('graphique-ca'), {
                type: 'line',
                data: {
                    labels: reponse.labels,
                    datasets: [{
                        data: reponse.donnees,
                        borderColor: '#6366f1',
                        backgroundColor: 'rgba(99, 102, 241, 0.08)',
                        fill: true,
                        tension: 0.35,
                        pointBackgroundColor: '#6366f1',
                    }]
                },
                options: {
                    plugins: { legend: { display: false } },
                    scales: { y: { ticks: { callback: v => (v / 1000) + 'k FCFA' } } }
                }
            });
        }).fail(() => toastr.error("Impossible de charger le graphique du CA.", 'Erreur'));
 
        // -----------------------------------------------------------
        // Graphiques en anneau : réservations et véhicules par statut
        // -----------------------------------------------------------
        $.getJSON('../api/repartition_statuts.php', function (reponse) {
            const r = reponse.reservations.repartition;
            const totalReservations = reponse.reservations.total || 1; // évite une division par 0
            document.getElementById('total-reservations').textContent = reponse.reservations.total;
 
            const pct = (valeur) => Math.round((valeur / totalReservations) * 100);
            document.getElementById('legende-a-venir').textContent  = `${r.a_venir} (${pct(r.a_venir)}%)`;
            document.getElementById('legende-en-cours').textContent = `${r.en_cours} (${pct(r.en_cours)}%)`;
            document.getElementById('legende-terminee').textContent = `${r.terminee} (${pct(r.terminee)}%)`;
            document.getElementById('legende-annulee').textContent  = `${r.annulee} (${pct(r.annulee)}%)`;
 
            new Chart(document.getElementById('graphique-reservations-statut'), {
                type: 'doughnut',
                data: {
                    labels: ['À venir', 'En cours', 'Terminées', 'Annulées'],
                    datasets: [{ data: [r.a_venir, r.en_cours, r.terminee, r.annulee], backgroundColor: ['#2563eb', '#16a34a', '#f59e0b', '#9ca3af'], borderWidth: 0 }]
                },
                options: { cutout: '72%', plugins: { legend: { display: false } } }
            });
 
            const v = reponse.vehicules.repartition;
            const totalVehicules = reponse.vehicules.total || 1;
            document.getElementById('total-vehicules').textContent = reponse.vehicules.total;
 
            const pctV = (valeur) => Math.round((valeur / totalVehicules) * 100);
            document.getElementById('legende-disponibles').textContent    = `${v.disponibles} (${pctV(v.disponibles)}%)`;
            document.getElementById('legende-en-maintenance').textContent = `${v.en_maintenance} (${pctV(v.en_maintenance)}%)`;
            document.getElementById('legende-hors-service').textContent   = `${v.hors_service} (${pctV(v.hors_service)}%)`;
 
            new Chart(document.getElementById('graphique-vehicules-statut'), {
                type: 'doughnut',
                data: {
                    labels: ['Disponibles', 'En maintenance', 'Hors service'],
                    datasets: [{ data: [v.disponibles, v.en_maintenance, v.hors_service], backgroundColor: ['#16a34a', '#f59e0b', '#dc2626'], borderWidth: 0 }]
                },
                options: { cutout: '72%', plugins: { legend: { display: false } } }
            });
        }).fail(() => toastr.error("Impossible de charger les répartitions.", 'Erreur'));
        } // fin du else (Chart.js chargé)
 
        // -----------------------------------------------------------
        // Réservations récentes
        // -----------------------------------------------------------
        $.getJSON('../api/reservations_recentes.php', function (reponse) {
            const $corpsTable = $('#corps-table-reservations-recentes');
            if (reponse.data.length === 0) {
                $corpsTable.append('<tr><td colspan="5" class="text-center text-muted py-3">Aucune réservation.</td></tr>');
                return;
            }
            reponse.data.forEach(r => {
                $corpsTable.append(`
                    <tr>
                        <td>${r.id}</td>
                        <td>${r.client}</td>
                        <td>${r.vehicule}</td>
                        <td>${r.periode}</td>
                        <td><span class="badge-statut ${r.statut}">${r.libelle}</span></td>
                    </tr>
                `);
            });
        }).fail(() => toastr.error("Impossible de charger les réservations récentes.", 'Erreur'));
 
        // -----------------------------------------------------------
        // Activités récentes
        // -----------------------------------------------------------
        $.getJSON('../api/activites_recentes.php', function (reponse) {
            const $listeActivites = $('#liste-activites-recentes');
            if (reponse.data.length === 0) {
                $listeActivites.append('<p class="text-muted text-center py-3 mb-0">Aucune activité récente.</p>');
                return;
            }
            reponse.data.forEach(a => {
                const style = stylesActivite[a.type] || { icone: 'fa-circle', couleur: '#f3f4f6', iconeCouleur: '#6b7280' };
                $listeActivites.append(`
                    <div class="item-activite">
                        <div class="icone-activite" style="background:${style.couleur}; color:${style.iconeCouleur};"><i class="fa-solid ${style.icone}"></i></div>
                        <div class="texte-activite">
                            <div class="titre-activite">${a.titre}</div>
                            <div class="sous-activite">${a.sous}</div>
                        </div>
                        <div class="temps-activite">${a.temps}</div>
                    </div>
                `);
            });
        }).fail(() => toastr.error("Impossible de charger les activités récentes.", 'Erreur'));
 
        // -----------------------------------------------------------
        // Alertes
        // -----------------------------------------------------------
        $.getJSON('../api/alertes.php', function (reponse) {
            const $listeAlertes = $('#liste-alertes');
            if (reponse.data.length === 0) {
                $listeAlertes.append('<p class="text-muted text-center py-3 mb-0">Aucune alerte, tout va bien 👍</p>');
                return;
            }
            reponse.data.forEach(a => {
                const style = stylesAlerte[a.type] || { icone: 'fa-circle-info', couleur: '#f3f4f6', iconeCouleur: '#6b7280' };
                $listeAlertes.append(`
                    <div class="item-alerte">
                        <div class="icone-activite" style="background:${style.couleur}; color:${style.iconeCouleur};"><i class="fa-solid ${style.icone}"></i></div>
                        <div class="texte-activite">
                            <div class="titre-activite">${a.titre}</div>
                            <div class="sous-activite">${a.sous}</div>
                        </div>
                    </div>
                `);
            });
        }).fail(() => toastr.error("Impossible de charger les alertes.", 'Erreur'));
 
        // -----------------------------------------------------------
        // Top véhicules loués (mois)
        // -----------------------------------------------------------
        $.getJSON('../api/top_vehicules.php', function (reponse) {
            const $listeTop = $('#liste-top-vehicules');
            if (reponse.data.length === 0) {
                $listeTop.append('<li class="text-muted text-center py-3">Aucune location ce mois-ci.</li>');
                return;
            }
            reponse.data.forEach((v, i) => {
                $listeTop.append(`
                    <li>
                        <span class="rang-vehicule">${i + 1}</span>
                        <span>${v.nom}</span>
                        <span class="nb-locations-vehicule">${v.locations} locations</span>
                    </li>
                `);
            });
        }).fail(() => toastr.error("Impossible de charger le classement des véhicules.", 'Erreur'));
    </script>
</body>
</html>