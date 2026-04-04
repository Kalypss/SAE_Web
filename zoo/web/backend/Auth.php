<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/Security.php';

class Auth {
    /**
     * Protège une page en vérifiant si l'utilisateur est connecté et possède le bon rôle.
     * @param array $allowedRoles Liste des rôles autorisés (ex: ['gérant', 'veterinaire'])
     */
    public static function checkAccess(array $allowedRoles) {
        $security = new Security();
        $ip = $_SERVER['REMOTE_ADDR'];
        $uri = $_SERVER['REQUEST_URI'];

        // 1. Vérification si connecté : Si l'utilisateur n'a pas de session valide, on clear tout et on renvoie à la connexion.
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
            $security->logAction($ip, 'ANONYMOUS', "ACCES REFUSE: Non authentifié vers $uri");
            $_SESSION = array();
            session_destroy();
            header('Location: /public_html/index.php'); // Remplacer par l'URL root
            exit;
        }

        // 2. Vérification du rôle
        $userRole = $_SESSION['role'];
        if (!in_array($userRole, $allowedRoles) && !in_array('tous', $allowedRoles)) {
            // Le statut "gérant" peut théoriquement tout voir d'après les spécifications, on peut l'inclure d'office:
            if ($userRole !== 'gérant') {
                $username = $_SESSION['nom'] ?? 'Unk';
                $security->logAction($ip, $username, "ACCES REFUSE: Role $userRole vers $uri");
                
                header('Location: /public_html/errors/access-error.php');
                exit;
            }
        }
    }
}
?>