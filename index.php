<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AutoRent - Connexion</title>

    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/toastr/toastr.min.css">
    <link rel="stylesheet" href="style/index.css">

</head>
<body>

    <div class="ecran-connexion">

        <!-- Panneau gauche : fond + logo -->
        <div class="panneau-visuel">
            <img src="images/autorent.png" alt="Logo AutoRent" class="logo-autorent">
        </div>

        <!-- Panneau droit : formulaire de connexion -->
        <div class="panneau-formulaire">
            <img src="images/autorent.png" alt="Logo AutoRent" class="logo-autorent">
            <div class="carte-connexion">
                <h2>Bienvenue !</h2>
                <p class="sous-titre">Connectez-vous à votre compte pour accéder à votre espace de gestion</p>

                <form id="form-connexion" novalidate>
                    <div class="mb-3">
                        <label for="connexion-email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="connexion-email" name="email" required>
                    </div>
                    <div class="mb-2">
                        <label for="connexion-motdepasse" class="form-label">Mot de passe</label>
                        <input type="password" class="form-control" id="connexion-motdepasse" name="mot_de_passe" required>
                    </div>

                    <button type="submit" class="btn btn-connexion mt-3" id="btn-connexion">
                        <span class="libelle-btn">Se connecter</span>
                    </button>
                </form>
            </div>
        </div>

    </div>

    <script src="assets/jquery/jquery.min.js"></script>
    <script src="assets/jquery-ui/jquery.validate.min.js"></script>
    <script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/toastr/toastr.min.js"></script>

    <script>
        toastr.options = {
            closeButton: true,
            progressBar: true,
            positionClass: 'toast-top-right',
            timeOut: 4000
        };

        // if (localStorage.getItem('jwt_token')) {
        //     const utilisateurExistant = JSON.parse(localStorage.getItem('utilisateur') || '{}');
        //     window.location.href = (utilisateurExistant.role === 'admin') ? 'admin/accueil.php' : 'agent/accueil.php';
        // }

        function basculerChargement($bouton, enCours, texteChargement) {
            if (enCours) {
                $bouton.data('texte-original', $bouton.find('.libelle-btn').text());
                $bouton.prop('disabled', true).find('.libelle-btn').text(texteChargement);
            } else {
                $bouton.prop('disabled', false).find('.libelle-btn').text($bouton.data('texte-original'));
            }
        }

        $("#form-connexion").validate({
            rules: {
                email: {
                    required: true,
                    email: true
                },
                mot_de_passe: {
                    required: true,
                    minlength: 6
                }
            },
            messages: {
                email: {
                    required: "Veuillez renseigner votre email",
                    email: "Format d'email invalide"
                },
                mot_de_passe: {
                    required: "Veuillez saisir votre mot de passe",
                    minlength: "6 caractères minimum"
                }
            },
            errorClass: "is-invalid",
            validClass: "is-valid",
            errorElement: "label",
            errorPlacement: function (error, element) {
                error.insertAfter(element);
            },
            submitHandler: function (form) {
                const $bouton = $('#btn-connexion');
                const donnees = {
                    email: $('#connexion-email').val().trim(),
                    mot_de_passe: $('#connexion-motdepasse').val()
                };

                basculerChargement($bouton, true, 'Connexion...');

                $.ajax({
                    url: 'api/auth_connexion.php',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(donnees),
                    success: function (reponse) {
                        localStorage.setItem('jwt_token', reponse.token);
                        localStorage.setItem('utilisateur', JSON.stringify(reponse.utilisateur));

                        toastr.success('Bienvenue ' + reponse.utilisateur.prenom + ' !', 'Connexion réussie');

                        // Redirection selon le rôle
                        const PageAccueil = reponse.utilisateur.role === 'admin' ? 'admin/accueil.php' : 'agent/accueil.php';

                        setTimeout(function () {
                            window.location.href = PageAccueil;
                        }, 1000);
                    },
                    error: function (xhr) {
                        const reponse = xhr.responseJSON || {};
                        toastr.error(reponse.message || "Une erreur est survenue.", 'Échec de connexion');
                    },
                    complete: function () {
                        basculerChargement($bouton, false);
                    }
                });
            }
        });
    </script>
</body>
</html>