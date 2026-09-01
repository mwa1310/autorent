<?php $pageActive = 'utilisateurs'; $roleConnecte = 'admin'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>AutoRent - Utilisateurs</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="assets/toastr/toastr.min.css">
    <link rel="stylesheet" href="style/style.css">
    <style>
        .table-users th { text-transform:uppercase; font-size:11px; color:#9ca3af; font-weight:600; border-bottom:1px solid #f0f1f4; padding:10px 8px; }
        .table-users td { padding:12px 8px; border-bottom:1px solid #f6f7f9; font-size:13.5px; vertical-align:middle; }
        .avatar-mini { width:34px; height:34px; border-radius:50%; background:#6366f1; color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:12.5px; }
        .badge-role { font-size:11px; font-weight:600; padding:3px 10px; border-radius:999px; }
        .badge-role.admin { background:#f1eafd; color:#7c3aed; }
        .badge-role.agent { background:#e8f0fe; color:#2563eb; }
        .badge-etat-user { font-size:11px; font-weight:600; padding:3px 10px; border-radius:999px; }
        .badge-etat-user.actif { background:#e9f7ef; color:#16a34a; }
        .badge-etat-user.inactif { background:#f3f4f6; color:#6b7280; }
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
            <div class="entete-page"><h1>Utilisateurs</h1><p class="sous-titre">Gérez les comptes du personnel</p></div>
            <button class="btn btn-primary" id="btn-nouvel-utilisateur" style="background:#6366f1; border:none;"><i class="fa-solid fa-plus"></i> Nouvel utilisateur</button>
        </div>

        <div class="row row-cols-1 row-cols-md-4 g-3 mb-4">
            <div class="col"><div class="carte-stat"><div class="icone-stat" style="background:#e8f0fe; color:#2563eb;"><i class="fa-solid fa-users"></i></div><div class="valeur-stat" id="stat-total">-</div><div class="libelle-stat">Comptes totaux</div></div></div>
            <div class="col"><div class="carte-stat"><div class="icone-stat" style="background:#f1eafd; color:#7c3aed;"><i class="fa-solid fa-user-shield"></i></div><div class="valeur-stat" id="stat-admins">-</div><div class="libelle-stat">Administrateurs</div></div></div>
            <div class="col"><div class="carte-stat"><div class="icone-stat" style="background:#e8f0fe; color:#2563eb;"><i class="fa-solid fa-user"></i></div><div class="valeur-stat" id="stat-agents">-</div><div class="libelle-stat">Agents</div></div></div>
            <div class="col"><div class="carte-stat"><div class="icone-stat" style="background:#e9f7ef; color:#16a34a;"><i class="fa-solid fa-circle-check"></i></div><div class="valeur-stat" id="stat-actifs">-</div><div class="libelle-stat">Comptes actifs</div></div></div>
        </div>

        <div class="panneau mb-3">
            <div class="row g-3">
                <div class="col-md-5"><label class="form-label" style="font-size:12.5px; font-weight:600;">Recherche</label><input type="text" id="filtre-recherche" class="form-control" placeholder="Nom, prénom, email..."></div>
                <div class="col-md-3"><label class="form-label" style="font-size:12.5px; font-weight:600;">Rôle</label>
                    <select id="filtre-role" class="form-select"><option value="">Tous les rôles</option><option value="admin">Administrateur</option><option value="agent">Agent</option></select>
                </div>
                <div class="col-md-3"><label class="form-label" style="font-size:12.5px; font-weight:600;">Statut</label>
                    <select id="filtre-etat" class="form-select"><option value="">Tous les statuts</option><option value="actif">Actif</option><option value="inactif">Inactif</option></select>
                </div>
            </div>
            <a href="#" id="btn-reinitialiser" class="lien-voir-tout d-inline-block mt-2">Réinitialiser</a>
        </div>

        <div class="panneau">
            <div class="entete-panneau"><h6 id="titre-liste">Liste des utilisateurs</h6></div>
            <div class="table-responsive">
                <table class="table-users w-100">
                    <thead><tr><th></th><th>Nom</th><th>Email</th><th>Rôle</th><th>Statut</th><th>Créé le</th><th>Actions</th></tr></thead>
                    <tbody id="corps-table-users"><tr><td colspan="7" class="text-center text-muted py-4">Chargement...</td></tr></tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-user" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="form-user">
                    <div class="modal-header"><h5 class="modal-title" id="titre-modal-user">Nouvel utilisateur</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                    <div class="modal-body">
                        <input type="hidden" id="user-id">
                        <div class="row g-2">
                            <div class="col-6 mb-2"><label class="form-label">Prénom *</label><input type="text" class="form-control" id="user-prenom" required></div>
                            <div class="col-6 mb-2"><label class="form-label">Nom *</label><input type="text" class="form-control" id="user-nom" required></div>
                        </div>
                        <div class="mb-2"><label class="form-label">Email *</label><input type="email" class="form-control" id="user-email" required></div>
                        <div class="mb-2"><label class="form-label">Mot de passe <span id="mdp-obligatoire">*</span></label><input type="password" class="form-control" id="user-mdp" placeholder="Laisser vide pour ne pas changer"></div>
                        <div class="mb-2"><label class="form-label">Rôle *</label>
                            <select class="form-select" id="user-role" required><option value="agent">Agent</option><option value="admin">Administrateur</option></select>
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
        if (utilisateurConnecte.role !== 'admin') window.location.href = '../agent/accueil.php';

        // -----------------------------------------------------------
        // En-tête : identité de l'utilisateur connecté
        // -----------------------------------------------------------
        const prenomUtilisateur = utilisateurConnecte.prenom || 'Admin';
        const nomUtilisateur = utilisateurConnecte.nom || '';
        const initialess = (prenomUtilisateur[0] || 'A') + (nomUtilisateur[0] || 'D');

        document.getElementById('avatar-initiales').textContent = initialess.toUpperCase();
        document.getElementById('topbar-nom-utilisateur').textContent = prenomUtilisateur;
        document.getElementById('topbar-role-utilisateur').textContent = 'Administrateur';

        // Date du jour affichée dans la topbar
        document.getElementById('date-du-jour').textContent =
            new Date().toLocaleDateString('fr-FR', { day: '2-digit', month: 'long', year: 'numeric' });


        const modalUser = new bootstrap.Modal(document.getElementById('modal-user'));
        function formaterDate(d) { return new Date(d).toLocaleDateString('fr-FR'); }
        function initiales(nom, prenom) { return ((prenom[0]||'')+(nom[0]||'')).toUpperCase(); }

        function chargerStats() {
            $.getJSON('api/utilisateurs_stats.php', function (r) {
                $('#stat-total').text(r.total); $('#stat-admins').text(r.admins);
                $('#stat-agents').text(r.agents); $('#stat-actifs').text(r.actifs);
            }).fail(() => toastr.error("Impossible de charger les statistiques.", 'Erreur'));
        }

        function chargerListe() {
            const params = { recherche: $('#filtre-recherche').val(), role: $('#filtre-role').val(), etat: $('#filtre-etat').val() };
            $.getJSON('api/utilisateurs_liste.php', params, function (r) {
                $('#titre-liste').text(`Liste des utilisateurs (${r.data.length})`);
                const $corps = $('#corps-table-users').empty();
                if (r.data.length === 0) { $corps.append('<tr><td colspan="7" class="text-center text-muted py-4">Aucun utilisateur trouvé.</td></tr>'); return; }
                r.data.forEach(u => {
                    const soi = u.id_utilisateur == utilisateurConnecte.id_utilisateur;
                    $corps.append(`<tr>
                        <td><div class="avatar-mini">${initiales(u.nom, u.prenom)}</div></td>
                        <td><strong>${u.prenom} ${u.nom}</strong>${soi ? ' <small class="text-muted">(vous)</small>' : ''}</td>
                        <td>${u.email}</td>
                        <td><span class="badge-role ${u.role}">${u.role === 'admin' ? 'Administrateur' : 'Agent'}</span></td>
                        <td><span class="badge-etat-user ${u.etat}">${u.etat === 'actif' ? 'Actif' : 'Inactif'}</span></td>
                        <td>${formaterDate(u.date_creation)}</td>
                        <td><div class="dropdown"><button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown"><i class="fa-solid fa-ellipsis"></i></button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item btn-editer-user" href="#" data-id="${u.id_utilisateur}">Modifier</a></li>
                                ${!soi ? `<li><a class="dropdown-item btn-toggle-etat-user" href="#" data-id="${u.id_utilisateur}">${u.etat === 'actif' ? 'Désactiver' : 'Activer'}</a></li>` : ''}
                            </ul></div></td>
                    </tr>`);
                });
            }).fail(() => toastr.error("Impossible de charger les utilisateurs.", 'Erreur'));
        }

        let timeoutRecherche;
        $('#filtre-recherche').on('input', function () { clearTimeout(timeoutRecherche); timeoutRecherche = setTimeout(chargerListe, 350); });
        $('#filtre-role, #filtre-etat').on('change', chargerListe);
        $('#btn-reinitialiser').on('click', function (e) { e.preventDefault(); $('#filtre-recherche').val(''); $('#filtre-role, #filtre-etat').val(''); chargerListe(); });

        $('#btn-nouvel-utilisateur').on('click', function () {
            $('#form-user')[0].reset(); $('#user-id').val('');
            $('#user-mdp').prop('required', true).attr('placeholder', ''); $('#mdp-obligatoire').show();
            $('#titre-modal-user').text('Nouvel utilisateur'); modalUser.show();
        });

        $('#corps-table-users').on('click', '.btn-editer-user', function (e) {
            e.preventDefault();
            $.getJSON('api/utilisateur_detail.php', { id: $(this).data('id') }, function (r) {
                const u = r.data;
                $('#user-id').val(u.id_utilisateur); $('#user-prenom').val(u.prenom); $('#user-nom').val(u.nom);
                $('#user-email').val(u.email); $('#user-role').val(u.role);
                $('#user-mdp').val('').prop('required', false).attr('placeholder', 'Laisser vide pour ne pas changer');
                $('#mdp-obligatoire').hide();
                $('#titre-modal-user').text("Modifier l'utilisateur"); modalUser.show();
            }).fail(() => toastr.error("Impossible de charger cet utilisateur.", 'Erreur'));
        });

        $('#form-user').on('submit', function (e) {
            e.preventDefault();
            const id = $('#user-id').val();
            const donnees = { prenom: $('#user-prenom').val(), nom: $('#user-nom').val(), email: $('#user-email').val(), role: $('#user-role').val(), mot_de_passe: $('#user-mdp').val() };
            $.ajax({
                url: id ? `api/utilisateur_modifier.php?id=${id}` : 'api/utilisateur_creer.php', method: id ? 'PUT' : 'POST',
                contentType: 'application/json', data: JSON.stringify(donnees),
                success: function (r) { modalUser.hide(); toastr.success(r.message, 'Succès'); chargerListe(); chargerStats(); },
                error: function (xhr) { const r = xhr.responseJSON || {}; if (xhr.status !== 401) toastr.error(r.message || "Erreur.", 'Erreur'); }
            });
        });

        $('#corps-table-users').on('click', '.btn-toggle-etat-user', function (e) {
            e.preventDefault();
            $.ajax({
                url: `api/utilisateur_changer_etat.php?id=${$(this).data('id')}`, method: 'PUT',
                success: function (r) { toastr.success(r.message, 'Statut modifié'); chargerListe(); chargerStats(); },
                error: function (xhr) { const r = xhr.responseJSON || {}; if (xhr.status !== 401) toastr.error(r.message || "Erreur.", 'Erreur'); }
            });
        });

        chargerStats(); chargerListe();
    </script>
</body>
</html>
