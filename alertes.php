<?php $pageActive = 'alertes'; $roleConnecte = 'admin'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>AutoRent - Alertes</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="assets/toastr/toastr.min.css">
    <link rel="stylesheet" href="style/style.css">
    <style>
        .carte-alerte { display:flex; align-items:center; gap:14px; padding:14px 16px; border:1px solid #f0f1f4; border-radius:10px; margin-bottom:10px; background:#fff; }
        .carte-alerte .icone-alerte { width:40px; height:40px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:16px; flex-shrink:0; }
        .carte-alerte.danger .icone-alerte { background:#fdeaea; color:#dc2626; }
        .carte-alerte.warning .icone-alerte { background:#fef3e2; color:#d97706; }
        .carte-alerte .titre-alerte { font-weight:600; font-size:14px; }
        .carte-alerte .sous-alerte { font-size:12.5px; color:#9ca3af; }
        .badge-gravite { font-size:10.5px; font-weight:600; padding:3px 9px; border-radius:999px; margin-left:auto; }
        .badge-gravite.danger { background:#fdeaea; color:#dc2626; }
        .badge-gravite.warning { background:#fef3e2; color:#d97706; }
        .badge-action { font-size:11px; font-weight:600; padding:3px 10px; border-radius:999px; }
        .badge-action.creation { background:#e9f7ef; color:#16a34a; }
        .badge-action.modification { background:#e8f0fe; color:#2563eb; }
        .badge-action.suppression { background:#fdeaea; color:#dc2626; }
        .badge-action.autre { background:#f1f2f6; color:#6b7280; }
        .table-journal th { text-transform:uppercase; font-size:11px; color:#9ca3af; font-weight:600; border-bottom:1px solid #f0f1f4; padding:10px 8px; }
        .table-journal td { padding:12px 8px; border-bottom:1px solid #f6f7f9; font-size:13.5px; vertical-align:middle; }
        .pagination-perso .page-btn { width:32px; height:32px; border:1px solid #e5e7eb; border-radius:6px; background:#fff; display:flex; align-items:center; justify-content:center; font-size:13px; cursor:pointer; }
        .pagination-perso .page-btn.actif { background:#6366f1; color:#fff; border-color:#6366f1; }
        .pagination-perso .page-btn.desactive { opacity:0.4; pointer-events:none; }
        .nav-tabs-alertes .nav-link { color:#6b7280; font-weight:600; font-size:13.5px; }
        .nav-tabs-alertes .nav-link.active { color:#4338ca; border-color:#4338ca #4338ca #fff; }
    </style>
</head>
<body>
    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <div class="contenu-principal">
        <!--   Topbar   -->
        <div class="topbar">
            <div class="d-flex align-items-center gap-3"></div>
            <div class="zone-droite">
                <div class="selecteur-date">
                    <i class="fa-regular fa-calendar"></i> <span id="date-du-jour">-</span>
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

        <div class="d-flex justify-content-between align-items-start mb-3">
            <div class="entete-page">
                <h1>Alertes &amp; Activité</h1>
                <p class="sous-titre">Suivez les points d'attention et les dernières actions effectuées dans l'application.</p>
            </div>
            <button class="btn btn-outline-secondary" id="btn-rafraichir">
                <i class="fa-solid fa-rotate"></i> Rafraîchir
            </button>
        </div>

        <div class="row row-cols-1 row-cols-md-4 g-3 mb-4">
            <div class="col"><div class="carte-stat">
                <div class="icone-stat" style="background:#eef0fe; color:#4338ca;"><i class="fa-solid fa-bell"></i></div>
                <div class="valeur-stat" id="stat-total-alertes">-</div>
                <div class="libelle-stat">Alertes actives</div>
            </div></div>
            <div class="col"><div class="carte-stat">
                <div class="icone-stat" style="background:#fdeaea; color:#dc2626;"><i class="fa-solid fa-triangle-exclamation"></i></div>
                <div class="valeur-stat" id="stat-critiques">-</div>
                <div class="libelle-stat">Critiques</div>
            </div></div>
            <div class="col"><div class="carte-stat">
                <div class="icone-stat" style="background:#fef3e2; color:#d97706;"><i class="fa-solid fa-clock"></i></div>
                <div class="valeur-stat" id="stat-surveiller">-</div>
                <div class="libelle-stat">À surveiller</div>
            </div></div>
            <div class="col"><div class="carte-stat">
                <div class="icone-stat" style="background:#e8f0fe; color:#2563eb;"><i class="fa-solid fa-list-check"></i></div>
                <div class="valeur-stat" id="stat-actions-jour">-</div>
                <div class="libelle-stat">Actions aujourd'hui</div>
            </div></div>
        </div>

        <ul class="nav nav-tabs nav-tabs-alertes mb-3" id="onglets-alertes">
            <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#panneau-alertes" type="button">
                    <i class="fa-solid fa-bell"></i> Alertes
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#panneau-journal" type="button">
                    <i class="fa-solid fa-clock-rotate-left"></i> Journal d'activité
                </button>
            </li>
        </ul>

        <div class="tab-content">
            <!-- Onglet Alertes -->
            <div class="tab-pane fade show active" id="panneau-alertes">
                <div class="panneau">
                    <div class="entete-panneau">
                        <h6>Alertes en cours</h6>
                    </div>
                    <div id="liste-alertes">
                        <p class="text-center text-muted py-4">Chargement...</p>
                    </div>
                </div>
            </div>

            <!-- Onglet Journal d'activité -->
            <div class="tab-pane fade" id="panneau-journal">
                <div class="panneau mb-3">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label" style="font-size:12.5px; font-weight:600;">Type</label>
                            <select id="filtre-type-entite" class="form-select">
                                <option value="">Tous les types</option>
                                <option value="vehicule">Véhicule</option>
                                <option value="client">Client</option>
                                <option value="reservation">Réservation</option>
                                <option value="paiement">Paiement</option>
                                <option value="maintenance">Maintenance</option>
                                <option value="categorie">Catégorie</option>
                                <option value="utilisateur">Utilisateur</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" style="font-size:12.5px; font-weight:600;">Action</label>
                            <select id="filtre-action" class="form-select">
                                <option value="">Toutes les actions</option>
                                <option value="creation">Création</option>
                                <option value="modification">Modification</option>
                                <option value="suppression">Suppression</option>
                                <option value="autre">Autre</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-primary w-100" id="btn-filtrer-journal" style="background:#6366f1; border:none;">Filtrer</button>
                        </div>
                    </div>
                </div>

                <div class="panneau">
                    <div class="entete-panneau">
                        <h6>Dernières actions</h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table-journal w-100">
                            <thead>
                                <tr>
                                    <th>Date / heure</th><th>Utilisateur</th><th>Rôle</th>
                                    <th>Action</th><th>Type</th><th>Détail</th>
                                </tr>
                            </thead>
                            <tbody id="corps-table-journal">
                                <tr><td colspan="6" class="text-center text-muted py-4">Chargement...</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <small class="text-muted" id="info-pagination-journal"></small>
                        <div class="d-flex gap-1 pagination-perso" id="zone-pagination-journal"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/jquery/jquery.min.js"></script>
    <script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/toastr/toastr.min.js"></script>
    <script>const URL_CONNEXION = 'index.php';</script>
    <script src="assets/session.js"></script>

    <script>
        toastr.options = { closeButton: true, progressBar: true, positionClass: 'toast-top-right', timeOut: 3500 };
        exigerConnexionSinonRediriger();

        // Intervalle de rafraîchissement automatique (ms) - ajustez selon vos besoins.
        const INTERVALLE_RAFRAICHISSEMENT_MS = 20000;

        const utilisateurConnecte = JSON.parse(localStorage.getItem('utilisateur') || '{}');
        const estAdmin = utilisateurConnecte.role === 'admin';
        if (!estAdmin) {
            $('.menu-admin-only').hide(); // la sidebar est rendue côté PHP sans connaître le vrai rôle sur cette page partagée
            const prenomUtilisateur = utilisateurConnecte.prenom || 'Agent';
            const nomUtilisateur = utilisateurConnecte.nom || '';
            const initiales = (prenomUtilisateur[0] || 'A') + (nomUtilisateur[0] || 'G');
            document.getElementById('avatar-initiales').textContent = initiales.toUpperCase();
            document.getElementById('topbar-nom-utilisateur').textContent = prenomUtilisateur;
            document.getElementById('topbar-role-utilisateur').textContent = 'Agent';
        } else {
            const prenomUtilisateur = utilisateurConnecte.prenom || 'Admin';
            const nomUtilisateur = utilisateurConnecte.nom || '';
            const initiales = (prenomUtilisateur[0] || 'A') + (nomUtilisateur[0] || 'D');
            document.getElementById('avatar-initiales').textContent = initiales.toUpperCase();
            document.getElementById('topbar-nom-utilisateur').textContent = prenomUtilisateur;
            document.getElementById('topbar-role-utilisateur').textContent = 'Administrateur';
        }
        document.getElementById('date-du-jour').textContent =
            new Date().toLocaleDateString('fr-FR', { day: '2-digit', month: 'long', year: 'numeric' });

        let ongletJournalActif = false;
        let pageJournalActuelle = 1;

        const ICONES_ALERTE = {
            hors_service: 'fa-car-burst',
            maintenance_en_retard: 'fa-screwdriver-wrench',
            maintenance_a_venir: 'fa-screwdriver-wrench',
            retour_en_retard: 'fa-calendar-xmark',
            fin_reservation_proche: 'fa-calendar-check',
            paiement_en_retard: 'fa-money-bill-transfer',
        };

        function libelleAction(a) {
            return { creation: 'Création', modification: 'Modification', suppression: 'Suppression', autre: 'Autre' }[a] || a;
        }
        function formaterDateHeure(d) {
            const dt = new Date(d);
            return dt.toLocaleDateString('fr-FR', { day: '2-digit', month: 'short' }) + ' à ' +
                   dt.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
        }

        function chargerAlertes() {
            $.getJSON('api/alertes.php', function (reponse) {
                const alertes = reponse.data || [];
                const $liste = $('#liste-alertes').empty();

                if (alertes.length === 0) {
                    $liste.append('<p class="text-center text-muted py-4"><i class="fa-solid fa-circle-check text-success"></i> Aucune alerte active pour le moment.</p>');
                } else {
                    alertes.forEach(function (a) {
                        const icone = ICONES_ALERTE[a.type] || 'fa-circle-exclamation';
                        $liste.append(`
                            <div class="carte-alerte ${a.gravite}">
                                <div class="icone-alerte"><i class="fa-solid ${icone}"></i></div>
                                <div>
                                    <div class="titre-alerte">${a.titre}</div>
                                    <div class="sous-alerte">${a.sous}</div>
                                </div>
                                <span class="badge-gravite ${a.gravite}">${a.gravite === 'danger' ? 'Critique' : 'À surveiller'}</span>
                            </div>
                        `);
                    });
                }

                const critiques = alertes.filter(a => a.gravite === 'danger').length;
                const surveiller = alertes.filter(a => a.gravite === 'warning').length;
                $('#stat-total-alertes').text(reponse.total ?? alertes.length);
                $('#stat-critiques').text(critiques);
                $('#stat-surveiller').text(surveiller);
            }).fail(function (xhr) {
                if (xhr.status !== 401) toastr.error("Impossible de charger les alertes.", 'Erreur');
            });
        }

        function chargerJournal(page) {
            pageJournalActuelle = page;
            const params = {
                page: page,
                type_entite: $('#filtre-type-entite').val(),
                action: $('#filtre-action').val(),
            };
            $.getJSON('api/journal_lister.php', params, function (reponse) {
                const $corps = $('#corps-table-journal').empty();
                const lignes = reponse.data || [];

                if (lignes.length === 0) {
                    $corps.append('<tr><td colspan="6" class="text-center text-muted py-4">Aucune activité enregistrée.</td></tr>');
                } else {
                    lignes.forEach(function (l) {
                        $corps.append(`
                            <tr>
                                <td>${formaterDateHeure(l.cree_le)}</td>
                                <td>${l.nom_utilisateur}</td>
                                <td>${l.role_utilisateur || '-'}</td>
                                <td><span class="badge-action ${l.action}">${libelleAction(l.action)}</span></td>
                                <td>${l.type_entite}</td>
                                <td>${l.description}</td>
                            </tr>
                        `);
                    });
                }

                const debut = (reponse.page - 1) * reponse.par_page + 1;
                const fin = Math.min(reponse.page * reponse.par_page, reponse.total);
                $('#info-pagination-journal').text(reponse.total > 0 ? `Affichage de ${debut} à ${fin} sur ${reponse.total} actions` : '');

                const $pagination = $('#zone-pagination-journal').empty();
                const dernierePage = reponse.total_pages || 1;
                $pagination.append(`<div class="page-btn ${reponse.page <= 1 ? 'desactive' : ''}" data-page="${reponse.page - 1}"><i class="fa-solid fa-chevron-left"></i></div>`);
                for (let p = 1; p <= dernierePage; p++) {
                    if (p === 1 || p === dernierePage || Math.abs(p - reponse.page) <= 1) {
                        $pagination.append(`<div class="page-btn ${p === reponse.page ? 'actif' : ''}" data-page="${p}">${p}</div>`);
                    } else if (Math.abs(p - reponse.page) === 2) {
                        $pagination.append(`<div class="page-btn desactive">…</div>`);
                    }
                }
                $pagination.append(`<div class="page-btn ${reponse.page >= dernierePage ? 'desactive' : ''}" data-page="${reponse.page + 1}"><i class="fa-solid fa-chevron-right"></i></div>`);

                // "Actions aujourd'hui" calculé sur la 1re page uniquement, à titre indicatif.
                if (page === 1) {
                    const aujourdHui = new Date().toDateString();
                    const nbAujourdHui = lignes.filter(l => new Date(l.cree_le).toDateString() === aujourdHui).length;
                    $('#stat-actions-jour').text(reponse.total > 0 ? nbAujourdHui : 0);
                }
            }).fail(function (xhr) {
                if (xhr.status !== 401) toastr.error("Impossible de charger le journal d'activité.", 'Erreur');
            });
        }

        $('#zone-pagination-journal').on('click', '.page-btn:not(.desactive)', function () {
            const p = $(this).data('page');
            if (p) chargerJournal(p);
        });
        $('#btn-filtrer-journal').on('click', function () { chargerJournal(1); });
        $('#btn-rafraichir').on('click', function () {
            chargerAlertes();
            if (ongletJournalActif) chargerJournal(pageJournalActuelle);
            toastr.info('Données actualisées.', '', { timeOut: 1200 });
        });

        $('button[data-bs-target="#panneau-journal"]').on('shown.bs.tab', function () {
            ongletJournalActif = true;
            chargerJournal(1);
        });
        $('button[data-bs-target="#panneau-alertes"]').on('shown.bs.tab', function () {
            ongletJournalActif = false;
        });

        chargerAlertes();
        setInterval(function () {
            chargerAlertes();
            if (ongletJournalActif) chargerJournal(pageJournalActuelle);
        }, INTERVALLE_RAFRAICHISSEMENT_MS);
    </script>
</body>
</html>
