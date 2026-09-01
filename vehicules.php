<?php $pageActive = 'vehicules'; $roleConnecte = 'admin'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>AutoRent - Véhicules</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="assets/toastr/toastr.min.css">
    <link rel="stylesheet" href="style/style.css">
    <style>
        .badge-cat { font-size: 11px; font-weight: 600; padding: 3px 10px; border-radius: 999px; background:#eef0fe; color:#4338ca; }
        .badge-statut-vehicule { font-size: 11px; font-weight: 600; padding: 3px 10px; border-radius: 999px; }
        .badge-statut-vehicule.disponible { background:#e9f7ef; color:#16a34a; }
        .badge-statut-vehicule.en_location { background:#e8f0fe; color:#2563eb; }
        .badge-statut-vehicule.maintenance { background:#fef3e2; color:#d97706; }
        .badge-statut-vehicule.hors_service { background:#fdeaea; color:#dc2626; }
        .table-vehicules th { text-transform:uppercase; font-size:11px; color:#9ca3af; font-weight:600; border-bottom:1px solid #f0f1f4; padding:10px 8px; }
        .table-vehicules td { padding:12px 8px; border-bottom:1px solid #f6f7f9; font-size:13.5px; vertical-align:middle; }
        .icone-vehicule-mini { width:36px; height:36px; border-radius:8px; background:#eef0fe; color:#4338ca; display:flex; align-items:center; justify-content:center; }
        .pagination-perso .page-btn { width:32px; height:32px; border:1px solid #e5e7eb; border-radius:6px; background:#fff; display:flex; align-items:center; justify-content:center; font-size:13px; cursor:pointer; }
        .pagination-perso .page-btn.actif { background:#6366f1; color:#fff; border-color:#6366f1; }
        .pagination-perso .page-btn.desactive { opacity:0.4; pointer-events:none; }
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

        <div class="d-flex justify-content-between align-items-start mb-3">
            <div class="entete-page">
                <h1>Véhicules</h1>
                <p class="sous-titre">Gérez votre parc automobile et suivez le statut de chaque véhicule.</p>
            </div>
            <button class="btn btn-primary" id="btn-ajouter-vehicule" style="background:#6366f1; border:none;">
                <i class="fa-solid fa-plus"></i> Ajouter un véhicule
            </button>
        </div>

        <div class="row row-cols-1 row-cols-md-4 g-3 mb-4">
            <div class="col"><div class="carte-stat">
                <div class="icone-stat" style="background:#e8f0fe; color:#2563eb;"><i class="fa-solid fa-car-side"></i></div>
                <div class="valeur-stat" id="stat-total">-</div>
                <div class="libelle-stat">Total véhicules</div>
                <div class="tendance"><span class="contexte">Tous statuts confondus</span></div>
            </div></div>
            <div class="col"><div class="carte-stat">
                <div class="icone-stat" style="background:#e9f7ef; color:#16a34a;"><i class="fa-solid fa-car-side"></i></div>
                <div class="valeur-stat" id="stat-disponibles">-</div>
                <div class="libelle-stat">Disponibles</div>
                <div class="tendance" id="pct-disponibles"></div>
            </div></div>
            <div class="col"><div class="carte-stat">
                <div class="icone-stat" style="background:#fef3e2; color:#d97706;"><i class="fa-solid fa-car-side"></i></div>
                <div class="valeur-stat" id="stat-maintenance">-</div>
                <div class="libelle-stat">En maintenance</div>
                <div class="tendance" id="pct-maintenance"></div>
            </div></div>
            <div class="col"><div class="carte-stat">
                <div class="icone-stat" style="background:#fdeaea; color:#dc2626;"><i class="fa-solid fa-car-side"></i></div>
                <div class="valeur-stat" id="stat-hors-service">-</div>
                <div class="libelle-stat">Hors service</div>
                <div class="tendance" id="pct-hors-service"></div>
            </div></div>
        </div>

        <div class="panneau mb-3">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label" style="font-size:12.5px; font-weight:600;">Recherche</label>
                    <input type="text" id="filtre-recherche" class="form-control" placeholder="Marque, modèle, immatriculation...">
                </div>
                <div class="col-md-2">
                    <label class="form-label" style="font-size:12.5px; font-weight:600;">Catégorie</label>
                    <select id="filtre-categorie" class="form-select"><option value="">Toutes les catégories</option></select>
                </div>
                <div class="col-md-2">
                    <label class="form-label" style="font-size:12.5px; font-weight:600;">Statut</label>
                    <select id="filtre-statut" class="form-select">
                        <option value="">Tous les statuts</option>
                        <option value="disponible">Disponible</option>
                        <option value="en_location">En location</option>
                        <option value="maintenance">En maintenance</option>
                        <option value="hors_service">Hors service</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label" style="font-size:12.5px; font-weight:600;">Carburant</label>
                    <select id="filtre-carburant" class="form-select">
                        <option value="">Tous</option>
                        <option value="essence">Essence</option>
                        <option value="diesel">Diesel</option>
                        <option value="hybride">Hybride</option>
                        <option value="electrique">Électrique</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <select id="filtre-tri" class="form-select">
                        <option value="recents">Plus récents</option>
                        <option value="anciens">Plus anciens</option>
                        <option value="km_asc">Kilométrage ↑</option>
                        <option value="km_desc">Kilométrage ↓</option>
                        <option value="annee_desc">Année ↓</option>
                        <option value="annee_asc">Année ↑</option>
                    </select>
                </div>
            </div>
            <a href="#" id="btn-reinitialiser" class="lien-voir-tout d-inline-block mt-2">Réinitialiser les filtres</a>
        </div>

        <div class="panneau">
            <div class="entete-panneau">
                <h6 id="titre-liste">Liste des véhicules</h6>
            </div>
            <div class="table-responsive">
                <table class="table-vehicules w-100">
                    <thead>
                        <tr>
                            <th></th><th>Immatriculation</th><th>Véhicule</th><th>Catégorie</th>
                            <th>Carburant</th><th>Année</th><th>Kilométrage</th><th>Statut</th>
                            <th>Dernière MAJ</th><th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="corps-table-vehicules">
                        <tr><td colspan="10" class="text-center text-muted py-4">Chargement...</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-3">
                <small class="text-muted" id="info-pagination"></small>
                <div class="d-flex gap-1 pagination-perso" id="zone-pagination"></div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-vehicule" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="form-vehicule">
                    <div class="modal-header">
                        <h5 class="modal-title" id="titre-modal-vehicule">Ajouter un véhicule</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="vehicule-id">
                        <div class="row g-2">
                            <div class="col-6 mb-2">
                                <label class="form-label">Immatriculation *</label>
                                <input type="text" class="form-control" id="vehicule-immatriculation" required>
                            </div>
                            <div class="col-6 mb-2">
                                <label class="form-label">Catégorie *</label>
                                <select class="form-select" id="vehicule-categorie" required></select>
                            </div>
                            <div class="col-6 mb-2">
                                <label class="form-label">Marque *</label>
                                <input type="text" class="form-control" id="vehicule-marque" required>
                            </div>
                            <div class="col-6 mb-2">
                                <label class="form-label">Modèle *</label>
                                <input type="text" class="form-control" id="vehicule-modele" required>
                            </div>
                            <div class="col-4 mb-2">
                                <label class="form-label">Année *</label>
                                <input type="number" class="form-control" id="vehicule-annee" min="1990" required>
                            </div>
                            <div class="col-4 mb-2">
                                <label class="form-label">Carburant *</label>
                                <select class="form-select" id="vehicule-carburant" required>
                                    <option value="essence">Essence</option>
                                    <option value="diesel">Diesel</option>
                                    <option value="hybride">Hybride</option>
                                    <option value="electrique">Électrique</option>
                                </select>
                            </div>
                            <div class="col-4 mb-2">
                                <label class="form-label">Kilométrage *</label>
                                <input type="number" class="form-control" id="vehicule-kilometrage" min="0" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary" style="background:#6366f1; border:none;">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-statut" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="form-statut">
                    <div class="modal-header">
                        <h5 class="modal-title">Changer le statut</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="statut-id-vehicule">
                        <div class="mb-2">
                            <label class="form-label">Nouveau statut *</label>
                            <select class="form-select" id="statut-nouveau" required>
                                <option value="disponible">Disponible</option>
                                <option value="en_location">En location</option>
                                <option value="maintenance">En maintenance</option>
                                <option value="hors_service">Hors service</option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Raison (optionnel)</label>
                            <textarea class="form-control" id="statut-raison" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary" style="background:#6366f1; border:none;">Confirmer</button>
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
            $('#btn-ajouter-vehicule').hide();
            $('.menu-admin-only').hide(); // la sidebar est rendue côté PHP sans connaître le vrai rôle sur cette page partagée
            
            const prenomUtilisateur = utilisateurConnecte.prenom || 'Agent';
            const nomUtilisateur = utilisateurConnecte.nom || '';
            const initiales = (prenomUtilisateur[0] || 'A') + (nomUtilisateur[0] || 'G');

            document.getElementById('avatar-initiales').textContent = initiales.toUpperCase();
            document.getElementById('topbar-nom-utilisateur').textContent = prenomUtilisateur;
            document.getElementById('topbar-role-utilisateur').textContent = 'Agent';

        }
        else {
            const prenomUtilisateur = utilisateurConnecte.prenom || 'Admin';
            const nomUtilisateur = utilisateurConnecte.nom || '';
            const initiales = (prenomUtilisateur[0] || 'A') + (nomUtilisateur[0] || 'D');

            document.getElementById('avatar-initiales').textContent = initiales.toUpperCase();
            document.getElementById('topbar-nom-utilisateur').textContent = prenomUtilisateur;
            document.getElementById('topbar-role-utilisateur').textContent = 'Administrateur';

        }
        // Date du jour affichée dans la topbar
        document.getElementById('date-du-jour').textContent =
            new Date().toLocaleDateString('fr-FR', { day: '2-digit', month: 'long', year: 'numeric' });


        const modalVehicule = new bootstrap.Modal(document.getElementById('modal-vehicule'));
        const modalStatut = new bootstrap.Modal(document.getElementById('modal-statut'));
        let pageActuelle = 1;

        function libelleStatut(s) {
            return { disponible: 'Disponible', en_location: 'En location', maintenance: 'En maintenance', hors_service: 'Hors service' }[s] || s;
        }
        function libelleCarburant(c) {
            const icones = { essence: 'fa-gas-pump', diesel: 'fa-gas-pump', hybride: 'fa-leaf', electrique: 'fa-bolt' };
            const noms = { essence: 'Essence', diesel: 'Diesel', hybride: 'Hybride', electrique: 'Électrique' };
            return `<i class="fa-solid ${icones[c] || 'fa-gas-pump'}"></i> ${noms[c] || c}`;
        }
        function formaterDate(d) {
            const dt = new Date(d);
            return dt.toLocaleDateString('fr-FR') + ' ' + dt.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
        }

        function chargerCategories() {
            $.getJSON('api/categories_liste.php', function (reponse) {
                const $filtreCat = $('#filtre-categorie');
                const $formCat = $('#vehicule-categorie');
                reponse.data.forEach(c => {
                    $filtreCat.append(`<option value="${c.id_categorie}">${c.nom}</option>`);
                    $formCat.append(`<option value="${c.id_categorie}">${c.nom}</option>`);
                });
            });
        }

        function chargerStats() {
            $.getJSON('api/repartition_statuts.php', function (reponse) {
                const v = reponse.vehicules.repartition;
                const total = reponse.vehicules.total || 1;
                $('#stat-total').text(reponse.vehicules.total);
                $('#stat-disponibles').text(v.disponibles);
                $('#stat-maintenance').text(v.en_maintenance);
                $('#stat-hors-service').text(v.hors_service);
                $('#pct-disponibles').html(`<span class="contexte">${Math.round(v.disponibles/total*100)}% du parc</span>`);
                $('#pct-maintenance').html(`<span class="contexte">${Math.round(v.en_maintenance/total*100)}% du parc</span>`);
                $('#pct-hors-service').html(`<span class="contexte">${Math.round(v.hors_service/total*100)}% du parc</span>`);
            });
        }

        function chargerListe(page = 1) {
            pageActuelle = page;
            const params = {
                recherche: $('#filtre-recherche').val(),
                id_categorie: $('#filtre-categorie').val(),
                statut: $('#filtre-statut').val(),
                carburant: $('#filtre-carburant').val(),
                tri: $('#filtre-tri').val(),
                page: page,
            };

            $.getJSON('api/vehicules_liste.php', params, function (reponse) {
                $('#titre-liste').text(`Liste des véhicules (${reponse.total})`);
                const $corps = $('#corps-table-vehicules').empty();

                if (reponse.data.length === 0) {
                    $corps.append('<tr><td colspan="10" class="text-center text-muted py-4">Aucun véhicule trouvé.</td></tr>');
                } else {
                    reponse.data.forEach(v => {
                        const actions = estAdmin ? `
                            <button class="btn btn-sm btn-outline-secondary btn-editer" data-id="${v.id_vehicule}" title="Modifier"><i class="fa-solid fa-pen"></i></button>
                            <div class="dropdown d-inline-block">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown"><i class="fa-solid fa-ellipsis-vertical"></i></button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item btn-changer-statut" href="#" data-id="${v.id_vehicule}">Changer le statut</a></li>
                                    <li><a class="dropdown-item text-danger btn-archiver" href="#" data-id="${v.id_vehicule}">Archiver</a></li>
                                </ul>
                            </div>` : '<span class="text-muted">-</span>';

                        $corps.append(`
                            <tr>
                                <td><div class="icone-vehicule-mini"><i class="fa-solid fa-car-side"></i></div></td>
                                <td><strong>${v.immatriculation}</strong></td>
                                <td>${v.marque} ${v.modele}</td>
                                <td><span class="badge-cat">${v.categorie_nom}</span></td>
                                <td>${libelleCarburant(v.carburant)}</td>
                                <td>${v.annee}</td>
                                <td>${Number(v.kilometrage).toLocaleString('fr-FR')} km</td>
                                <td><span class="badge-statut-vehicule ${v.statut_actuel}">${libelleStatut(v.statut_actuel)}</span></td>
                                <td>${formaterDate(v.derniere_maj)}</td>
                                <td>${actions}</td>
                            </tr>
                        `);
                    });
                }

                const debut = (reponse.page - 1) * reponse.par_page + 1;
                const fin = Math.min(reponse.page * reponse.par_page, reponse.total);
                $('#info-pagination').text(reponse.total > 0 ? `Affichage de ${debut} à ${fin} sur ${reponse.total} véhicules` : '');

                const $pagination = $('#zone-pagination').empty();
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
            }).fail(() => toastr.error("Impossible de charger la liste des véhicules.", 'Erreur'));
        }

        $('#zone-pagination').on('click', '.page-btn:not(.desactive)', function () {
            const p = $(this).data('page');
            if (p) chargerListe(p);
        });

        let timeoutRecherche;
        $('#filtre-recherche').on('input', function () {
            clearTimeout(timeoutRecherche);
            timeoutRecherche = setTimeout(() => chargerListe(1), 350);
        });
        $('#filtre-categorie, #filtre-statut, #filtre-carburant, #filtre-tri').on('change', () => chargerListe(1));
        $('#btn-reinitialiser').on('click', function (e) {
            e.preventDefault();
            $('#filtre-recherche').val('');
            $('#filtre-categorie, #filtre-statut, #filtre-carburant').val('');
            $('#filtre-tri').val('recents');
            chargerListe(1);
        });

        $('#btn-ajouter-vehicule').on('click', function () {
            $('#form-vehicule')[0].reset();
            $('#vehicule-id').val('');
            $('#vehicule-immatriculation').prop('readonly', false);
            $('#titre-modal-vehicule').text('Ajouter un véhicule');
            modalVehicule.show();
        });

        $('#corps-table-vehicules').on('click', '.btn-editer', function () {
            const id = $(this).data('id');
            $.getJSON('api/vehicule_detail.php', { id }, function (reponse) {
                const v = reponse.data;
                $('#vehicule-id').val(v.id_vehicule);
                $('#vehicule-immatriculation').val(v.immatriculation).prop('readonly', true);
                $('#vehicule-categorie').val(v.id_categorie);
                $('#vehicule-marque').val(v.marque);
                $('#vehicule-modele').val(v.modele);
                $('#vehicule-annee').val(v.annee);
                $('#vehicule-carburant').val(v.carburant);
                $('#vehicule-kilometrage').val(v.kilometrage);
                $('#titre-modal-vehicule').text('Modifier le véhicule');
                modalVehicule.show();
            }).fail(() => toastr.error("Impossible de charger ce véhicule.", 'Erreur'));
        });

        $('#form-vehicule').on('submit', function (e) {
            e.preventDefault();
            const id = $('#vehicule-id').val();
            const donnees = {
                immatriculation: $('#vehicule-immatriculation').val(),
                id_categorie: $('#vehicule-categorie').val(),
                marque: $('#vehicule-marque').val(),
                modele: $('#vehicule-modele').val(),
                annee: $('#vehicule-annee').val(),
                carburant: $('#vehicule-carburant').val(),
                kilometrage: $('#vehicule-kilometrage').val(),
            };
            const url = id ? `api/vehicule_modifier.php?id=${id}` : 'api/vehicule_creer.php';
            const methode = id ? 'PUT' : 'POST';

            $.ajax({
                url, method: methode, contentType: 'application/json', data: JSON.stringify(donnees),
                success: function (reponse) {
                    modalVehicule.hide();
                    toastr.success(reponse.message, 'Succès');
                    chargerListe(pageActuelle);
                    chargerStats();
                },
                error: function (xhr) {
                    const r = xhr.responseJSON || {};
                    if (xhr.status !== 401) toastr.error(r.message || "Erreur.", 'Erreur');
                }
            });
        });

        $('#corps-table-vehicules').on('click', '.btn-changer-statut', function (e) {
            e.preventDefault();
            $('#statut-id-vehicule').val($(this).data('id'));
            $('#statut-raison').val('');
            modalStatut.show();
        });
        $('#form-statut').on('submit', function (e) {
            e.preventDefault();
            $.ajax({
                url: 'api/vehicule_changer_statut.php', method: 'PUT', contentType: 'application/json',
                data: JSON.stringify({
                    id_vehicule: $('#statut-id-vehicule').val(),
                    nouveau_statut: $('#statut-nouveau').val(),
                    raison: $('#statut-raison').val(),
                }),
                success: function (reponse) {
                    modalStatut.hide();
                    toastr.success(reponse.message, 'Statut mis à jour');
                    chargerListe(pageActuelle);
                    chargerStats();
                },
                error: function (xhr) {
                    const r = xhr.responseJSON || {};
                    if (xhr.status !== 401) toastr.error(r.message || "Erreur.", 'Erreur');
                }
            });
        });

        $('#corps-table-vehicules').on('click', '.btn-archiver', function (e) {
            e.preventDefault();
            const id = $(this).data('id');
            if (!window.confirm('Archiver ce véhicule ? Il ne sera plus proposé à la location, mais son historique est conservé.')) return;

            $.ajax({
                url: `api/vehicule_supprimer.php?id=${id}`, method: 'DELETE',
                success: function (reponse) {
                    toastr.success(reponse.message, 'Archivé');
                    chargerListe(pageActuelle);
                    chargerStats();
                },
                error: function (xhr) {
                    const r = xhr.responseJSON || {};
                    if (xhr.status !== 401) toastr.error(r.message || "Erreur.", 'Erreur');
                }
            });
        });

        chargerCategories();
        chargerStats();
        chargerListe(1);
    </script>
</body>
</html>
