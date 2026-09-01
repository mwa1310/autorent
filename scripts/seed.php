<?php

require_once __DIR__ . '/config/db.php';

$pdo = getPDO();

$email       = 'admin@autorent.cm';
$motDePasse  = 'Admin123!'; // changez-le après votre premier test
$nom         = 'Admin';
$prenom      = 'AutoRent';
$role        = 'admin';

$hash = password_hash($motDePasse, PASSWORD_BCRYPT);

$stmt = $pdo->prepare("
    INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role, etat, date_creation)
    VALUES (:nom, :prenom, :email, :hash, :role, 'actif', NOW())
");
$stmt->execute([
    ':nom'    => $nom,
    ':prenom' => $prenom,
    ':email'  => $email,
    ':hash'   => $hash,
    ':role'   => $role,
]);

echo "Utilisateur créé avec succès.<br>";
echo "Email : {$email}<br>";
echo "Mot de passe : {$motDePasse}<br>";
echo "<strong>Supprimez ce fichier maintenant.</strong>";