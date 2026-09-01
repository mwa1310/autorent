<?php $pageActive = 'paiements'; $roleConnecte = 'admin'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>AutoRent - Paiements</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="assets/toastr/toastr.min.css">
    <link rel="stylesheet" href="style/style.css">
    <style>
        .table-paiements th { text-transform:uppercase; font-size:11px; color:#9ca3af; font-weight:600; border-bottom:1px solid #f0f1f4; padding:10px 8px; white-space:nowrap; }
        .table-paiements td { padding:12px 8px; border-bottom:1px solid #f6f7f9; font-size:13.5px; vertical-align:middle; }
        .ref-paiement { color:#2563eb; font-weight:600; }
        .badge-statut-pay { font-size: 11px; font-weight: 600; padding: 3px 10px; border-radius: 999px; }
        .badge-statut-pay.valide { background:#e9f7ef; color:#16a34a; }
        .badge-statut-pay.en_attente { background:#fef3e2; color:#d97706; }
        .badge-statut-pay.rembourse { background:#fdeaea; color:#dc2626; }
        .icone-mode { margin-right:4px; }
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
            <h1>Paiements</h1>
            <p class="sous-titre">Suivez et gérez tous les paiements effectués</p>
        </div>

        <div class="row row-cols-1 row-cols-md-4 g-3 mb-4">
            <div class="col"><div class="carte-stat">
                <div class="icone-stat" style="background:#e8f0fe; color:#2563eb;"><i class="fa-solid fa-wallet"></i></div>
                <div class="valeur-stat" id="stat-total">-</div>
                <div class="libelle-stat">Paiements totaux</div>
                <div class="tendance" id="tendance-total"></div>
            </div></div>
            <div class="col"><div class="carte-stat">
                <div class="icone-stat" style="background:#e9f7ef; color:#16a34a;"><i class="fa-solid fa-euro-sign"></i></div>
                <div class="valeur-stat" id="stat-recu">-</div>
                <div class="libelle-stat">Montant total reçu</div>
                <div class="tendance" id="tendance-recu"></div>
            </div></div>
            <div class="col"><div class="carte-stat">
                <div class="icone-stat" style="background:#f1eafd; color:#7c3aed;"><i class="fa-solid fa-building-columns"></i></div>
                <div class="valeur-stat" id="stat-attente">-</div>
                <div class="libelle-stat">En attente</div>
                <div class="tendance" id="tendance-attente"></div>
            </div></div>
            <div class="col"><div class="carte-stat">
                <div class="icone-stat" style="background:#fdeaea; color:#dc2626;"><i class="fa-solid fa-rotate-left"></i></div>
                <div class="valeur-stat" id="stat-rembourse">-</div>
                <div class="libelle-stat">Remboursés</div>
                <div class="tendance" id="tendance-rembourse"></div>
            </div></div>
        </div>

        <div class="panneau mb-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0">Filtres de recherche</h6>
                <button class="btn btn-primary" id="btn-nouveau-paiement" style="background:#6366f1; border:none;">
                    <i class="fa-solid fa-plus"></i> Nouveau paiement
                </button>
            </div>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label" style="font-size:12.5px; font-weight:600;">Recherche</label>
                    <input type="text" id="filtre-recherche" class="form-control" placeholder="Référence, client, réservation...">
                </div>
                <div class="col-md-2">
                    <label class="form-label" style="font-size:12.5px; font-weight:600;">Statut</label>
                    <select id="filtre-statut" class="form-select">
                        <option value="">Tous les statuts</option>
                        <option value="valide">Payé</option>
                        <option value="en_attente">En attente</option>
                        <option value="rembourse">Remboursé</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label" style="font-size:12.5px; font-weight:600;">Méthode</label>
                    <select id="filtre-mode" class="form-select">
                        <option value="">Toutes les méthodes</option>
                        <option value="especes">Espèces</option>
                        <option value="virement">Virement bancaire</option>
                        <option value="mobile_money">Mobile Money</option>
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
            </div>
            <div class="d-flex justify-content-between mt-3">
                <a href="#" id="btn-reinitialiser" class="lien-voir-tout">Réinitialiser</a>
                <select id="filtre-tri" class="form-select" style="width:auto; font-size:13px;">
                    <option value="date_desc">Trier par : Date de paiement</option>
                    <option value="date_asc">Date de paiement (ancien)</option>
                    <option value="montant_desc">Montant (décroissant)</option>
                    <option value="montant_asc">Montant (croissant)</option>
                </select>
            </div>
        </div>

        <div class="panneau">
            <div class="entete-panneau"><h6 id="titre-liste">Liste des paiements</h6></div>
            <div class="table-responsive">
                <table class="table-paiements w-100">
                    <thead>
                        <tr>
                            <th>Référence paiement</th><th>Réservation</th><th>Client</th><th>Montant</th>
                            <th>Méthode</th><th>Statut</th><th>Date de paiement</th><th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="corps-table-paiements">
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

    <div class="modal fade" id="modal-paiement" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="form-paiement">
                    <div class="modal-header">
                        <h5 class="modal-title" id="titre-modal-paiement">Nouveau paiement</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="paiement-id">
                        <input type="hidden" id="paiement-id-reservation">

                        <div class="mb-2 zone-suggestions">
                            <label class="form-label">Réservation *</label>
                            <input type="text" class="form-control" id="paiement-recherche-reservation" placeholder="Référence ou nom du client..." autocomplete="off" required>
                            <div class="liste-suggestions" id="suggestions-reservation"></div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Montant (FCFA) *</label>
                            <input type="number" class="form-control" id="paiement-montant" min="0" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Méthode de paiement *</label>
                            <select class="form-select" id="paiement-mode" required>
                                <option value="especes">Espèces</option>
                                <option value="virement">Virement bancaire</option>
                                <option value="mobile_money">Mobile Money</option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Statut *</label>
                            <select class="form-select" id="paiement-statut" required>
                                <option value="valide">Payé</option>
                                <option value="en_attente">En attente</option>
                                <option value="rembourse">Remboursé</option>
                            </select>
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


        const modalPaiement = new bootstrap.Modal(document.getElementById('modal-paiement'));
        let pageActuelle = 1;

        const iconesMode = { especes: 'fa-money-bill-wave', virement: 'fa-building-columns', mobile_money: 'fa-mobile-screen' };

        function formaterDate(d) {
            const dt = new Date(d);
            return dt.toLocaleDateString('fr-FR') + ' ' + dt.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
        }
        function formaterMontant(m) { return Number(m).toLocaleString('fr-FR') + ' F CFA'; }

        function chargerStats() {
            $.getJSON('api/paiements_stats.php', function (r) {
                $('#stat-total').text(r.total_paiements);
                $('#stat-recu').text(formaterMontant(r.montant_recu));
                $('#stat-attente').text(formaterMontant(r.montant_attente));
                $('#stat-rembourse').text(formaterMontant(r.montant_rembourse));

                afficherTendance('#tendance-total', r.variation_total);
                afficherTendance('#tendance-recu', r.variation_recu);
                afficherTendance('#tendance-attente', r.variation_attente);
                afficherTendance('#tendance-rembourse', r.variation_rembourse);
            }).fail(() => toastr.error("Impossible de charger les statistiques.", 'Erreur'));
        }
        function afficherTendance(selecteur, valeur) {
            if (valeur === null) { $(selecteur).html('<span class="contexte">vs mois dernier</span>'); return; }
            const hausse = valeur >= 0;
            $(selecteur).html(`<i class="fa-solid fa-arrow-${hausse?'up':'down'}"></i> ${Math.abs(valeur)}% <span class="contexte">vs mois dernier</span>`)
                .attr('class', 'tendance ' + (hausse ? 'hausse' : 'baisse'));
        }

        function chargerListe(page = 1) {
            pageActuelle = page;
            const params = {
                recherche: $('#filtre-recherche').val(),
                statut: $('#filtre-statut').val(),
                mode_paiement: $('#filtre-mode').val(),
                periode_debut: $('#filtre-periode-debut').val(),
                periode_fin: $('#filtre-periode-fin').val(),
                tri: $('#filtre-tri').val(),
                page,
            };
            $.getJSON('api/paiements_liste.php', params, function (r) {
                $('#titre-liste').text(`Liste des paiements (${r.total})`);
                const $corps = $('#corps-table-paiements').empty();
                if (r.data.length === 0) {
                    $corps.append('<tr><td colspan="8" class="text-center text-muted py-4">Aucun paiement trouvé.</td></tr>');
                } else {
                    r.data.forEach(p => {
                        $corps.append(`
                            <tr>
                                <td class="ref-paiement">${p.reference}</td>
                                <td>${p.reference_reservation}</td>
                                <td>${p.client}<br><small class="text-muted">${p.client_tel}</small></td>
                                <td>${formaterMontant(p.montant)}</td>
                                <td><i class="fa-solid ${iconesMode[p.mode_paiement] || 'fa-circle'} icone-mode"></i>${p.mode_libelle}</td>
                                <td><span class="badge-statut-pay ${p.statut}">${p.statut_libelle}</span></td>
                                <td>${formaterDate(p.date_paiement)}</td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown"><i class="fa-solid fa-ellipsis"></i></button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item btn-editer-pay" href="#" data-id="${p.id_paie}">Modifier</a></li>
                                            ${estAdmin ? `<li><a class="dropdown-item text-danger btn-supprimer-pay" href="#" data-id="${p.id_paie}">Supprimer</a></li>` : ''}
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        `);
                    });
                }
                const debut = (r.page - 1) * r.par_page + 1;
                const fin = Math.min(r.page * r.par_page, r.total);
                $('#info-pagination').text(r.total > 0 ? `Affichage de ${debut} à ${fin} sur ${r.total} paiements` : '');

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
            }).fail(() => toastr.error("Impossible de charger les paiements.", 'Erreur'));
        }

        $('#zone-pagination').on('click', '.page-btn:not(.desactive)', function () {
            const p = $(this).data('page'); if (p) chargerListe(p);
        });

        let timeoutRecherche;
        $('#filtre-recherche').on('input', function () {
            clearTimeout(timeoutRecherche);
            timeoutRecherche = setTimeout(() => chargerListe(1), 350);
        });
        $('#filtre-statut, #filtre-mode, #filtre-periode-debut, #filtre-periode-fin, #filtre-tri').on('change', () => chargerListe(1));
        $('#btn-reinitialiser').on('click', function (e) {
            e.preventDefault();
            $('#filtre-recherche').val(''); $('#filtre-statut, #filtre-mode').val('');
            $('#filtre-periode-debut, #filtre-periode-fin').val(''); $('#filtre-tri').val('date_desc');
            chargerListe(1);
        });

        let timeoutResa;
        $('#paiement-recherche-reservation').on('input', function () {
            const terme = $(this).val();
            $('#paiement-id-reservation').val('');
            clearTimeout(timeoutResa);
            if (terme.length < 1) { $('#suggestions-reservation').hide(); return; }
            timeoutResa = setTimeout(() => {
                $.getJSON('api/reservations_recherche.php', { recherche: terme }, function (r) {
                    const $liste = $('#suggestions-reservation').empty();
                    if (r.data.length === 0) { $liste.hide(); return; }
                    r.data.forEach(res => $liste.append(`<div class="suggestion" data-id="${res.id}" data-label="${res.label}" data-montant="${res.montant_total}">${res.label}</div>`));
                    $liste.show();
                });
            }, 300);
        });
        $('#suggestions-reservation').on('click', '.suggestion', function () {
            $('#paiement-id-reservation').val($(this).data('id'));
            $('#paiement-recherche-reservation').val($(this).data('label'));
            if (!$('#paiement-montant').val()) $('#paiement-montant').val($(this).data('montant'));
            $('#suggestions-reservation').hide();
        });
        $(document).on('click', function (e) {
            if (!$(e.target).closest('.zone-suggestions').length) $('.liste-suggestions').hide();
        });

        $('#btn-nouveau-paiement').on('click', function () {
            $('#form-paiement')[0].reset();
            $('#paiement-id, #paiement-id-reservation').val('');
            $('#paiement-recherche-reservation').prop('disabled', false);
            $('#paiement-statut').val('valide');
            $('#titre-modal-paiement').text('Nouveau paiement');
            modalPaiement.show();
        });

        $('#corps-table-paiements').on('click', '.btn-editer-pay', function (e) {
            e.preventDefault();
            const id = $(this).data('id');
            $.getJSON('api/paiement_detail.php', { id }, function (r) {
                const p = r.data;
                $('#paiement-id').val(p.id_paie);
                $('#paiement-id-reservation').val(p.id_reservation);
                $('#paiement-recherche-reservation').val('RÉS-' + new Date().getFullYear() + '-' + String(p.id_reservation).padStart(4,'0') + ' - ' + p.client_prenom + ' ' + p.client_nom).prop('disabled', true);
                $('#paiement-montant').val(p.montant);
                $('#paiement-mode').val(p.mode_paiement);
                $('#paiement-statut').val(p.statut);
                $('#titre-modal-paiement').text('Modifier le paiement');
                modalPaiement.show();
            }).fail(() => toastr.error("Impossible de charger ce paiement.", 'Erreur'));
        });

        $('#form-paiement').on('submit', function (e) {
            e.preventDefault();
            const id = $('#paiement-id').val();
            if (!id && !$('#paiement-id-reservation').val()) {
                toastr.warning('Veuillez sélectionner une réservation dans les suggestions.', 'Champ manquant');
                return;
            }
            const donnees = {
                id_reservation: $('#paiement-id-reservation').val(),
                montant: $('#paiement-montant').val(),
                mode_paiement: $('#paiement-mode').val(),
                statut: $('#paiement-statut').val(),
            };
            const url = id ? `api/paiement_modifier.php?id=${id}` : 'api/paiement_creer.php';
            $.ajax({
                url, method: id ? 'PUT' : 'POST', contentType: 'application/json', data: JSON.stringify(donnees),
                success: function (r) {
                    modalPaiement.hide();
                    $('#paiement-recherche-reservation').prop('disabled', false);
                    toastr.success(r.message, 'Succès');
                    chargerListe(pageActuelle); chargerStats();
                },
                error: function (xhr) {
                    const r = xhr.responseJSON || {};
                    if (xhr.status !== 401) toastr.error(r.message || "Erreur.", 'Erreur');
                }
            });
        });

        $('#corps-table-paiements').on('click', '.btn-supprimer-pay', function (e) {
            e.preventDefault();
            const id = $(this).data('id');
            if (!window.confirm('Supprimer définitivement ce paiement ?')) return;
            $.ajax({
                url: `api/paiement_supprimer.php?id=${id}`, method: 'DELETE',
                success: function (r) { toastr.success(r.message, 'Supprimé'); chargerListe(pageActuelle); chargerStats(); },
                error: function (xhr) { const r = xhr.responseJSON || {}; if (xhr.status !== 401) toastr.error(r.message || "Erreur.", 'Erreur'); }
            });
        });

        chargerStats();
        chargerListe(1);
    </script>
</body>
</html>
