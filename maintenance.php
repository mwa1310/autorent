<?php $pageActive = 'maintenance'; $roleConnecte = 'admin'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>AutoRent - Maintenance</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="assets/toastr/toastr.min.css">
    <link rel="stylesheet" href="style/style.css">
    <style>
        .table-maint th { text-transform:uppercase; font-size:11px; color:#9ca3af; font-weight:600; border-bottom:1px solid #f0f1f4; padding:10px 8px; white-space:nowrap; }
        .table-maint td { padding:12px 8px; border-bottom:1px solid #f6f7f9; font-size:13.5px; vertical-align:middle; }
        .ref-maint { color:#2563eb; font-weight:600; }
        .badge-statut-m { font-size: 11px; font-weight: 600; padding: 3px 10px; border-radius: 999px; }
        .badge-statut-m.planifiee { background:#fef3e2; color:#d97706; }
        .badge-statut-m.en_cours { background:#e8f0fe; color:#2563eb; }
        .badge-statut-m.terminee { background:#e9f7ef; color:#16a34a; }
        .badge-statut-m.annulee { background:#f3f4f6; color:#6b7280; }
        .badge-statut-m.en_retard { background:#fdeaea; color:#dc2626; }
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

        <div class="d-flex justify-content-between align-items-start mb-3">
            <div class="entete-page"><h1>Maintenance</h1><p class="sous-titre">Suivez et gérez la maintenance de vos véhicules</p></div>
            <button class="btn btn-primary" id="btn-nouvelle-intervention" style="background:#6366f1; border:none;"><i class="fa-solid fa-plus"></i> Nouvelle intervention</button>
        </div>

        <div class="row row-cols-1 row-cols-md-4 g-3 mb-4">
            <div class="col"><div class="carte-stat"><div class="icone-stat" style="background:#e8f0fe; color:#2563eb;"><i class="fa-solid fa-screwdriver-wrench"></i></div><div class="valeur-stat" id="stat-total">-</div><div class="libelle-stat">Interventions totales</div><div class="tendance" id="tendance-total"></div></div></div>
            <div class="col"><div class="carte-stat"><div class="icone-stat" style="background:#fef3e2; color:#d97706;"><i class="fa-solid fa-clock"></i></div><div class="valeur-stat" id="stat-en-cours">-</div><div class="libelle-stat">En cours</div><div class="tendance" id="pct-en-cours"></div></div></div>
            <div class="col"><div class="carte-stat"><div class="icone-stat" style="background:#e9f7ef; color:#16a34a;"><i class="fa-solid fa-circle-check"></i></div><div class="valeur-stat" id="stat-terminees">-</div><div class="libelle-stat">Terminées</div><div class="tendance" id="pct-terminees"></div></div></div>
            <div class="col"><div class="carte-stat"><div class="icone-stat" style="background:#fdeaea; color:#dc2626;"><i class="fa-solid fa-circle-xmark"></i></div><div class="valeur-stat" id="stat-en-retard">-</div><div class="libelle-stat">En retard</div><div class="tendance" id="pct-en-retard"></div></div></div>
        </div>

        <div class="panneau mb-3">
            <div class="row g-3">
                <div class="col-md-4"><label class="form-label" style="font-size:12.5px; font-weight:600;">Recherche</label><input type="text" id="filtre-recherche" class="form-control" placeholder="Référence, véhicule, immatriculation..."></div>
                <div class="col-md-2"><label class="form-label" style="font-size:12.5px; font-weight:600;">Statut</label>
                    <select id="filtre-statut" class="form-select">
                        <option value="">Tous les statuts</option>
                        <option value="planifiee">En attente</option>
                        <option value="en_cours">En cours</option>
                        <option value="terminee">Terminée</option>
                        <option value="en_retard">En retard</option>
                        <option value="annulee">Annulée</option>
                    </select>
                </div>
                <div class="col-md-3"><label class="form-label" style="font-size:12.5px; font-weight:600;">Type d'intervention</label><select id="filtre-type" class="form-select"><option value="">Tous les types</option></select></div>
                <div class="col-md-1"><label class="form-label" style="font-size:12.5px; font-weight:600;">Du</label><input type="date" id="filtre-periode-debut" class="form-control"></div>
                <div class="col-md-2"><label class="form-label" style="font-size:12.5px; font-weight:600;">Au</label><input type="date" id="filtre-periode-fin" class="form-control"></div>
            </div>
            <div class="d-flex justify-content-between mt-3">
                <a href="#" id="btn-reinitialiser" class="lien-voir-tout">Réinitialiser</a>
                <select id="filtre-tri" class="form-select" style="width:auto; font-size:13px;">
                    <option value="date_desc">Trier par : Date d'intervention</option>
                    <option value="date_asc">Date (ancien)</option>
                    <option value="cout_desc">Coût (décroissant)</option>
                    <option value="cout_asc">Coût (croissant)</option>
                </select>
            </div>
        </div>

        <div class="panneau">
            <div class="entete-panneau"><h6 id="titre-liste">Liste des interventions</h6></div>
            <div class="table-responsive">
                <table class="table-maint w-100">
                    <thead><tr><th>Référence</th><th>Véhicule</th><th>Type</th><th>Fournisseur</th><th>Coût</th><th>Statut</th><th>Date prévue</th><th>Prochaine maintenance</th><th>Actions</th></tr></thead>
                    <tbody id="corps-table-maint"><tr><td colspan="9" class="text-center text-muted py-4">Chargement...</td></tr></tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-3">
                <small class="text-muted" id="info-pagination"></small>
                <div class="d-flex gap-1 pagination-perso" id="zone-pagination"></div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-maint" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="form-maint">
                    <div class="modal-header"><h5 class="modal-title" id="titre-modal-maint">Nouvelle intervention</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                    <div class="modal-body">
                        <input type="hidden" id="maint-id">
                        <input type="hidden" id="maint-id-vehicule">
                        <div class="mb-2 zone-suggestions">
                            <label class="form-label">Véhicule *</label>
                            <input type="text" class="form-control" id="maint-recherche-vehicule" placeholder="Marque, modèle, immatriculation..." autocomplete="off" required>
                            <div class="liste-suggestions" id="suggestions-vehicule-maint"></div>
                        </div>
                        <div class="row g-2">
                            <div class="col-6 mb-2"><label class="form-label">Type d'intervention *</label><input type="text" class="form-control" id="maint-type" required></div>
                            <div class="col-6 mb-2"><label class="form-label">Fournisseur / Garage</label><input type="text" class="form-control" id="maint-fournisseur"></div>
                        </div>
                        <div class="row g-2">
                            <div class="col-6 mb-2"><label class="form-label">Date prévue *</label><input type="date" class="form-control" id="maint-date-prevue" required></div>
                            <div class="col-6 mb-2"><label class="form-label">Coût (FCFA)</label><input type="number" class="form-control" id="maint-cout" min="0"></div>
                        </div>
                        <div class="mb-2"><label class="form-label">Statut *</label>
                            <select class="form-select" id="maint-statut" required>
                                <option value="planifiee">En attente</option>
                                <option value="en_cours">En cours</option>
                                <option value="terminee">Terminée</option>
                                <option value="annulee">Annulée</option>
                            </select>
                        </div>
                        <div class="mb-2"><label class="form-label">Description</label><textarea class="form-control" id="maint-description" rows="2"></textarea></div>
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


        const modalMaint = new bootstrap.Modal(document.getElementById('modal-maint'));
        let pageActuelle = 1;
        function formaterMontant(m) { return m ? Number(m).toLocaleString('fr-FR') + ' F CFA' : '-'; }
        function formaterDate(d) { return d ? new Date(d).toLocaleDateString('fr-FR') : '-'; }

        function joursRestants(dateProchaine) {
            if (!dateProchaine) return '-';
            const diff = Math.ceil((new Date(dateProchaine) - new Date()) / 86400000);
            if (diff < 0) return `<span class="text-danger">en retard de ${Math.abs(diff)} jours</span>`;
            return `dans ${diff} jours`;
        }

        function chargerTypes() {
            $.getJSON('api/maintenance_types_distincts.php', function (r) {
                r.data.forEach(t => $('#filtre-type').append(`<option value="${t}">${t}</option>`));
            });
        }

        function chargerStats() {
            $.getJSON('api/maintenance_stats.php', function (r) {
                $('#stat-total').text(r.total);
                $('#stat-en-cours').text(r.en_cours);
                $('#stat-terminees').text(r.terminees);
                $('#stat-en-retard').text(r.en_retard);
                $('#pct-en-cours').html(`<span class="contexte">${r.pct_en_cours}% du total</span>`);
                $('#pct-terminees').html(`<span class="contexte">${r.pct_terminees}% du total</span>`);
                $('#pct-en-retard').html(`<span class="contexte">${r.pct_en_retard}% du total</span>`);
                if (r.variation_total !== null) {
                    const hausse = r.variation_total >= 0;
                    $('#tendance-total').html(`<i class="fa-solid fa-arrow-${hausse?'up':'down'}"></i> ${Math.abs(r.variation_total)}% <span class="contexte">vs mois dernier</span>`).attr('class', 'tendance ' + (hausse?'hausse':'baisse'));
                }
            }).fail(() => toastr.error("Impossible de charger les statistiques.", 'Erreur'));
        }

        function chargerListe(page = 1) {
            pageActuelle = page;
            const params = {
                recherche: $('#filtre-recherche').val(), statut: $('#filtre-statut').val(), type_maintenance: $('#filtre-type').val(),
                periode_debut: $('#filtre-periode-debut').val(), periode_fin: $('#filtre-periode-fin').val(), tri: $('#filtre-tri').val(), page,
            };
            $.getJSON('api/maintenance_liste.php', params, function (r) {
                $('#titre-liste').text(`Liste des interventions (${r.total})`);
                const $corps = $('#corps-table-maint').empty();
                if (r.data.length === 0) { $corps.append('<tr><td colspan="9" class="text-center text-muted py-4">Aucune intervention trouvée.</td></tr>'); }
                else {
                    r.data.forEach(m => {
                        $corps.append(`<tr>
                            <td class="ref-maint">${m.reference}</td>
                            <td>${m.vehicule}<br><small class="text-muted">${m.immatriculation}</small></td>
                            <td>${m.type_maintenance}</td>
                            <td>${m.fournisseur}</td>
                            <td>${formaterMontant(m.cout)}</td>
                            <td><span class="badge-statut-m ${m.statut}">${m.statut_libelle}</span></td>
                            <td>${formaterDate(m.date_prevue)}</td>
                            <td>${m.prochaine_maintenance ? formaterDate(m.prochaine_maintenance) + '<br><small>' + joursRestants(m.prochaine_maintenance) + '</small>' : '-'}</td>
                            <td><div class="dropdown"><button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown"><i class="fa-solid fa-ellipsis"></i></button>
                                <ul class="dropdown-menu"><li><a class="dropdown-item btn-editer-maint" href="#" data-id="${m.id_maintenance}">Modifier</a></li></ul></div></td>
                        </tr>`);
                    });
                }
                const debut = (r.page - 1) * r.par_page + 1, fin = Math.min(r.page * r.par_page, r.total);
                $('#info-pagination').text(r.total > 0 ? `Affichage de ${debut} à ${fin} sur ${r.total} interventions` : '');
                const $pag = $('#zone-pagination').empty();
                const derniere = r.total_pages || 1;
                $pag.append(`<div class="page-btn ${r.page<=1?'desactive':''}" data-page="${r.page-1}"><i class="fa-solid fa-chevron-left"></i></div>`);
                for (let p = 1; p <= derniere; p++) {
                    if (p === 1 || p === derniere || Math.abs(p - r.page) <= 1) $pag.append(`<div class="page-btn ${p===r.page?'actif':''}" data-page="${p}">${p}</div>`);
                    else if (Math.abs(p - r.page) === 2) $pag.append(`<div class="page-btn desactive">…</div>`);
                }
                $pag.append(`<div class="page-btn ${r.page>=derniere?'desactive':''}" data-page="${r.page+1}"><i class="fa-solid fa-chevron-right"></i></div>`);
            }).fail(() => toastr.error("Impossible de charger les interventions.", 'Erreur'));
        }

        $('#zone-pagination').on('click', '.page-btn:not(.desactive)', function () { const p = $(this).data('page'); if (p) chargerListe(p); });
        let timeoutR;
        $('#filtre-recherche').on('input', function () { clearTimeout(timeoutR); timeoutR = setTimeout(() => chargerListe(1), 350); });
        $('#filtre-statut, #filtre-type, #filtre-periode-debut, #filtre-periode-fin, #filtre-tri').on('change', () => chargerListe(1));
        $('#btn-reinitialiser').on('click', function (e) { e.preventDefault(); $('#filtre-recherche').val(''); $('#filtre-statut, #filtre-type').val(''); $('#filtre-periode-debut, #filtre-periode-fin').val(''); $('#filtre-tri').val('date_desc'); chargerListe(1); });

        let timeoutV;
        $('#maint-recherche-vehicule').on('input', function () {
            const terme = $(this).val(); $('#maint-id-vehicule').val('');
            clearTimeout(timeoutV);
            if (terme.length < 1) { $('#suggestions-vehicule-maint').hide(); return; }
            timeoutV = setTimeout(() => {
                $.getJSON('api/vehicules_liste.php', { recherche: terme, page: 1 }, function (r) {
                    const $liste = $('#suggestions-vehicule-maint').empty();
                    if (r.data.length === 0) { $liste.hide(); return; }
                    r.data.forEach(v => $liste.append(`<div class="suggestion" data-id="${v.id_vehicule}" data-label="${v.marque} ${v.modele} (${v.immatriculation})">${v.marque} ${v.modele} (${v.immatriculation})</div>`));
                    $liste.show();
                });
            }, 300);
        });
        $('#suggestions-vehicule-maint').on('click', '.suggestion', function () {
            $('#maint-id-vehicule').val($(this).data('id'));
            $('#maint-recherche-vehicule').val($(this).data('label'));
            $('#suggestions-vehicule-maint').hide();
        });
        $(document).on('click', function (e) { if (!$(e.target).closest('.zone-suggestions').length) $('.liste-suggestions').hide(); });

        $('#btn-nouvelle-intervention').on('click', function () {
            $('#form-maint')[0].reset(); $('#maint-id, #maint-id-vehicule').val('');
            $('#maint-recherche-vehicule').prop('disabled', false);
            $('#maint-statut').val('planifiee');
            $('#titre-modal-maint').text('Nouvelle intervention'); modalMaint.show();
        });

        $('#corps-table-maint').on('click', '.btn-editer-maint', function (e) {
            e.preventDefault();
            $.getJSON('api/maintenance_detail.php', { id: $(this).data('id') }, function (r) {
                const m = r.data;
                $('#maint-id').val(m.id_maintenance);
                $('#maint-id-vehicule').val(m.id_vehicule);
                $('#maint-recherche-vehicule').val(m.marque + ' ' + m.modele + ' (' + m.immatriculation + ')').prop('disabled', true);
                $('#maint-type').val(m.type_maintenance);
                $('#maint-fournisseur').val(m.fournisseur);
                $('#maint-date-prevue').val(m.date_prevue);
                $('#maint-cout').val(m.cout);
                $('#maint-statut').val(m.statut);
                $('#maint-description').val(m.description);
                $('#titre-modal-maint').text("Modifier l'intervention"); modalMaint.show();
            }).fail(() => toastr.error("Impossible de charger cette intervention.", 'Erreur'));
        });

        $('#form-maint').on('submit', function (e) {
            e.preventDefault();
            const id = $('#maint-id').val();
            if (!$('#maint-id-vehicule').val()) { toastr.warning('Veuillez sélectionner un véhicule.', 'Champ manquant'); return; }
            const donnees = {
                id_vehicule: $('#maint-id-vehicule').val(), type_maintenance: $('#maint-type').val(), fournisseur: $('#maint-fournisseur').val(),
                date_prevue: $('#maint-date-prevue').val(), cout: $('#maint-cout').val(), statut: $('#maint-statut').val(), description: $('#maint-description').val(),
            };
            $.ajax({
                url: id ? `api/maintenance_modifier.php?id=${id}` : 'api/maintenance_creer.php', method: id ? 'PUT' : 'POST',
                contentType: 'application/json', data: JSON.stringify(donnees),
                success: function (r) { modalMaint.hide(); $('#maint-recherche-vehicule').prop('disabled', false); toastr.success(r.message, 'Succès'); chargerListe(pageActuelle); chargerStats(); },
                error: function (xhr) { const r = xhr.responseJSON || {}; if (xhr.status !== 401) toastr.error(r.message || "Erreur.", 'Erreur'); }
            });
        });

        chargerTypes(); chargerStats(); chargerListe(1);
    </script>
</body>
</html>
