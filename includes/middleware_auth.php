<?php
/**
 * Middleware d'authentification JWT
 */

require_once __DIR__ . '/jwt_helper.php';
require_once __DIR__ . '/../config/db.php';

function exigerAuthentification(): array
{
    $token = recupererTokenDepuisRequete();
    $donnees = validerToken($token);

    if (!$donnees) {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(["success" => false, "message" => "Authentification requise ou session expirée."]);
        exit;
    }

    // IMPORTANT : le JWT prouve seulement qu'on avait un compte valide AU MOMENT de la connexion.
    // On vérifie ici l'état RÉEL du compte en base à chaque requête, pour qu'une désactivation
    // (page Utilisateurs) coupe l'accès immédiatement, sans attendre l'expiration du token (8h).
    $pdo = getPDO();
    $stmt = $pdo->prepare("SELECT etat, role FROM Utilisateurs WHERE id_utilisateur = :id");
    $stmt->execute(["id" => $donnees['id_utilisateur']]);
    $utilisateur = $stmt->fetch();

    if (!$utilisateur || $utilisateur['etat'] !== 'actif') {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(["success" => false, "message" => "Compte désactivé ou introuvable."]);
        exit;
    }

    // Le rôle vient de la base (source de vérité), pas du token, au cas où il aurait changé depuis la connexion
    $donnees['role'] = $utilisateur['role'];
    return $donnees;
}

function exigerRoleParmi(array $rolesAutorises): array
{
    $utilisateur = exigerAuthentification();
    if (!in_array($utilisateur['role'], $rolesAutorises, true)) {
        http_response_code(403);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(["success" => false, "message" => "Accès réservé au personnel autorisé."]);
        exit;
    }
    return $utilisateur;
}

function exigerRole(string $roleRequis): array
{
    return exigerRoleParmi([$roleRequis]);
}