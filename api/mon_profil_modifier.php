<?php
/**
 * Modification du profil personnel. Volontairement limité au nom/prénom/email :
 * ni le mot de passe ni le rôle ne sont modifiables ici — la gestion des mots
 * de passe et des rôles reste exclusivement une action admin, via la page Utilisateurs.
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/middleware_auth.php';
$pdo = getPDO();

$utilisateur = exigerAuthentification();

$d = json_decode(file_get_contents('php://input'), true);
$nom = trim($d['nom'] ?? '');
$prenom = trim($d['prenom'] ?? '');
$email = trim($d['email'] ?? '');

$erreurs = [];
if ($nom === '') $erreurs[] = "Le nom est obligatoire.";
if ($prenom === '') $erreurs[] = "Le prénom est obligatoire.";
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $erreurs[] = "Email invalide.";
if (!empty($erreurs)) { http_response_code(422); echo json_encode(["success" => false, "message" => implode(' ', $erreurs)]); exit; }

// Email déjà pris par un AUTRE compte ?
$stmtVerif = $pdo->prepare("SELECT id_utilisateur FROM Utilisateurs WHERE email = :email AND id_utilisateur != :id");
$stmtVerif->execute(["email" => $email, "id" => $utilisateur['id_utilisateur']]);
if ($stmtVerif->fetch()) { http_response_code(409); echo json_encode(["success" => false, "message" => "Cet email est déjà utilisé."]); exit; }

try {
    $stmt = $pdo->prepare("UPDATE Utilisateurs SET nom=:nom, prenom=:prenom, email=:email WHERE id_utilisateur=:id");
    $stmt->execute(["nom" => $nom, "prenom" => $prenom, "email" => $email, "id" => $utilisateur['id_utilisateur']]);
    echo json_encode(["success" => true, "message" => "Profil mis à jour."]);
} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur : " . $e->getMessage()]);
}
