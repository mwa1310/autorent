<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/middleware_auth.php';
$pdo = getPDO();

exigerRoleParmi(['admin']);

$d = json_decode(file_get_contents('php://input'), true);
$nom = trim($d['nom'] ?? '');
$prenom = trim($d['prenom'] ?? '');
$email = trim($d['email'] ?? '');
$motDePasse = (string) ($d['mot_de_passe'] ?? '');
$role = trim($d['role'] ?? '');

$erreurs = [];
if ($nom === '') $erreurs[] = "Le nom est obligatoire.";
if ($prenom === '') $erreurs[] = "Le prénom est obligatoire.";
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $erreurs[] = "Email invalide.";
if (mb_strlen($motDePasse) < 6) $erreurs[] = "Le mot de passe doit contenir au moins 6 caractères.";
if (!in_array($role, ['admin', 'agent'], true)) $erreurs[] = "Rôle invalide.";

if (!empty($erreurs)) { http_response_code(422); echo json_encode(["success" => false, "message" => implode(' ', $erreurs)]); exit; }

$stmtVerif = $pdo->prepare("SELECT id_utilisateur FROM Utilisateurs WHERE email = :email");
$stmtVerif->execute(["email" => $email]);
if ($stmtVerif->fetch()) { http_response_code(409); echo json_encode(["success" => false, "message" => "Cet email est déjà utilisé."]); exit; }

try {
    $stmt = $pdo->prepare(
        "INSERT INTO Utilisateurs (nom, prenom, email, mot_de_passe, role, etat) VALUES (:nom, :prenom, :email, :mdp, :role, 'actif')"
    );
    $stmt->execute(["nom" => $nom, "prenom" => $prenom, "email" => $email, "mdp" => password_hash($motDePasse, PASSWORD_DEFAULT), "role" => $role]);
    echo json_encode(["success" => true, "message" => "Compte créé.", "id_utilisateur" => $pdo->lastInsertId()]);
} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur : " . $e->getMessage()]);
}
