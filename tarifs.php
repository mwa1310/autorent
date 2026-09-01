<?php $pageActive = 'tarifs'; $roleConnecte = 'admin'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>AutoRent - Catégories & tarifs</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="assets/toastr/toastr.min.css">
    <link rel="stylesheet" href="style/style.css">
    <style>
        .onglets-cat { border-bottom: 1px solid #e5e7eb; margin-bottom: 20px; }
        .onglets-cat .onglet { display:inline-block; padding:10px 4px; margin-right:28px; font-size:14px; font-weight:600; color:#6b7280; cursor:pointer; border-bottom:2px solid transparent; }
        .onglets-cat .onglet.actif { color:#6366f1; border-color:#6366f1; }
        .table-categories th { text-transform:uppercase; font-size:11px; color:#9ca3af; font-weight:600; border-bottom:1px solid #f0f1f4; padding:10px 8px; white-space:nowrap; }
        .table-categories td { padding:14px 8px; border-bottom:1px solid #f6f7f9; font-size:13.5px; vertical-align:middle; }
        .icone-cat-mini { width:40px; height:40px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:17px; }
        .badge-code-cat { font-size:11px; color:#6b7280; background:#f3f4f6; padding:1px 8px; border-radius:5px; }
        .badge-etat-cat { font-size: 11px; font-weight: 600; padding: 3px 10px; border-radius: 999px; }
        .badge-etat-cat.actif { background:#e9f7ef; color:#16a34a; }
        .badge-etat-cat.inactif { background:#f3f4f6; color:#6b7280; }
    </style>
</head>
<body>
    <?php include __DIR__ . '/includes/sidebar.php'; ?>

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
            <h1>Catégories &amp; tarifs</h1>
            <p class="sous-titre">Gérez les catégories de véhicules et leurs tarifs de location</p>
        </div>

        <div class="row row-cols-1 row-cols-md-4 g-3 mb-4">
            <div class="col"><div class="carte-stat">
                <div class="icone-stat" style="background:#e8f0fe; color:#2563eb;"><i class="fa-solid fa-tags"></i></div>
                <div class="valeur-stat" id="stat-total-cat">-</div>
                <div class="libelle-stat">Catégories totales</div>
                <div class="tendance"><span class="contexte" id="stat-vehicules-classes"></span></div>
            </div></div>
            <div class="col"><div class="carte-stat">
                <div class="icone-stat" style="background:#e9f7ef; color:#16a34a;"><i class="fa-solid fa-calendar"></i></div>
                <div class="valeur-stat" id="stat-prix-moyen">-</div>
                <div class="libelle-stat">Prix moyen / jour</div>
                <div class="tendance"><span class="contexte">Toutes catégories confondues</span></div>
            </div></div>
            <div class="col"><div class="carte-stat">
                <div class="icone-stat" style="background:#f1eafd; color:#7c3aed;"><i class="fa-solid fa-arrow-trend-up"></i></div>
                <div class="valeur-stat" id="stat-tarif-min">-</div>
                <div class="libelle-stat">Tarif le plus bas</div>
                <div class="tendance"><span class="contexte" id="stat-cat-min"></span></div>
            </div></div>
            <div class="col"><div class="carte-stat">
                <div class="icone-stat" style="background:#fef3e2; color:#d97706;"><i class="fa-solid fa-star"></i></div>
                <div class="valeur-stat" id="stat-tarif-max">-</div>
                <div class="libelle-stat">Tarif le plus élevé</div>
                <div class="tendance"><span class="contexte" id="stat-cat-max"></span></div>
            </div></div>
        </div>

        <div class="d-flex justify-content-between align-items-center">
            <div class="onglets-cat">
                <span class="onglet actif" data-onglet="categories">Catégories</span>
                <!-- <span class="onglet" data-onglet="options">Tarifs &amp; options</span> -->
            </div>
            <button class="btn btn-primary mb-2" id="btn-nouvelle-categorie" style="background:#6366f1; border:none;">
                <i class="fa-solid fa-plus"></i> Nouvelle catégorie
            </button>
        </div>

        <div id="vue-categories" class="panneau">
            <div class="table-responsive">
                <table class="table-categories w-100">
                    <thead>
                        <tr>
                            <th>Catégorie</th><th>Description</th><th>Exemples de véhicules</th>
                            <th>Tarif journalier</th><th>Tarif hebdomadaire</th><th>Tarif mensuel</th>
                            <th>Véhicules</th><th>Statut</th><th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="corps-table-categories">
                        <tr><td colspan="9" class="text-center text-muted py-4">Chargement...</td></tr>
                    </tbody>
                </table>
            </div>
            <small class="text-muted" id="info-total-cat"></small>
        </div>

        <div id="vue-options" class="panneau" style="display:none;">
            <div class="text-center text-muted py-5">
                <i class="fa-solid fa-flask" style="font-size:32px;"></i>
                <p class="mt-3 mb-0">Fonctionnalité à venir.</p>
                <p class="mb-0">La gestion des options (assurance, kilométrage illimité, conducteur additionnel...) nécessite une table dédiée qui n'existe pas encore dans le modèle de données actuel.</p>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-categorie" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="form-categorie">
                    <div class="modal-header">
                        <h5 class="modal-title" id="titre-modal-categorie">Nouvelle catégorie</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="categorie-id">
                        <div class="mb-2">
                            <label class="form-label">Nom *</label>
                            <input type="text" class="form-control" id="categorie-nom" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" id="categorie-description" rows="2"></textarea>
                        </div>
                        <div class="row g-2">
                            <div class="col-4 mb-2">
                                <label class="form-label">Tarif / jour *</label>
                                <input type="number" class="form-control" id="categorie-tarif-jour" min="0" required>
                            </div>
                            <div class="col-4 mb-2">
                                <label class="form-label">Tarif / semaine</label>
                                <input type="number" class="form-control" id="categorie-tarif-hebdo" min="0">
                            </div>
                            <div class="col-4 mb-2">
                                <label class="form-label">Tarif / mois</label>
                                <input type="number" class="form-control" id="categorie-tarif-mensuel" min="0">
                            </div>
                        </div>
                        <small class="text-muted">Laissés vides, les tarifs hebdo/mensuel sont calculés automatiquement (6 jours facturés/semaine, 24 jours/mois). Les exemples de véhicules et le nombre de véhicules sont calculés automatiquement depuis le parc.</small>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary" style="background:#6366f1; border:none;">Enregistrer</button>
                    </div>
                </form>
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

        const utilisateurConnecte = JSON.parse(localStorage.getItem('utilisateur') || '{}');
        const estAdmin = utilisateurConnecte.role === 'admin';
        if (!estAdmin) {
            $('.menu-admin-only').hide();
            $('#btn-nouvelle-categorie').hide();
        }
        // -----------------------------------------------------------
        // En-tête : identité de l'utilisateur connecté
        // -----------------------------------------------------------
        const prenomUtilisateur = utilisateurConnecte.prenom || 'Admin';
        const nomUtilisateur = utilisateurConnecte.nom || '';
        const initiales = (prenomUtilisateur[0] || 'A') + (nomUtilisateur[0] || 'D');

        document.getElementById('avatar-initiales').textContent = initiales.toUpperCase();
        document.getElementById('topbar-nom-utilisateur').textContent = prenomUtilisateur;
        document.getElementById('topbar-role-utilisateur').textContent = 'Administrateur';

        // Date du jour affichée dans la topbar
        document.getElementById('date-du-jour').textContent =
            new Date().toLocaleDateString('fr-FR', { day: '2-digit', month: 'long', year: 'numeric' });


        const modalCategorie = new bootstrap.Modal(document.getElementById('modal-categorie'));
        const iconesCategorie = ['fa-car', 'fa-car-side', 'fa-truck-pickup', 'fa-car-rear', 'fa-truck', 'fa-bus'];
        const couleursCategorie = [
            { bg: '#e8f0fe', fg: '#2563eb' }, { bg: '#e9f7ef', fg: '#16a34a' }, { bg: '#f1eafd', fg: '#7c3aed' },
            { bg: '#fef3e2', fg: '#d97706' }, { bg: '#fdeaea', fg: '#dc2626' }, { bg: '#e0f7f7', fg: '#0d9488' },
        ];

        $('.onglet').on('click', function () {
            $('.onglet').removeClass('actif');
            $(this).addClass('actif');
            const cible = $(this).data('onglet');
            $('#vue-categories').toggle(cible === 'categories');
            $('#vue-options').toggle(cible === 'options');
        });

        function formaterMontant(m) {
            return m === null ? '-' : Number(m).toLocaleString('fr-FR') + ' F CFA';
        }

        function chargerStats() {
            $.getJSON('api/categories_stats.php', function (r) {
                $('#stat-total-cat').text(r.total_categories);
                $('#stat-vehicules-classes').text(r.vehicules_classes + ' véhicules classés');
                $('#stat-prix-moyen').text(formaterMontant(r.prix_moyen_jour));
                $('#stat-tarif-min').text(formaterMontant(r.tarif_min));
                $('#stat-cat-min').text('Catégorie ' + r.categorie_min);
                $('#stat-tarif-max').text(formaterMontant(r.tarif_max));
                $('#stat-cat-max').text('Catégorie ' + r.categorie_max);
            }).fail(() => toastr.error("Impossible de charger les statistiques.", 'Erreur'));
        }

        function chargerListe() {
            $.getJSON('api/categories_liste_complete.php', function (r) {
                $('#info-total-cat').text(`Affichage de 1 à ${r.total} sur ${r.total} catégories`);
                const $corps = $('#corps-table-categories').empty();
                if (r.data.length === 0) {
                    $corps.append('<tr><td colspan="9" class="text-center text-muted py-4">Aucune catégorie.</td></tr>');
                    return;
                }
                r.data.forEach((c, i) => {
                    const icone = iconesCategorie[i % iconesCategorie.length];
                    const couleur = couleursCategorie[i % couleursCategorie.length];
                    const actions = estAdmin ? `
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown"><i class="fa-solid fa-ellipsis"></i></button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item btn-editer-cat" href="#" data-id="${c.id_categorie}">Modifier</a></li>
                                <li><a class="dropdown-item btn-toggle-etat-cat" href="#" data-id="${c.id_categorie}">${c.etat === 'actif' ? 'Désactiver' : 'Activer'}</a></li>
                            </ul>
                        </div>` : '<span class="text-muted">-</span>';

                    $corps.append(`
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="icone-cat-mini" style="background:${couleur.bg}; color:${couleur.fg};"><i class="fa-solid ${icone}"></i></div>
                                    <div>
                                        <div><strong>${c.nom}</strong></div>
                                        <span class="badge-code-cat">ID: ${c.code}</span>
                                    </div>
                                </div>
                            </td>
                            <td style="max-width:220px;">${c.description || '-'}</td>
                            <td style="max-width:220px;">${c.exemples}</td>
                            <td>${formaterMontant(c.tarif_jour)}</td>
                            <td>${formaterMontant(c.tarif_hebdomadaire)}</td>
                            <td>${formaterMontant(c.tarif_mensuel)}</td>
                            <td>${c.nb_vehicules} véhicules</td>
                            <td><span class="badge-etat-cat ${c.etat}">${c.etat === 'actif' ? 'Actif' : 'Inactif'}</span></td>
                            <td>${actions}</td>
                        </tr>
                    `);
                });
            }).fail(() => toastr.error("Impossible de charger les catégories.", 'Erreur'));
        }

        $('#btn-nouvelle-categorie').on('click', function () {
            $('#form-categorie')[0].reset();
            $('#categorie-id').val('');
            $('#titre-modal-categorie').text('Nouvelle catégorie');
            modalCategorie.show();
        });

        $('#corps-table-categories').on('click', '.btn-editer-cat', function (e) {
            e.preventDefault();
            const id = $(this).data('id');
            $.getJSON('api/categorie_detail.php', { id }, function (r) {
                const c = r.data;
                $('#categorie-id').val(c.id_categorie);
                $('#categorie-nom').val(c.nom);
                $('#categorie-description').val(c.description);
                $('#categorie-tarif-jour').val(c.tarif_jour);
                $('#categorie-tarif-hebdo').val(c.tarif_hebdomadaire);
                $('#categorie-tarif-mensuel').val(c.tarif_mensuel);
                $('#titre-modal-categorie').text('Modifier la catégorie');
                modalCategorie.show();
            }).fail(() => toastr.error("Impossible de charger cette catégorie.", 'Erreur'));
        });

        $('#form-categorie').on('submit', function (e) {
            e.preventDefault();
            const id = $('#categorie-id').val();
            const donnees = {
                nom: $('#categorie-nom').val(),
                description: $('#categorie-description').val(),
                tarif_jour: $('#categorie-tarif-jour').val(),
                tarif_hebdomadaire: $('#categorie-tarif-hebdo').val(),
                tarif_mensuel: $('#categorie-tarif-mensuel').val(),
            };
            const url = id ? `api/categorie_modifier.php?id=${id}` : 'api/categorie_creer.php';
            $.ajax({
                url, method: id ? 'PUT' : 'POST', contentType: 'application/json', data: JSON.stringify(donnees),
                success: function (r) {
                    modalCategorie.hide();
                    toastr.success(r.message, 'Succès');
                    chargerListe(); chargerStats();
                },
                error: function (xhr) {
                    const r = xhr.responseJSON || {};
                    if (xhr.status !== 401) toastr.error(r.message || "Erreur.", 'Erreur');
                }
            });
        });

        $('#corps-table-categories').on('click', '.btn-toggle-etat-cat', function (e) {
            e.preventDefault();
            const id = $(this).data('id');
            $.ajax({
                url: `api/categorie_changer_etat.php?id=${id}`, method: 'PUT',
                success: function (r) { toastr.success(r.message, 'Statut modifié'); chargerListe(); },
                error: function (xhr) { const r = xhr.responseJSON || {}; if (xhr.status !== 401) toastr.error(r.message || "Erreur.", 'Erreur'); }
            });
        });

        chargerStats();
        chargerListe();
    </script>
</body>
</html>
