<?php $pageActive = 'clients'; $roleConnecte = 'admin'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>AutoRent - Clients</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="assets/toastr/toastr.min.css">
    <link rel="stylesheet" href="style/style.css">
    <style>
        .table-clients th { text-transform:uppercase; font-size:11px; color:#9ca3af; font-weight:600; border-bottom:1px solid #f0f1f4; padding:10px 8px; }
        .table-clients td { padding:12px 8px; border-bottom:1px solid #f6f7f9; font-size:13.5px; vertical-align:middle; }
        .avatar-mini { width:34px; height:34px; border-radius:50%; background:#6366f1; color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:12.5px; }
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
            <div class="entete-page"><h1>Clients</h1><p class="sous-titre">Gérez la base de vos clients</p></div>
            <button class="btn btn-primary" id="btn-nouveau-client" style="background:#6366f1; border:none;"><i class="fa-solid fa-plus"></i> Nouveau client</button>
        </div>

        <div class="row row-cols-1 row-cols-md-3 g-3 mb-4">
            <div class="col"><div class="carte-stat"><div class="icone-stat" style="background:#e8f0fe; color:#2563eb;"><i class="fa-solid fa-users"></i></div><div class="valeur-stat" id="stat-total">—</div><div class="libelle-stat">Clients totaux</div></div></div>
            <div class="col"><div class="carte-stat"><div class="icone-stat" style="background:#e9f7ef; color:#16a34a;"><i class="fa-solid fa-user-plus"></i></div><div class="valeur-stat" id="stat-nouveaux">—</div><div class="libelle-stat">Nouveaux ce mois-ci</div></div></div>
            <div class="col"><div class="carte-stat"><div class="icone-stat" style="background:#f1eafd; color:#7c3aed;"><i class="fa-solid fa-calendar-check"></i></div><div class="valeur-stat" id="stat-actifs">—</div><div class="libelle-stat">Avec réservation active</div></div></div>
        </div>

        <div class="panneau mb-3">
            <label class="form-label" style="font-size:12.5px; font-weight:600;">Recherche</label>
            <input type="text" id="filtre-recherche" class="form-control" placeholder="Nom, prénom, téléphone, email...">
        </div>

        <div class="panneau">
            <div class="entete-panneau"><h6 id="titre-liste">Liste des clients</h6></div>
            <div class="table-responsive">
                <table class="table-clients w-100">
                    <thead><tr><th></th><th>Nom</th><th>Téléphone</th><th>Email</th><th>N° Permis</th><th>Réservations</th><th>Client depuis</th><th>Actions</th></tr></thead>
                    <tbody id="corps-table-clients"><tr><td colspan="8" class="text-center text-muted py-4">Chargement...</td></tr></tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-3">
                <small class="text-muted" id="info-pagination"></small>
                <div class="d-flex gap-1 pagination-perso" id="zone-pagination"></div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-client" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="form-client">
                    <div class="modal-header"><h5 class="modal-title" id="titre-modal-client">Nouveau client</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                    <div class="modal-body">
                        <input type="hidden" id="client-id">
                        <div class="row g-2">
                            <div class="col-6 mb-2"><label class="form-label">Prénom *</label><input type="text" class="form-control" id="client-prenom" required></div>
                            <div class="col-6 mb-2"><label class="form-label">Nom *</label><input type="text" class="form-control" id="client-nom" required></div>
                        </div>
                        <div class="row g-2">
                            <div class="col-6 mb-2"><label class="form-label">Téléphone *</label><input type="text" class="form-control" id="client-telephone" required></div>
                            <div class="col-6 mb-2"><label class="form-label">Email</label><input type="email" class="form-control" id="client-email"></div>
                        </div>
                        <div class="row g-2">
                            <div class="col-6 mb-2"><label class="form-label">N° Permis *</label><input type="text" class="form-control" id="client-permis" required></div>
                            <div class="col-6 mb-2"><label class="form-label">Délivré le</label><input type="date" class="form-control" id="client-date-permis"></div>
                        </div>
                        <div class="mb-2"><label class="form-label">N° CNI</label><input type="text" class="form-control" id="client-cni"></div>
                        <div class="mb-2"><label class="form-label">Adresse</label><textarea class="form-control" id="client-adresse" rows="2"></textarea></div>
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
            $('#btn-ajouter-vehicule').hide();
            $('.menu-admin-only').hide(); // la sidebar est rendue côté PHP sans connaître le vrai rôle sur cette page partagée
            
            const prenomUtilisateur = utilisateurConnecte.prenom || 'Agent';
            const nomUtilisateur = utilisateurConnecte.nom || '';
            const initialess = (prenomUtilisateur[0] || 'A') + (nomUtilisateur[0] || 'G');

            document.getElementById('avatar-initiales').textContent = initialess.toUpperCase();
            document.getElementById('topbar-nom-utilisateur').textContent = prenomUtilisateur;
            document.getElementById('topbar-role-utilisateur').textContent = 'Agent';

        }
        else {
            const prenomUtilisateur = utilisateurConnecte.prenom || 'Admin';
            const nomUtilisateur = utilisateurConnecte.nom || '';
            const initialess = (prenomUtilisateur[0] || 'A') + (nomUtilisateur[0] || 'D');

            document.getElementById('avatar-initiales').textContent = initialess.toUpperCase();
            document.getElementById('topbar-nom-utilisateur').textContent = prenomUtilisateur;
            document.getElementById('topbar-role-utilisateur').textContent = 'Administrateur';

        }
        // Date du jour affichée dans la topbar
        document.getElementById('date-du-jour').textContent =
            new Date().toLocaleDateString('fr-FR', { day: '2-digit', month: 'long', year: 'numeric' });

        const modalClient = new bootstrap.Modal(document.getElementById('modal-client'));
        let pageActuelle = 1;
        function formaterDate(d) { return new Date(d).toLocaleDateString('fr-FR'); }
        function initiales(nom, prenom) { return ((prenom[0]||'')+(nom[0]||'')).toUpperCase(); }

        function chargerStats() {
            $.getJSON('api/clients_stats.php', function (r) {
                $('#stat-total').text(r.total);
                $('#stat-nouveaux').text(r.nouveaux_mois);
                $('#stat-actifs').text(r.avec_reservation_active);
            }).fail(() => toastr.error("Impossible de charger les statistiques.", 'Erreur'));
        }

        function chargerListe(page = 1) {
            pageActuelle = page;
            $.getJSON('api/clients_liste.php', { recherche: $('#filtre-recherche').val(), page }, function (r) {
                $('#titre-liste').text(`Liste des clients (${r.total})`);
                const $corps = $('#corps-table-clients').empty();
                if (r.data.length === 0) { $corps.append('<tr><td colspan="8" class="text-center text-muted py-4">Aucun client trouvé.</td></tr>'); }
                else {
                    r.data.forEach(c => {
                        const actions = estAdmin ? `
                            <button class="btn btn-sm btn-outline-secondary btn-editer-client" data-id="${c.id_client}" title="Modifier"><i class="fa-solid fa-pen"></i></button>
                            <button class="btn btn-sm btn-outline-danger btn-archiver-client" data-id="${c.id_client}" title="Archiver"><i class="fa-solid fa-box-archive"></i></button>`
                            : `<button class="btn btn-sm btn-outline-secondary btn-editer-client" data-id="${c.id_client}" title="Modifier"><i class="fa-solid fa-pen"></i></button>`;
                        $corps.append(`<tr>
                            <td><div class="avatar-mini">${initiales(c.nom, c.prenom)}</div></td>
                            <td><strong>${c.prenom} ${c.nom}</strong></td>
                            <td>${c.telephone}</td>
                            <td>${c.email || '—'}</td>
                            <td>${c.numero_permis}</td>
                            <td>${c.nb_reservations}</td>
                            <td>${formaterDate(c.date_creation)}</td>
                            <td>${actions}</td>
                        </tr>`);
                    });
                }
                const debut = (r.page - 1) * r.par_page + 1;
                const fin = Math.min(r.page * r.par_page, r.total);
                $('#info-pagination').text(r.total > 0 ? `Affichage de ${debut} à ${fin} sur ${r.total} clients` : '');
                const $pag = $('#zone-pagination').empty();
                const derniere = r.total_pages || 1;
                $pag.append(`<div class="page-btn ${r.page<=1?'desactive':''}" data-page="${r.page-1}"><i class="fa-solid fa-chevron-left"></i></div>`);
                for (let p = 1; p <= derniere; p++) {
                    if (p === 1 || p === derniere || Math.abs(p - r.page) <= 1) $pag.append(`<div class="page-btn ${p===r.page?'actif':''}" data-page="${p}">${p}</div>`);
                    else if (Math.abs(p - r.page) === 2) $pag.append(`<div class="page-btn desactive">…</div>`);
                }
                $pag.append(`<div class="page-btn ${r.page>=derniere?'desactive':''}" data-page="${r.page+1}"><i class="fa-solid fa-chevron-right"></i></div>`);
            }).fail(() => toastr.error("Impossible de charger les clients.", 'Erreur'));
        }

        $('#zone-pagination').on('click', '.page-btn:not(.desactive)', function () { const p = $(this).data('page'); if (p) chargerListe(p); });
        let timeoutRecherche;
        $('#filtre-recherche').on('input', function () { clearTimeout(timeoutRecherche); timeoutRecherche = setTimeout(() => chargerListe(1), 350); });

        $('#btn-nouveau-client').on('click', function () {
            $('#form-client')[0].reset(); $('#client-id').val('');
            $('#titre-modal-client').text('Nouveau client'); modalClient.show();
        });

        $('#corps-table-clients').on('click', '.btn-editer-client', function () {
            $.getJSON('api/client_detail.php', { id: $(this).data('id') }, function (r) {
                const c = r.data;
                $('#client-id').val(c.id_client); $('#client-prenom').val(c.prenom); $('#client-nom').val(c.nom);
                $('#client-telephone').val(c.telephone); $('#client-email').val(c.email);
                $('#client-permis').val(c.numero_permis); $('#client-date-permis').val(c.date_delivrance_permis);
                $('#client-cni').val(c.numero_CNI); $('#client-adresse').val(c.adresse);
                $('#titre-modal-client').text('Modifier le client'); modalClient.show();
            }).fail(() => toastr.error("Impossible de charger ce client.", 'Erreur'));
        });

        $('#form-client').on('submit', function (e) {
            e.preventDefault();
            const id = $('#client-id').val();
            const donnees = {
                prenom: $('#client-prenom').val(), nom: $('#client-nom').val(), telephone: $('#client-telephone').val(),
                email: $('#client-email').val(), numero_permis: $('#client-permis').val(), date_delivrance_permis: $('#client-date-permis').val(),
                numero_CNI: $('#client-cni').val(), adresse: $('#client-adresse').val(),
            };
            $.ajax({
                url: id ? `api/client_modifier.php?id=${id}` : 'api/client_creer.php', method: id ? 'PUT' : 'POST',
                contentType: 'application/json', data: JSON.stringify(donnees),
                success: function (r) { modalClient.hide(); toastr.success(r.message, 'Succès'); chargerListe(pageActuelle); chargerStats(); },
                error: function (xhr) { const r = xhr.responseJSON || {}; if (xhr.status !== 401) toastr.error(r.message || "Erreur.", 'Erreur'); }
            });
        });

        $('#corps-table-clients').on('click', '.btn-archiver-client', function () {
            const id = $(this).data('id');
            if (!window.confirm('Archiver ce client ?')) return;
            $.ajax({
                url: `api/client_archiver.php?id=${id}`, method: 'DELETE',
                success: function (r) { toastr.success(r.message, 'Archivé'); chargerListe(pageActuelle); chargerStats(); },
                error: function (xhr) { const r = xhr.responseJSON || {}; if (xhr.status !== 401) toastr.error(r.message || "Erreur.", 'Erreur'); }
            });
        });

        chargerStats(); chargerListe(1);
    </script>
</body>
</html>
