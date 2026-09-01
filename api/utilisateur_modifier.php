<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/middleware_auth.php';
$pdo = getPDO();

exigerRoleParmi(['admin']);

$id = (int) ($_GET['id'] ?? 0);
$d = json_decode(file_get_contents('php://input'), true);
$nom = trim($d['nom'] ?? '');
$prenom = trim($d['prenom'] ?? '');
$email = trim($d['email'] ?? '');
$role = trim($d['role'] ?? '');
$nouveauMotDePasse = trim((string) ($d['mot_de_passe'] ?? ''));

$erreurs = [];
if ($id <= 0) $erreurs[] = "Identifiant invalide.";
if ($nom === '') $erreurs[] = "Le nom est obligatoire.";
if ($prenom === '') $erreurs[] = "Le prénom est obligatoire.";
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $erreurs[] = "Email invalide.";
if (!in_array($role, ['admin', 'agent'], true)) $erreurs[] = "Rôle invalide.";
if ($nouveauMotDePasse !== '' && mb_strlen($nouveauMotDePasse) < 6) $erreurs[] = "Le nouveau mot de passe doit contenir au moins 6 caractères.";

if (!empty($erreurs)) { http_response_code(422); echo json_encode(["success" => false, "message" => implode(' ', $erreurs)]); exit; }

try {
    if ($nouveauMotDePasse !== '') {
        $stmt = $pdo->prepare("UPDATE Utilisateurs SET nom=:nom, prenom=:prenom, email=:email, role=:role, mot_de_passe=:mdp WHERE id_utilisateur=:id");
        $stmt->execute(["nom" => $nom, "prenom" => $prenom, "email" => $email, "role" => $role, "mdp" => password_hash($nouveauMotDePasse, PASSWORD_DEFAULT), "id" => $id]);
    } else {
        $stmt = $pdo->prepare("UPDATE Utilisateurs SET nom=:nom, prenom=:prenom, email=:email, role=:role WHERE id_utilisateur=:id");
        $stmt->execute(["nom" => $nom, "prenom" => $prenom, "email" => $email, "role" => $role, "id" => $id]);
    }
    echo json_encode(["success" => true, "message" => "Utilisateur modifié."]);
} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur : " . $e->getMessage()]);
}
