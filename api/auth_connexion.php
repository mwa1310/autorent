<?php
/**
 * API - Connexion
 * POST /api/auth_connexion.php
 * Body (JSON) : { email, mot_de_passe }
 * Retourne : { success, token, utilisateur: {id_utilisateur, nom, prenom, email, role} }
 */
 
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/db.php';
$pdo = getPDO();
require_once __DIR__ . '/../includes/jwt_helper.php';
 
$donnees = json_decode(file_get_contents('php://input'), true);
 
$email       = trim($donnees['email'] ?? '');
$motDePasse  = (string) ($donnees['mot_de_passe'] ?? '');
 
if ($email === '' || $motDePasse === '') {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Email et mot de passe requis."]);
    exit;
}
 
try {
    $stmt = $pdo->prepare(
        "SELECT id_utilisateur, nom, prenom, email, mot_de_passe, role, etat
         FROM Utilisateurs
         WHERE email = :email"
    );
    $stmt->execute(["email" => $email]);
    $utilisateur = $stmt->fetch();
 
    // Message volontairement générique (ne révèle pas si c'est l'email ou le mot de passe qui est faux)
    if (!$utilisateur || !password_verify($motDePasse, $utilisateur['mot_de_passe'])) {
        http_response_code(401);
        echo json_encode(["success" => false, "message" => "Email ou mot de passe incorrect."]);
        exit;
    }
 
    if ($utilisateur['etat'] !== 'actif') {
        http_response_code(403);
        echo json_encode(["success" => false, "message" => "Ce compte a été désactivé. Contactez un administrateur."]);
        exit;
    }
 
    $token = genererToken([
        'id_utilisateur' => $utilisateur['id_utilisateur'],
        'nom'            => $utilisateur['nom'],
        'prenom'         => $utilisateur['prenom'],
        'email'          => $utilisateur['email'],
        'role'           => $utilisateur['role'],
    ]);
 
    echo json_encode([
        "success" => true,
        "token"   => $token,
        "utilisateur" => [
            "id_utilisateur" => $utilisateur['id_utilisateur'],
            "nom"            => $utilisateur['nom'],
            "prenom"         => $utilisateur['prenom'],
            "email"          => $utilisateur['email'],
            "role"           => $utilisateur['role'],
        ],
    ]);
} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur lors de la connexion : " . $e->getMessage()]);
}