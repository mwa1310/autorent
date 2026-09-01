<?php $pageActive = 'rapports'; $roleConnecte = 'admin'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>AutoRent - Rapports & statistiques</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="assets/toastr/toastr.min.css">
    <link rel="stylesheet" href="style/style.css">
    <style>
        .zone-graphique-donut { position: relative; width: 150px; height: 150px; margin: 0 auto; }
        .centre-donut { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; pointer-events: none; }
        .centre-donut .total-donut { font-size: 18px; font-weight: 700; color: #1e2233; }
        .centre-donut .libelle-donut { font-size: 10px; color: #9ca3af; }
        .table-rapport th { text-transform:uppercase; font-size:11px; color:#9ca3af; font-weight:600; border-bottom:1px solid #f0f1f4; padding:8px; }
        .table-rapport td { padding:10px 8px; border-bottom:1px solid #f6f7f9; font-size:13px; vertical-align:middle; }
        .badge-cat-mini { font-size: 10.5px; font-weight: 600; padding: 2px 8px; border-radius: 999px; background:#eef0fe; color:#4338ca; }
        .item-indicateur { display:flex; align-items:center; gap:10px; padding:10px 0; border-bottom:1px solid #f6f7f9; }
        .item-indicateur:last-child { border-bottom:none; }
        .icone-indicateur { width:32px; height:32px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:14px; flex-shrink:0; }
        .valeur-indicateur { margin-left:auto; text-align:right; }
        .valeur-indicateur .principal { font-weight:700; font-size:14px; }
        .valeur-indicateur .delta { font-size:11px; }
        .delta.hausse-positive { color:#16a34a; }
        .delta.hausse-negative { color:#dc2626; }
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
            <div class="entete-page">
                <h1><i class="fa-solid fa-chart-column" style="color:#6366f1;"></i> Rapports &amp; statistiques</h1>
                <p class="sous-titre">Vue d'ensemble complète de votre activité.</p>
            </div>
            <div class="d-flex align-items-center gap-2" style="background:#fff; border:1px solid #e5e7eb; border-radius:8px; padding:8px 12px;">
                <label style="font-size:12px; color:#6b7280;">Période</label>
                <input type="date" id="periode-debut" class="form-control form-control-sm" style="width:140px;">
                <span>-</span>
                <input type="date" id="periode-fin" class="form-control form-control-sm" style="width:140px;">
                <button class="btn btn-sm btn-primary" id="btn-appliquer-periode" style="background:#6366f1; border:none;">OK</button>
            </div>
        </div>

        <div class="row row-cols-1 row-cols-md-5 g-3 mb-4">
            <div class="col"><div class="carte-stat">
                <div class="icone-stat" style="background:#e8f0fe; color:#2563eb;"><i class="fa-solid fa-calendar-days"></i></div>
                <div class="valeur-stat" id="stat-reservations">-</div>
                <div class="libelle-stat">Réservations totales</div>
                <div class="tendance" id="tendance-reservations"></div>
            </div></div>
            <div class="col"><div class="carte-stat">
                <div class="icone-stat" style="background:#e9f7ef; color:#16a34a;"><i class="fa-solid fa-car-side"></i></div>
                <div class="valeur-stat" id="stat-locations">-</div>
                <div class="libelle-stat">Locations réalisées</div>
                <div class="tendance" id="tendance-locations"></div>
            </div></div>
            <div class="col"><div class="carte-stat">
                <div class="icone-stat" style="background:#fef3e2; color:#d97706;"><i class="fa-solid fa-sack-dollar"></i></div>
                <div class="valeur-stat" id="stat-ca">-</div>
                <div class="libelle-stat">Chiffre d'affaires</div>
                <div class="tendance" id="tendance-ca"></div>
            </div></div>
            <div class="col"><div class="carte-stat">
                <div class="icone-stat" style="background:#f1eafd; color:#7c3aed;"><i class="fa-solid fa-building-columns"></i></div>
                <div class="valeur-stat" id="stat-paiements">-</div>
                <div class="libelle-stat">Paiements reçus</div>
                <div class="tendance" id="tendance-paiements"></div>
            </div></div>
            <div class="col"><div class="carte-stat">
                <div class="icone-stat" style="background:#fdeaea; color:#dc2626;"><i class="fa-solid fa-clock"></i></div>
                <div class="valeur-stat" id="stat-occupation">-</div>
                <div class="libelle-stat">Taux d'occupation</div>
                <div class="tendance" id="tendance-occupation"></div>
            </div></div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-lg-5">
                <div class="panneau">
                    <div class="entete-panneau">
                        <h6>Évolution du chiffre d'affaires</h6>
                        <small class="text-muted" id="legende-periodes"></small>
                    </div>
                    <canvas id="graphique-evolution-ca" height="180"></canvas>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="panneau">
                    <div class="entete-panneau"><h6>Réservations par statut</h6></div>
                    <div class="zone-graphique-donut">
                        <canvas id="graphique-resa-statut"></canvas>
                        <div class="centre-donut"><div class="total-donut" id="total-resa-periode">-</div><div class="libelle-donut"></div></div>
                    </div>
                    <ul class="legende-donut">
                        <li><span><span class="puce" style="background:#2563eb;"></span>À venir</span><span class="valeur" id="legende-a-venir">-</span></li>
                        <li><span><span class="puce" style="background:#16a34a;"></span>En cours</span><span class="valeur" id="legende-en-cours">-</span></li>
                        <li><span><span class="puce" style="background:#f59e0b;"></span>Terminées</span><span class="valeur" id="legende-terminee">-</span></li>
                        <li><span><span class="puce" style="background:#9ca3af;"></span>Annulées</span><span class="valeur" id="legende-annulee">-</span></li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="panneau">
                    <div class="entete-panneau"><h6>CA par catégorie</h6></div>
                    <div class="zone-graphique-donut" style="width:130px; height:130px;">
                        <canvas id="graphique-ca-categorie"></canvas>
                        <div class="centre-donut"><div class="total-donut" id="total-ca-categorie" style="font-size:14px;">-</div><div class="libelle-donut"></div></div>
                    </div>
                    <ul class="legende-donut" id="legende-ca-categorie"></ul>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-6">
                <div class="panneau">
                    <div class="entete-panneau"><h6>Top 5 des véhicules les plus loués</h6></div>
                    <table class="table-rapport w-100">
                        <thead><tr><th>#</th><th>Véhicule</th><th>Catégorie</th><th>Locations</th><th>CA généré</th></tr></thead>
                        <tbody id="corps-top-vehicules"></tbody>
                    </table>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="panneau">
                    <div class="entete-panneau"><h6>Indicateurs clés</h6></div>
                    <div id="liste-indicateurs"></div>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/jquery/jquery.min.js"></script>
    <script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/toastr/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <script>const URL_CONNEXION = 'index.php';</script>
    <script src="assets/session.js"></script>

    <script>
        toastr.options = { closeButton: true, progressBar: true, positionClass: 'toast-top-right', timeOut: 3500 };
        exigerConnexionSinonRediriger();

        if (JSON.parse(localStorage.getItem('utilisateur') || '{}').role !== 'admin') window.location.href = 'agent/accueil.php';

        // ---
        // En-tête : identité de l'utilisateur connecté
        // ---
        const utilisateurConnecte = JSON.parse(localStorage.getItem('utilisateur') || '{}');
        const prenomUtilisateur = utilisateurConnecte.prenom || 'Admin';
        const nomUtilisateur = utilisateurConnecte.nom || '';
        const initiales = (prenomUtilisateur[0] || 'A') + (nomUtilisateur[0] || 'D');

        document.getElementById('avatar-initiales').textContent = initiales.toUpperCase();
        document.getElementById('topbar-nom-utilisateur').textContent = prenomUtilisateur;
        document.getElementById('topbar-role-utilisateur').textContent = 'Administrateur';

        // Date du jour affichée dans la topbar
        document.getElementById('date-du-jour').textContent =
            new Date().toLocaleDateString('fr-FR', { day: '2-digit', month: 'long', year: 'numeric' });


        let graphiqueCA, graphiqueResaStatut, graphiqueCategorie;
        function formaterMontant(m) { return Number(m).toLocaleString('fr-FR') + ' F CFA'; }
        function premierJourMois() { const d = new Date(); d.setDate(1); return d.toISOString().split('T')[0]; }
        function dernierJourMois() { const d = new Date(); d.setMonth(d.getMonth()+1, 0); return d.toISOString().split('T')[0]; }
        $('#periode-debut').val(premierJourMois());
        $('#periode-fin').val(dernierJourMois());

        function afficherTendance(sel, val, positifEstBon = true) {
            if (val === null || isNaN(val)) { $(sel).html('<span class="contexte">vs période précédente</span>'); return; }
            const hausse = val >= 0;
            const classe = (hausse === positifEstBon) ? 'hausse' : 'baisse';
            $(sel).html(`<i class="fa-solid fa-arrow-${hausse?'up':'down'}"></i> ${Math.abs(val)}% <span class="contexte">vs période précédente</span>`).attr('class', 'tendance ' + classe);
        }

        function chargerStatsGlobales(d, f) {
            $.getJSON('api/rapports_stats_globales.php', { date_debut: d, date_fin: f }, function (r) {
                $('#stat-reservations').text(r.reservations_totales);
                $('#stat-locations').text(r.locations_realisees);
                $('#stat-ca').text(formaterMontant(r.chiffre_affaires));
                $('#stat-paiements').text(formaterMontant(r.paiements_recus));
                $('#stat-occupation').text(r.taux_occupation + ' %');
                afficherTendance('#tendance-reservations', r.variation_reservations);
                afficherTendance('#tendance-locations', r.variation_locations);
                afficherTendance('#tendance-ca', r.variation_ca);
                afficherTendance('#tendance-paiements', r.variation_paiements);
                afficherTendance('#tendance-occupation', r.variation_occupation);
            }).fail(() => toastr.error("Impossible de charger les statistiques.", 'Erreur'));
        }

        function chargerEvolutionCA(d, f) {
            $.getJSON('api/rapports_evolution_ca.php', { date_debut: d, date_fin: f }, function (r) {
                $('#legende-periodes').text(`${r.libelle_periode_actuelle} vs ${r.libelle_periode_precedente}`);
                if (graphiqueCA) graphiqueCA.destroy();
                graphiqueCA = new Chart(document.getElementById('graphique-evolution-ca'), {
                    type: 'line',
                    data: { labels: r.labels, datasets: [
                        { label: r.libelle_periode_actuelle, data: r.periode_actuelle, borderColor: '#6366f1', backgroundColor: 'rgba(99,102,241,0.08)', fill: true, tension: 0.35 },
                        { label: r.libelle_periode_precedente, data: r.periode_precedente, borderColor: '#c7cadf', borderDash: [4,4], fill: false, tension: 0.35, pointRadius: 2 },
                    ]},
                    options: { plugins: { legend: { display: false } }, scales: { y: { ticks: { callback: v => (v/1000)+'k' } } } }
                });
            }).fail(() => toastr.error("Impossible de charger le graphique.", 'Erreur'));
        }

        function chargerRepartitionReservations(d, f) {
            $.getJSON('api/rapports_repartition_reservations.php', { date_debut: d, date_fin: f }, function (r) {
                $('#total-resa-periode').text(r.total);
                const rep = r.repartition;
                $('#legende-a-venir').text(rep.a_venir); $('#legende-en-cours').text(rep.en_cours);
                $('#legende-terminee').text(rep.terminee); $('#legende-annulee').text(rep.annulee);
                if (graphiqueResaStatut) graphiqueResaStatut.destroy();
                graphiqueResaStatut = new Chart(document.getElementById('graphique-resa-statut'), {
                    type: 'doughnut',
                    data: { datasets: [{ data: [rep.a_venir, rep.en_cours, rep.terminee, rep.annulee], backgroundColor: ['#2563eb','#16a34a','#f59e0b','#9ca3af'], borderWidth: 0 }] },
                    options: { cutout: '72%', plugins: { legend: { display: false } } }
                });
            }).fail(() => toastr.error("Impossible de charger la répartition.", 'Erreur'));
        }

        function chargerCAParCategorie(d, f) {
            $.getJSON('api/rapports_repartition_ca_categorie.php', { date_debut: d, date_fin: f }, function (r) {
                $('#total-ca-categorie').text(formaterMontant(r.total));
                const couleurs = ['#2563eb','#16a34a','#f59e0b','#7c3aed','#dc2626','#0d9488'];
                const $legende = $('#legende-ca-categorie').empty();
                r.data.forEach((c,i) => $legende.append(`<li><span><span class="puce" style="background:${couleurs[i%couleurs.length]};"></span>${c.categorie}</span><span class="valeur">${formaterMontant(c.ca)} (${c.pourcentage}%)</span></li>`));
                if (graphiqueCategorie) graphiqueCategorie.destroy();
                graphiqueCategorie = new Chart(document.getElementById('graphique-ca-categorie'), {
                    type: 'doughnut',
                    data: { datasets: [{ data: r.data.map(c => c.ca), backgroundColor: couleurs, borderWidth: 0 }] },
                    options: { cutout: '72%', plugins: { legend: { display: false } } }
                });
            }).fail(() => toastr.error("Impossible de charger la répartition par catégorie.", 'Erreur'));
        }

        function chargerTopVehicules(d, f) {
            $.getJSON('api/rapports_top_vehicules.php', { date_debut: d, date_fin: f }, function (r) {
                const $corps = $('#corps-top-vehicules').empty();
                if (r.data.length === 0) { $corps.append('<tr><td colspan="5" class="text-center text-muted py-3">Aucune donnée sur cette période.</td></tr>'); return; }
                r.data.forEach((v,i) => $corps.append(`<tr><td>${i+1}</td><td><i class="fa-solid fa-car-side text-muted"></i> ${v.vehicule}</td><td><span class="badge-cat-mini">${v.categorie}</span></td><td>${v.locations}</td><td>${formaterMontant(v.ca_genere)}</td></tr>`));
            }).fail(() => toastr.error("Impossible de charger le classement.", 'Erreur'));
        }

        function chargerIndicateurs(d, f) {
            $.getJSON('api/rapports_indicateurs_cles.php', { date_debut: d, date_fin: f }, function (r) {
                const $liste = $('#liste-indicateurs').empty();
                const items = [
                    { icone:'fa-hourglass-half', bg:'#e8f0fe', fg:'#2563eb', titre:'Durée moyenne de location', valeur:r.duree_moyenne_jours+' jours', delta:r.delta_duree, unite:' jour', positif:true },
                    { icone:'fa-road', bg:'#e9f7ef', fg:'#16a34a', titre:'Distance moyenne par location', valeur:r.distance_moyenne_km+' km', delta:r.delta_distance, unite:' km', positif:true },
                    { icone:'fa-sack-dollar', bg:'#fef3e2', fg:'#d97706', titre:'Panier moyen', valeur:formaterMontant(r.panier_moyen), delta:r.delta_panier, unite:' F CFA', positif:true },
                    { icone:'fa-ban', bg:'#fdeaea', fg:'#dc2626', titre:"Taux d'annulation", valeur:r.taux_annulation+' %', delta:r.delta_annulation, unite:' pts', positif:false },
                ];
                items.forEach(i => {
                    const hausse = i.delta >= 0;
                    const bon = hausse === i.positif;
                    $liste.append(`<div class="item-indicateur"><div class="icone-indicateur" style="background:${i.bg}; color:${i.fg};"><i class="fa-solid ${i.icone}"></i></div><div>${i.titre}</div><div class="valeur-indicateur"><div class="principal">${i.valeur}</div><div class="delta ${bon?'hausse-positive':'hausse-negative'}"><i class="fa-solid fa-arrow-${hausse?'up':'down'}"></i> ${Math.abs(i.delta)}${i.unite}</div></div></div>`);
                });
                $liste.append(`<div class="item-indicateur"><div class="icone-indicateur" style="background:#f3f4f6; color:#6b7280;"><i class="fa-solid fa-screwdriver-wrench"></i></div><div>Véhicules en maintenance</div><div class="valeur-indicateur"><div class="principal">${r.vehicules_maintenance}</div><div class="delta" style="color:#9ca3af;">${r.pct_parc_maintenance}% du parc</div></div></div>`);
            }).fail(() => toastr.error("Impossible de charger les indicateurs.", 'Erreur'));
        }

        function chargerToutLeRapport() {
            const d = $('#periode-debut').val(), f = $('#periode-fin').val();
            chargerStatsGlobales(d, f); chargerEvolutionCA(d, f); chargerRepartitionReservations(d, f);
            chargerCAParCategorie(d, f); chargerTopVehicules(d, f); chargerIndicateurs(d, f);
        }
        $('#btn-appliquer-periode').on('click', chargerToutLeRapport);
        chargerToutLeRapport();
    </script>
</body>
</html>
