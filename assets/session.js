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

$(document).on('click', '.profil-utilisateur', function () {
    const prefixe = (typeof URL_CONNEXION !== 'undefined' && URL_CONNEXION.indexOf('../') === 0) ? '../' : '';
    window.location.href = prefixe + 'parametres.php';
});

$(document).on('click', '.cloche-notif', function () {
    const prefixe = (typeof URL_CONNEXION !== 'undefined' && URL_CONNEXION.indexOf('../') === 0) ? '../' : '';
    window.location.href = prefixe + 'alertes.php';
});

/**
 * Modale de confirmation Bootstrap universelle (remplace window.confirm)
 */
function confirmerAction(options, callback) {
    if (typeof options === 'string') {
        options = { message: options };
    }
    const titre = options.titre || 'Confirmation';
    const message = options.message || 'Etes-vous sur de vouloir continuer ?';
    const btnTexte = options.btnTexte || 'Confirmer';
    const btnClasse = options.btnClasse || 'btn-danger';

    let $modal = $('#modal-confirmation-globale');
    if (!$modal.length) {
        $('body').append(`
            <div class="modal fade" id="modal-confirmation-globale" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered" style="max-width:420px;">
                    <div class="modal-content" style="border-radius:12px; border:none; box-shadow:0 10px 30px rgba(0,0,0,0.15);">
                        <div class="modal-header border-0 pb-0">
                            <h5 class="modal-title fw-bold" id="titre-modal-conf"></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body py-3 text-muted" id="message-modal-conf" style="font-size:14px;"></div>
                        <div class="modal-footer border-0 pt-0">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                            <button type="button" class="btn" id="btn-confirmer-action"></button>
                        </div>
                    </div>
                </div>
            </div>
        `);
        $modal = $('#modal-confirmation-globale');
    }

    $('#titre-modal-conf').text(titre);
    $('#message-modal-conf').text(message);
    const $btn = $('#btn-confirmer-action');
    $btn.attr('class', 'btn ' + btnClasse).text(btnTexte);

    const bsModal = bootstrap.Modal.getOrCreateInstance($modal[0]);

    $btn.off('click').on('click', function () {
        bsModal.hide();
        if (typeof callback === 'function') callback();
    });

    bsModal.show();
}



