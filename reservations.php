<?php $pageActive = 'reservations'; $roleConnecte = 'admin'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>AutoRent - Réservations</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="assets/toastr/toastr.min.css">
    <link rel="stylesheet" href="style/style.css">
    <style>
        .table-reservations th { text-transform:uppercase; font-size:11px; color:#9ca3af; font-weight:600; border-bottom:1px solid #f0f1f4; padding:10px 8px; white-space:nowrap; }
        .table-reservations td { padding:12px 8px; border-bottom:1px solid #f6f7f9; font-size:13.5px; vertical-align:middle; }
        .ref-reservation { color:#2563eb; font-weight:600; }
        .badge-statut-resa { font-size: 11px; font-weight: 600; padding: 3px 10px; border-radius: 999px; }
        .badge-statut-resa.reservee { background:#e9f7ef; color:#16a34a; }
        .badge-statut-resa.en_cours { background:#e8f0fe; color:#2563eb; }
        .badge-statut-resa.terminee { background:#f3f4f6; color:#6b7280; }
        .badge-statut-resa.annulee { background:#fdeaea; color:#dc2626; }
        .pagination-perso .page-btn { width:32px; height:32px; border:1px solid #e5e7eb; border-radius:6px; background:#fff; display:flex; align-items:center; justify-content:center; font-size:13px; cursor:pointer; }
        .pagination-perso .page-btn.actif { background:#6366f1; color:#fff; border-color:#6366f1; }
        .pagination-perso .page-btn.desactive { opacity:0.4; pointer-events:none; }
        .zone-suggestions { position:relative; }
        .liste-suggestions { position:absolute; top:100%; left:0; right:0; background:#fff; border:1px solid #e5e7eb; border-radius:8px; box-shadow:0 4px 14px rgba(0,0,0,0.08); z-index:50; max-height:200px; overflow-y:auto; display:none; }
        .liste-suggestions .suggestion { padding:8px 12px; font-size:13px; cursor:pointer; }
        .liste-suggestions .suggestion:hover { background:#f3f4f6; }
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
            <h1>Réservations</h1>
            <p class="sous-titre">Gérez toutes les réservations de véhicules</p>
        </div>

        <div class="row row-cols-1 row-cols-md-4 g-3 mb-4">
            <div class="col"><div class="carte-stat">
                <div class="icone-stat" style="background:#e8f0fe; color:#2563eb;"><i class="fa-solid fa-calendar-days"></i></div>
                <div class="valeur-stat" id="stat-total">-</div>
                <div class="libelle-stat">Réservations totales</div>
                <div class="tendance" id="tendance-total"></div>
            </div></div>
            <div class="col"><div class="carte-stat">
                <div class="icone-stat" style="background:#e8f0fe; color:#2563eb;"><i class="fa-solid fa-clock"></i></div>
                <div class="valeur-stat" id="stat-en-cours">-</div>
                <div class="libelle-stat">En cours</div>
                <div class="tendance" id="pct-en-cours"></div>
            </div></div>
            <div class="col"><div class="carte-stat">
                <div class="icone-stat" style="background:#e9f7ef; color:#16a34a;"><i class="fa-solid fa-circle-check"></i></div>
                <div class="valeur-stat" id="stat-terminee">-</div>
                <div class="libelle-stat">Terminées</div>
                <div class="tendance" id="pct-terminee"></div>
            </div></div>
            <div class="col"><div class="carte-stat">
                <div class="icone-stat" style="background:#fdeaea; color:#dc2626;"><i class="fa-solid fa-circle-xmark"></i></div>
                <div class="valeur-stat" id="stat-annulee">-</div>
                <div class="libelle-stat">Annulées</div>
                <div class="tendance" id="pct-annulee"></div>
            </div></div>
        </div>

        <div class="panneau mb-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0">Filtres de recherche</h6>
                <button class="btn btn-primary" id="btn-nouvelle-reservation" style="background:#6366f1; border:none;">
                    <i class="fa-solid fa-plus"></i> Nouvelle réservation
                </button>
            </div>
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label" style="font-size:12.5px; font-weight:600;">Recherche</label>
                    <input type="text" id="filtre-recherche" class="form-control" placeholder="Référence, client ou véhicule...">
                </div>
                <div class="col-md-2">
                    <label class="form-label" style="font-size:12.5px; font-weight:600;">Statut</label>
                    <select id="filtre-statut" class="form-select">
                        <option value="">Tous les statuts</option>
                        <option value="reservee">À venir</option>
                        <option value="en_cours">En cours</option>
                        <option value="terminee">Terminée</option>
                        <option value="annulee">Annulée</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label" style="font-size:12.5px; font-weight:600;">Période de début</label>
                    <input type="date" id="filtre-periode-debut" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label" style="font-size:12.5px; font-weight:600;">Période de fin</label>
                    <input type="date" id="filtre-periode-fin" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label" style="font-size:12.5px; font-weight:600;">Catégorie</label>
                    <select id="filtre-categorie" class="form-select"><option value="">Toutes les catégories</option></select>
                </div>
            </div>
            <div class="d-flex justify-content-between mt-3">
                <a href="#" id="btn-reinitialiser" class="lien-voir-tout">Réinitialiser</a>
                <select id="filtre-tri" class="form-select" style="width:auto; font-size:13px;">
                    <option value="creation_desc">Trier par : Date de création</option>
                    <option value="debut_desc">Date de début (récent)</option>
                    <option value="debut_asc">Date de début (ancien)</option>
                    <option value="montant_desc">Montant (décroissant)</option>
                    <option value="montant_asc">Montant (croissant)</option>
                </select>
            </div>
        </div>

        <div class="panneau">
            <div class="entete-panneau">
                <h6 id="titre-liste">Liste des réservations</h6>
            </div>
            <div class="table-responsive">
                <table class="table-reservations w-100">
                    <thead>
                        <tr>
                            <th>Référence</th><th>Client</th><th>Véhicule</th><th>Période de location</th>
                            <th>Montant total</th><th>Statut</th><th>Créé le</th><th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="corps-table-reservations">
                        <tr><td colspan="8" class="text-center text-muted py-4">Chargement...</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-3">
                <small class="text-muted" id="info-pagination"></small>
                <div class="d-flex gap-1 pagination-perso" id="zone-pagination"></div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-reservation" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="form-reservation">
                    <div class="modal-header">
                        <h5 class="modal-title" id="titre-modal-reservation">Nouvelle réservation</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="reservation-id">
                        <input type="hidden" id="reservation-id-client">
                        <input type="hidden" id="reservation-id-vehicule">
                        <input type="hidden" id="reservation-tarif-jour">

                        <div class="row g-3">
                            <div class="col-6">
                                <label class="form-label">Date de début *</label>
                                <input type="datetime-local" class="form-control" id="reservation-date-debut" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Date de fin *</label>
                                <input type="datetime-local" class="form-control" id="reservation-date-fin" required>
                            </div>

                            <div class="col-6 zone-suggestions">
                                <label class="form-label">Client *</label>
                                <input type="text" class="form-control" id="reservation-recherche-client" placeholder="Nom, prénom ou téléphone..." autocomplete="off" required>
                                <div class="liste-suggestions" id="suggestions-client"></div>
                            </div>
                            <div class="col-6 zone-suggestions">
                                <label class="form-label">Véhicule *</label>
                                <input type="text" class="form-control" id="reservation-recherche-vehicule" placeholder="Sélectionnez d'abord les dates..." autocomplete="off" required disabled>
                                <div class="liste-suggestions" id="suggestions-vehicule"></div>
                            </div>

                            <div class="col-6">
                                <label class="form-label">Montant total (FCFA) *</label>
                                <input type="number" class="form-control" id="reservation-montant" min="0" required>
                                <small class="text-muted">Calculé automatiquement, modifiable si besoin.</small>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Statut *</label>
                                <select class="form-select" id="reservation-statut" required>
                                    <option value="reservee">À venir</option>
                                    <option value="en_cours">En cours</option>
                                    <option value="terminee">Terminée</option>
                                    <option value="annulee">Annulée</option>
                                </select>
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
            

        const modalReservation = new bootstrap.Modal(document.getElementById('modal-reservation'));
        let pageActuelle = 1;

        function formaterDate(d) {
            const dt = new Date(d);
            return dt.toLocaleDateString('fr-FR') + ' ' + dt.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
        }
        function formaterMontant(m) { return Number(m).toLocaleString('fr-FR') + ' F CFA'; }
        function versDatetimeLocal(d) {
            const dt = new Date(d);
            const pad = n => String(n).padStart(2, '0');
            return `${dt.getFullYear()}-${pad(dt.getMonth()+1)}-${pad(dt.getDate())}T${pad(dt.getHours())}:${pad(dt.getMinutes())}`;
        }

        function chargerCategories() {
            $.getJSON('api/categories_liste.php', function (r) {
                r.data.forEach(c => $('#filtre-categorie').append(`<option value="${c.id_categorie}">${c.nom}</option>`));
            });
        }

        function chargerStats() {
            $.getJSON('api/reservations_stats.php', function (r) {
                $('#stat-total').text(r.total);
                $('#stat-en-cours').text(r.en_cours);
                $('#stat-terminee').text(r.terminee);
                $('#stat-annulee').text(r.annulee);
                const total = r.total || 1;
                $('#pct-en-cours').html(`<span class="contexte">${Math.round(r.en_cours/total*100)}% du total</span>`);
                $('#pct-terminee').html(`<span class="contexte">${Math.round(r.terminee/total*100)}% du total</span>`);
                $('#pct-annulee').html(`<span class="contexte">${Math.round(r.annulee/total*100)}% du total</span>`);
                if (r.variation_mensuelle !== null) {
                    const hausse = r.variation_mensuelle >= 0;
                    $('#tendance-total').html(`<i class="fa-solid fa-arrow-${hausse?'up':'down'}"></i> ${Math.abs(r.variation_mensuelle)}% <span class="contexte">vs mois dernier</span>`).attr('class', 'tendance ' + (hausse?'hausse':'baisse'));
                }
            });
        }

        function chargerListe(page = 1) {
            pageActuelle = page;
            const params = {
                recherche: $('#filtre-recherche').val(),
                statut: $('#filtre-statut').val(),
                periode_debut: $('#filtre-periode-debut').val(),
                periode_fin: $('#filtre-periode-fin').val(),
                id_categorie: $('#filtre-categorie').val(),
                tri: $('#filtre-tri').val(),
                page,
            };
            $.getJSON('api/reservations_liste.php', params, function (r) {
                $('#titre-liste').text(`Liste des réservations (${r.total})`);
                const $corps = $('#corps-table-reservations').empty();
                if (r.data.length === 0) {
                    $corps.append('<tr><td colspan="8" class="text-center text-muted py-4">Aucune réservation trouvée.</td></tr>');
                } else {
                    r.data.forEach(res => {
                        const peutModifier = res.statut !== 'terminee';
                        const peutAnnuler = res.statut !== 'terminee' && res.statut !== 'annulee';
                        $corps.append(`
                            <tr>
                                <td class="ref-reservation">${res.reference}</td>
                                <td>${res.client}<br><small class="text-muted">${res.client_tel}</small></td>
                                <td>${res.vehicule}<br><small class="text-muted">${res.vehicule_immat}</small></td>
                                <td>${formaterDate(res.date_debut)}<br>→ ${formaterDate(res.date_fin)}</td>
                                <td>${formaterMontant(res.montant_total)}</td>
                                <td><span class="badge-statut-resa ${res.statut}">${res.statut_libelle}</span></td>
                                <td>${formaterDate(res.date_reservation)}</td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown"><i class="fa-solid fa-ellipsis"></i></button>
                                        <ul class="dropdown-menu">
                                            ${peutModifier ? `<li><a class="dropdown-item btn-editer-resa" href="#" data-id="${res.id_reservation}">Modifier</a></li>` : ''}
                                            ${peutAnnuler ? `<li><a class="dropdown-item btn-annuler-resa" href="#" data-id="${res.id_reservation}">Annuler</a></li>` : ''}
                                            ${estAdmin ? `<li><a class="dropdown-item text-danger btn-supprimer-resa" href="#" data-id="${res.id_reservation}">Supprimer</a></li>` : ''}
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        `);
                    });
                }
                const debut = (r.page - 1) * r.par_page + 1;
                const fin = Math.min(r.page * r.par_page, r.total);
                $('#info-pagination').text(r.total > 0 ? `Affichage de ${debut} à ${fin} sur ${r.total} réservations` : '');

                const $pag = $('#zone-pagination').empty();
                const derniere = r.total_pages || 1;
                $pag.append(`<div class="page-btn ${r.page<=1?'desactive':''}" data-page="${r.page-1}"><i class="fa-solid fa-chevron-left"></i></div>`);
                for (let p = 1; p <= derniere; p++) {
                    if (p === 1 || p === derniere || Math.abs(p - r.page) <= 1) {
                        $pag.append(`<div class="page-btn ${p===r.page?'actif':''}" data-page="${p}">${p}</div>`);
                    } else if (Math.abs(p - r.page) === 2) {
                        $pag.append(`<div class="page-btn desactive">…</div>`);
                    }
                }
                $pag.append(`<div class="page-btn ${r.page>=derniere?'desactive':''}" data-page="${r.page+1}"><i class="fa-solid fa-chevron-right"></i></div>`);
            }).fail(() => toastr.error("Impossible de charger les réservations.", 'Erreur'));
        }

        $('#zone-pagination').on('click', '.page-btn:not(.desactive)', function () {
            const p = $(this).data('page'); if (p) chargerListe(p);
        });

        let timeoutRecherche;
        $('#filtre-recherche').on('input', function () {
            clearTimeout(timeoutRecherche);
            timeoutRecherche = setTimeout(() => chargerListe(1), 350);
        });
        $('#filtre-statut, #filtre-periode-debut, #filtre-periode-fin, #filtre-categorie, #filtre-tri').on('change', () => chargerListe(1));
        $('#btn-reinitialiser').on('click', function (e) {
            e.preventDefault();
            $('#filtre-recherche').val(''); $('#filtre-statut, #filtre-categorie').val('');
            $('#filtre-periode-debut, #filtre-periode-fin').val(''); $('#filtre-tri').val('creation_desc');
            chargerListe(1);
        });

        let timeoutClient;
        $('#reservation-recherche-client').on('input', function () {
            const terme = $(this).val();
            $('#reservation-id-client').val('');
            clearTimeout(timeoutClient);
            if (terme.length < 2) { $('#suggestions-client').hide(); return; }
            timeoutClient = setTimeout(() => {
                $.getJSON('api/clients_recherche.php', { recherche: terme }, function (r) {
                    const $liste = $('#suggestions-client').empty();
                    if (r.data.length === 0) { $liste.hide(); return; }
                    r.data.forEach(c => $liste.append(`<div class="suggestion" data-id="${c.id}" data-label="${c.label}">${c.label}</div>`));
                    $liste.show();
                });
            }, 300);
        });
        $('#suggestions-client').on('click', '.suggestion', function () {
            $('#reservation-id-client').val($(this).data('id'));
            $('#reservation-recherche-client').val($(this).data('label'));
            $('#suggestions-client').hide();
        });

        function verifierActivationVehicule() {
            const debut = $('#reservation-date-debut').val();
            const fin = $('#reservation-date-fin').val();
            const $champ = $('#reservation-recherche-vehicule');
            if (debut && fin) {
                $champ.prop('disabled', false).attr('placeholder', 'Tapez une marque, un modèle...');
            } else {
                $champ.prop('disabled', true).val('').attr('placeholder', "Sélectionnez d'abord les dates...");
                $('#reservation-id-vehicule, #reservation-tarif-jour').val('');
            }
        }
        $('#reservation-date-debut, #reservation-date-fin').on('change', verifierActivationVehicule);

        let timeoutVehicule;
        $('#reservation-recherche-vehicule').on('input', function () {
            const terme = $(this).val();
            $('#reservation-id-vehicule, #reservation-tarif-jour').val('');
            clearTimeout(timeoutVehicule);
            if (terme.length < 1) { $('#suggestions-vehicule').hide(); return; }
            timeoutVehicule = setTimeout(() => {
                const params = {
                    date_debut: $('#reservation-date-debut').val(),
                    date_fin: $('#reservation-date-fin').val(),
                    recherche: terme,
                };
                const idActuelle = $('#reservation-id').val();
                if (idActuelle) params.exclure_reservation = idActuelle;

                $.getJSON('api/vehicules_disponibles_periode.php', params, function (r) {
                    const $liste = $('#suggestions-vehicule').empty();
                    if (r.data.length === 0) {
                        $liste.append('<div class="suggestion text-muted">Aucun véhicule disponible sur cette période</div>').show();
                        return;
                    }
                    r.data.forEach(v => $liste.append(`<div class="suggestion" data-id="${v.id}" data-label="${v.label}" data-tarif="${v.tarif_jour}">${v.label}</div>`));
                    $liste.show();
                });
            }, 300);
        });
        $('#suggestions-vehicule').on('click', '.suggestion[data-id]', function () {
            $('#reservation-id-vehicule').val($(this).data('id'));
            $('#reservation-recherche-vehicule').val($(this).data('label'));
            $('#reservation-tarif-jour').val($(this).data('tarif'));
            $('#suggestions-vehicule').hide();
            recalculerMontant();
        });

        function recalculerMontant() {
            const debut = $('#reservation-date-debut').val();
            const fin = $('#reservation-date-fin').val();
            const tarif = parseFloat($('#reservation-tarif-jour').val());
            if (!debut || !fin || !tarif) return;
            const jours = Math.max(1, Math.ceil((new Date(fin) - new Date(debut)) / (1000 * 3600 * 24)));
            $('#reservation-montant').val(Math.round(jours * tarif));
        }
        $('#reservation-date-debut, #reservation-date-fin').on('change', recalculerMontant);

        $(document).on('click', function (e) {
            if (!$(e.target).closest('.zone-suggestions').length) {
                $('.liste-suggestions').hide();
            }
        });

        $('#btn-nouvelle-reservation').on('click', function () {
            $('#form-reservation')[0].reset();
            $('#reservation-id, #reservation-id-client, #reservation-id-vehicule, #reservation-tarif-jour').val('');
            $('#reservation-statut').val('reservee');
            verifierActivationVehicule();
            $('#titre-modal-reservation').text('Nouvelle réservation');
            modalReservation.show();
        });

        $('#corps-table-reservations').on('click', '.btn-editer-resa', function (e) {
            e.preventDefault();
            const id = $(this).data('id');
            $.getJSON('api/reservation_detail.php', { id }, function (r) {
                const v = r.data;
                $('#reservation-id').val(v.id_reservation);
                $('#reservation-id-client').val(v.id_client);
                $('#reservation-recherche-client').val(v.client_prenom + ' ' + v.client_nom + ' - ' + v.client_telephone);
                $('#reservation-id-vehicule').val(v.id_vehicule);
                $('#reservation-recherche-vehicule').val(v.marque + ' ' + v.modele + ' (' + v.immatriculation + ')').prop('disabled', false);
                $('#reservation-date-debut').val(versDatetimeLocal(v.date_debut));
                $('#reservation-date-fin').val(versDatetimeLocal(v.date_fin));
                $('#reservation-montant').val(v.montant_total);
                $('#reservation-statut').val(v.statut);
                $('#titre-modal-reservation').text('Modifier la réservation');
                modalReservation.show();
            }).fail(() => toastr.error("Impossible de charger cette réservation.", 'Erreur'));
        });

        $('#form-reservation').on('submit', function (e) {
            e.preventDefault();
            if (!$('#reservation-id-client').val() || !$('#reservation-id-vehicule').val()) {
                toastr.warning('Veuillez sélectionner un client et un véhicule dans les suggestions proposées.', 'Champs incomplets');
                return;
            }
            const id = $('#reservation-id').val();
            const donnees = {
                id_client: $('#reservation-id-client').val(),
                id_vehicule: $('#reservation-id-vehicule').val(),
                date_debut: $('#reservation-date-debut').val(),
                date_fin: $('#reservation-date-fin').val(),
                montant_total: $('#reservation-montant').val(),
                statut: $('#reservation-statut').val(),
            };
            const url = id ? `api/reservation_modifier.php?id=${id}` : 'api/reservation_creer.php';
            $.ajax({
                url, method: id ? 'PUT' : 'POST', contentType: 'application/json', data: JSON.stringify(donnees),
                success: function (r) {
                    modalReservation.hide();
                    toastr.success(r.message, 'Succès');
                    chargerListe(pageActuelle); chargerStats();
                },
                error: function (xhr) {
                    const r = xhr.responseJSON || {};
                    if (xhr.status !== 401) toastr.error(r.message || "Erreur.", 'Erreur');
                }
            });
        });

        $('#corps-table-reservations').on('click', '.btn-annuler-resa', function (e) {
            e.preventDefault();
            const id = $(this).data('id');
            if (!window.confirm('Annuler cette réservation ?')) return;
            $.ajax({
                url: `api/reservation_annuler.php?id=${id}`, method: 'PUT',
                success: function (r) { toastr.success(r.message, 'Annulée'); chargerListe(pageActuelle); chargerStats(); },
                error: function (xhr) { const r = xhr.responseJSON || {}; if (xhr.status !== 401) toastr.error(r.message || "Erreur.", 'Erreur'); }
            });
        });

        $('#corps-table-reservations').on('click', '.btn-supprimer-resa', function (e) {
            e.preventDefault();
            const id = $(this).data('id');
            if (!window.confirm('Supprimer définitivement cette réservation ? Cette action est irréversible.')) return;
            $.ajax({
                url: `api/reservation_supprimer.php?id=${id}`, method: 'DELETE',
                success: function (r) { toastr.success(r.message, 'Supprimée'); chargerListe(pageActuelle); chargerStats(); },
                error: function (xhr) { const r = xhr.responseJSON || {}; if (xhr.status !== 401) toastr.error(r.message || "Erreur.", 'Erreur'); }
            });
        });

        chargerCategories();
        chargerStats();
        chargerListe(1);
    </script>
</body>
</html>
