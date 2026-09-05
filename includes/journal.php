<?php
/**
 * Helper - Journal d'activité
 *
 * À inclure (require_once) dans les endpoints qui créent/modifient/suppriment
 * une ressource (api/vehicule_creer.php, api/reservation_creer.php, etc.)
 * puis à appeler juste après l'action réussie.
 *
 * Exemple d'utilisation dans un endpoint existant :
 *
 *   require_once __DIR__ . '/../includes/journal.php';
 *   // ... insertion du véhicule en base, $idVehicule connu ...
 *   enregistrerActivite($pdo, $utilisateurConnecte, 'creation', 'vehicule', $idVehicule,
 *       "Ajout du véhicule {$immatriculation}");
 *
 * ⚠️ $utilisateurConnecte doit être un tableau contenant au minimum
 * id_utilisateur, prenom, nom, role. Si votre middleware_auth.php expose déjà
 * ces infos sous une autre forme (ex: $_SESSION['utilisateur'], une variable
 * globale, ou un objet renvoyé par exigerAuthentification()), adaptez
 * simplement l'appel pour lui passer ce tableau - la fonction elle-même n'a
 * pas besoin de changer.
 */

function enregistrerActivite(PDO $pdo, $utilisateurConnecte, string $action, string $typeEntite, ?int $idEntite, string $description): void
{
    $actionsValides = ['creation', 'modification', 'suppression', 'autre'];
    if (!in_array($action, $actionsValides, true)) {
        $action = 'autre';
    }

    $idUtilisateur = $utilisateurConnecte['id_utilisateur'] ?? null;
    $nomComplet = trim(($utilisateurConnecte['prenom'] ?? '') . ' ' . ($utilisateurConnecte['nom'] ?? ''));
    if ($nomComplet === '') {
        $nomComplet = 'Utilisateur inconnu';
    }
    $role = $utilisateurConnecte['role'] ?? null;

    try {
        $stmt = $pdo->prepare(
            "INSERT INTO Journal_activite (id_utilisateur, nom_utilisateur, role_utilisateur, action, type_entite, id_entite, description)
             VALUES (:id_utilisateur, :nom_utilisateur, :role_utilisateur, :action, :type_entite, :id_entite, :description)"
        );
        $stmt->execute([
            ':id_utilisateur'   => $idUtilisateur,
            ':nom_utilisateur'  => $nomComplet,
            ':role_utilisateur' => $role,
            ':action'           => $action,
            ':type_entite'      => $typeEntite,
            ':id_entite'        => $idEntite,
            ':description'      => $description,
        ]);
    } catch (Throwable $e) {
        // On n'interrompt jamais l'action principale (ex: création d'un véhicule)
        // si la journalisation échoue. On log simplement l'erreur côté serveur.
        error_log('Journal_activite : ' . $e->getMessage());
    }
}
