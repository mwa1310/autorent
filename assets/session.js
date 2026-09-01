/**
 * Gestion de session JWT - à inclure sur toute page protégée du back-office,
 * APRÈS jQuery et Toastr. Chaque page définit URL_CONNEXION avant l'inclusion.
 */

const _urlConnexion = (typeof URL_CONNEXION !== 'undefined') ? URL_CONNEXION : '../index.php';

$.ajaxSetup({
    beforeSend: function (xhr) {
        const token = localStorage.getItem('jwt_token');
        if (token) {
            xhr.setRequestHeader('Authorization', 'Bearer ' + token);
        }
    }
});

$(document).ajaxError(function (event, xhr) {
    if (xhr.status === 401) {
        localStorage.removeItem('jwt_token');
        localStorage.removeItem('utilisateur');
        toastr.warning('Votre session a expiré, veuillez vous reconnecter.', 'Session expirée');
        setTimeout(function () { window.location.href = _urlConnexion; }, 1500);
    }
    if (xhr.status === 403) {
        toastr.error('Accès non autorisé pour votre rôle.', 'Accès refusé');
    }
});

function exigerConnexionSinonRediriger() {
    if (!localStorage.getItem('jwt_token')) {
        window.location.href = _urlConnexion;
    }
}

function deconnecter() {
    localStorage.removeItem('jwt_token');
    localStorage.removeItem('utilisateur');
    window.location.href = _urlConnexion;
}
