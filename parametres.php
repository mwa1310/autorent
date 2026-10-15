<?php $pageActive = 'parametres'; $roleConnecte = 'admin'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>AutoRent - Paramètres</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="assets/toastr/toastr.min.css">
    <link rel="stylesheet" href="style/style.css">
</head>
<body>
    <?php include __DIR__ . '/includes/sidebar.php'; ?>
    <div class="contenu-principal">
        <!-- Topbar -->
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

        <div class="entete-page">
            <h1>Paramètres</h1>
            <p class="sous-titre">Votre profil et la configuration générale de l'agence</p>
        </div>

        <div class="row g-3">
            <!-- Mon profil -->
            <div class="col-lg-5" id="colonne-profil">
                <div class="panneau">
                    <div class="entete-panneau"><h6><i class="fa-solid fa-user"></i> Mon profil</h6></div>
                    <form id="form-profil">
                        <div class="row g-2">
                            <div class="col-6 mb-2"><label class="form-label">Prénom *</label><input type="text" class="form-control" id="profil-prenom" required></div>
                            <div class="col-6 mb-2"><label class="form-label">Nom *</label><input type="text" class="form-control" id="profil-nom" required></div>
                        </div>
                        <div class="mb-2"><label class="form-label">Email *</label><input type="email" class="form-control" id="profil-email" required></div>
                        <div class="mb-3">
                            <label class="form-label">Rôle</label>
                            <input type="text" class="form-control" id="profil-role" disabled>
                            <small class="text-muted">Le mot de passe et le rôle ne se modifient que depuis la page <a href="utilisateurs.php" class="menu-admin-only">Utilisateurs</a>, par un administrateur.</small>
                        </div>
                        <button type="submit" class="btn btn-primary" style="background:#6366f1; border:none;">Enregistrer mon profil</button>
                    </form>
                </div>
            </div>

            <!-- Informations de l'agence (admin uniquement) -->
            <div class="col-lg-7 menu-admin-only">
                <div class="panneau">
                    <div class="entete-panneau"><h6><i class="fa-solid fa-building"></i> Informations de l'agence</h6></div>
                    <form id="form-agence">
                        <div class="row g-2">
                            <div class="col-6 mb-2"><label class="form-label">Nom de l'agence *</label><input type="text" class="form-control" id="agence-nom" required></div>
                            <div class="col-6 mb-2"><label class="form-label">Devise</label><input type="text" class="form-control" id="agence-devise"></div>
                        </div>
                        <div class="mb-2"><label class="form-label">Adresse</label><input type="text" class="form-control" id="agence-adresse"></div>
                        <div class="row g-2">
                            <div class="col-6 mb-2"><label class="form-label">Téléphone</label><input type="text" class="form-control" id="agence-telephone"></div>
                            <div class="col-6 mb-2"><label class="form-label">Email</label><input type="email" class="form-control" id="agence-email"></div>
                        </div>
                        <hr>
                        <p class="text-muted mb-2" style="font-size:12.5px;">Ces réglages influencent directement le calcul automatique des tarifs et les alertes du tableau de bord.</p>
                        <div class="row g-2">
                            <div class="col-4 mb-2">
                                <label class="form-label">Jours facturés / semaine</label>
                                <input type="number" class="form-control" id="agence-jours-semaine" min="1" max="7">
                                <small class="text-muted">Ex: 6 = le 7e jour offert</small>
                            </div>
                            <div class="col-4 mb-2">
                                <label class="form-label">Jours facturés / mois</label>
                                <input type="number" class="form-control" id="agence-jours-mois" min="1" max="31">
                            </div>
                            <div class="col-4 mb-2">
                                <label class="form-label">Alerte maintenance (jours avant)</label>
                                <input type="number" class="form-control" id="agence-jours-alerte" min="1">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary mt-2" style="background:#6366f1; border:none;">Enregistrer les paramètres</button>
                    </form>
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
        const utilisateurConnecte = JSON.parse(localStorage.getItem('utilisateur') || '{}');
        const estAdmin = utilisateurConnecte.role === 'admin';
        if (!estAdmin) {
            $('.menu-admin-only').hide();
            $('#colonne-profil').removeClass('col-lg-5').addClass('col-lg-8 mx-auto');
        }

        const prenomUtilisateur = utilisateurConnecte.prenom || (estAdmin ? 'Admin' : 'Agent');
        const nomUtilisateur = utilisateurConnecte.nom || '';
        const initiales = (prenomUtilisateur[0] || 'A') + (nomUtilisateur[0] || (estAdmin ? 'D' : 'G'));

        const elAvatar = document.getElementById('avatar-initiales');
        if (elAvatar) elAvatar.textContent = initiales.toUpperCase();
        const elNom = document.getElementById('topbar-nom-utilisateur');
        if (elNom) elNom.textContent = prenomUtilisateur;
        const elRole = document.getElementById('topbar-role-utilisateur');
        if (elRole) elRole.textContent = estAdmin ? 'Administrateur' : 'Agent';
        const elDate = document.getElementById('date-du-jour');
        if (elDate) elDate.textContent = new Date().toLocaleDateString('fr-FR', { day: '2-digit', month: 'long', year: 'numeric' });

        function chargerProfil() {
            $.getJSON('api/mon_profil_obtenir.php', function (r) {
                const p = r.data;
                $('#profil-prenom').val(p.prenom);
                $('#profil-nom').val(p.nom);
                $('#profil-email').val(p.email);
                $('#profil-role').val(p.role === 'admin' ? 'Administrateur' : 'Agent');
            }).fail(() => toastr.error("Impossible de charger votre profil.", 'Erreur'));
        }

        $('#form-profil').on('submit', function (e) {
            e.preventDefault();
            const donnees = { prenom: $('#profil-prenom').val(), nom: $('#profil-nom').val(), email: $('#profil-email').val() };
            $.ajax({
                url: 'api/mon_profil_modifier.php', method: 'PUT', contentType: 'application/json', data: JSON.stringify(donnees),
                success: function (r) {
                    toastr.success(r.message, 'Profil mis à jour');
                    // Met à jour le nom affiché dans la sidebar/localStorage sans devoir se reconnecter
                    const maj = JSON.parse(localStorage.getItem('utilisateur'));
                    maj.nom = donnees.nom; maj.prenom = donnees.prenom; maj.email = donnees.email;
                    localStorage.setItem('utilisateur', JSON.stringify(maj));
                },
                error: function (xhr) { const r = xhr.responseJSON || {}; if (xhr.status !== 401) toastr.error(r.message || "Erreur.", 'Erreur'); }
            });
        });

        if (estAdmin) {
            $.getJSON('api/parametres_obtenir.php', function (r) {
                const p = r.data;
                $('#agence-nom').val(p.nom_agence);
                $('#agence-devise').val(p.devise);
                $('#agence-adresse').val(p.adresse);
                $('#agence-telephone').val(p.telephone);
                $('#agence-email').val(p.email);
                $('#agence-jours-semaine').val(p.jours_semaine_facturation);
                $('#agence-jours-mois').val(p.jours_mois_facturation);
                $('#agence-jours-alerte').val(p.jours_alerte_maintenance);
            }).fail(() => toastr.error("Impossible de charger les paramètres de l'agence.", 'Erreur'));

            $('#form-agence').on('submit', function (e) {
                e.preventDefault();
                const donnees = {
                    nom_agence: $('#agence-nom').val(), devise: $('#agence-devise').val(), adresse: $('#agence-adresse').val(),
                    telephone: $('#agence-telephone').val(), email: $('#agence-email').val(),
                    jours_semaine_facturation: $('#agence-jours-semaine').val(), jours_mois_facturation: $('#agence-jours-mois').val(),
                    jours_alerte_maintenance: $('#agence-jours-alerte').val(),
                };
                $.ajax({
                    url: 'api/parametres_modifier.php', method: 'PUT', contentType: 'application/json', data: JSON.stringify(donnees),
                    success: function (r) { toastr.success(r.message, 'Paramètres enregistrés'); },
                    error: function (xhr) { const r = xhr.responseJSON || {}; if (xhr.status !== 401) toastr.error(r.message || "Erreur.", 'Erreur'); }
                });
            });
        }

        chargerProfil();
    </script>
</body>
</html>
