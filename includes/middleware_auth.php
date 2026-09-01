<?php
/**
 * Middleware d'authentification JWT
 */

require_once __DIR__ . '/jwt_helper.php';

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

    return $donnees;
}


// Exige que l'utilisateur connecté ait l'un des rôles fournis.
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
