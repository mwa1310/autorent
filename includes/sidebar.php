<?php
/**
 * Fragment de sidebar - à inclure dans chaque page du back-office.
 * Variables attendues avant l'inclusion :
 *   $pageActive    (ex: 'accueil', 'vehicules', 'reservations'...)
 *   $roleConnecte  (affichage uniquement ; la vraie vérification se fait via le JWT côté API)
 *   $prefixeRacine ('' si la page est à la racine du projet, '../' si dans un sous-dossier)
 */
if (!isset($pageActive)) { $pageActive = ''; }
if (!isset($prefixeRacine)) { $prefixeRacine = ''; }
$dossierAccueil = (($roleConnecte ?? '') === 'admin') ? 'admin' : 'agent';
?>
<div class="sidebar">
    <div class="zone-logo-sidebar">
       <img src="<?= $prefixeRacine ?>images/autorent2.png" alt="Logo AutoRent" height="100" width="200">
    </div>

    <nav class="flex-grow-1">

        <div class="rubrique-titre">Général</div>
        <a href="<?= $prefixeRacine . $dossierAccueil ?>/accueil.php" class="menu-item <?= $pageActive === 'accueil' ? 'actif' : '' ?>">
            <span class="icone"><i class="fa-solid fa-house"></i></span> Accueil
        </a>

        <div class="rubrique-titre">Opérations</div>
        <a href="<?= $prefixeRacine ?>reservations.php" class="menu-item <?= $pageActive === 'reservations' ? 'actif' : '' ?>">
            <span class="icone"><i class="fa-solid fa-calendar-days"></i></span> Réservations
        </a>
        <a href="<?= $prefixeRacine ?>vehicules.php" class="menu-item <?= $pageActive === 'vehicules' ? 'actif' : '' ?>">
            <span class="icone"><i class="fa-solid fa-car-side"></i></span> Véhicules
        </a>
        <a href="<?= $prefixeRacine ?>clients.php" class="menu-item <?= $pageActive === 'clients' ? 'actif' : '' ?>">
            <span class="icone"><i class="fa-solid fa-user"></i></span> Clients
        </a>
        <a href="<?= $prefixeRacine ?>paiements.php" class="menu-item <?= $pageActive === 'paiements' ? 'actif' : '' ?>">
            <span class="icone"><i class="fa-solid fa-money-bill-wave"></i></span> Paiements
        </a>
        <a href="<?= $prefixeRacine ?>maintenance.php" class="menu-item <?= $pageActive === 'maintenance' ? 'actif' : '' ?>">
            <span class="icone"><i class="fa-solid fa-screwdriver-wrench"></i></span> Maintenance
        </a>

        <?php if (($roleConnecte ?? '') === 'admin'): ?>
            <div class="rubrique-titre menu-admin-only">Administration</div>
            <a href="<?= $prefixeRacine ?>tarifs.php" class="menu-item menu-admin-only <?= $pageActive === 'tarifs' ? 'actif' : '' ?>">
                <span class="icone"><i class="fa-solid fa-tags"></i></span> Catégories &amp; tarifs
            </a>
            <a href="<?= $prefixeRacine ?>utilisateurs.php" class="menu-item menu-admin-only <?= $pageActive === 'utilisateurs' ? 'actif' : '' ?>">
                <span class="icone"><i class="fa-solid fa-user-shield"></i></span> Utilisateurs
            </a>
            <a href="<?= $prefixeRacine ?>rapports.php" class="menu-item menu-admin-only <?= $pageActive === 'rapports' ? 'actif' : '' ?>">
                <span class="icone"><i class="fa-solid fa-chart-column"></i></span> Rapports
            </a>
            <a href="<?= $prefixeRacine ?>parametres.php" class="menu-item menu-admin-only <?= $pageActive === 'parametres' ? 'actif' : '' ?>">
                <span class="icone"><i class="fa-solid fa-gear"></i></span> Paramètres
            </a>
        <?php endif; ?>
    </nav>

    <div class="pied-sidebar">
        <button type="button" class="btn-deconnexion" onclick="deconnecter()">
            <span class="icone"><i class="fa-solid fa-right-from-bracket"></i></span> Déconnexion
        </button>
    </div>
</div>