<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/jwt_config.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;


function genererToken(array $donneesUtilisateur): string
{
    $maintenant = time();

    $payload = [
        'iat' => $maintenant,
        'exp' => $maintenant + JWT_DUREE_VALIDITE,
        'sub' => $donneesUtilisateur['id_utilisateur'],
        'data' => [
            'id_utilisateur' => $donneesUtilisateur['id_utilisateur'],
            'nom' => $donneesUtilisateur['nom'],
            'prenom' => $donneesUtilisateur['prenom'],
            'email' => $donneesUtilisateur['email'],
            'role' => $donneesUtilisateur['role'],
        ],
    ];

    return JWT::encode($payload, JWT_SECRET, JWT_ALGO);
}


function validerToken(?string $token): ?array
{
    if (!$token) {
        return null;
    }

    try {
        $decode = JWT::decode($token, new Key(JWT_SECRET, JWT_ALGO));
        return (array) $decode->data;
    } catch (\Exception $e) {
        return null;
    }
}


function recupererTokenDepuisRequete(): ?string
{
    $header = null;

    if (function_exists('getallheaders')) {
        $headers = getallheaders();
        $header = $headers['Authorization'] ?? $headers['authorization'] ?? null;
    }

    if (!$header && isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $header = $_SERVER['HTTP_AUTHORIZATION'];
    }

    if ($header && preg_match('/Bearer\s+(.*)$/i', $header, $matches)) {
        return trim($matches[1]);
    }

    return $_COOKIE['jwt_token'] ?? null;
}
