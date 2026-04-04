<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/Security.php';

class Auth {
    /**
     * Protège une page en vérifiant si l'utilisateur est connecté et possède le
     * bon rôle selon le fichier de configuration des permissions.
     */
    public static function checkAccess() {
        $security = new Security();
        $ip = $_SERVER['REMOTE_ADDR'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // 1. Vérification si l'utilisateur est connecté
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
            $security->logAction($ip, 'ANONYMOUS', "ACCES REFUSE: Non authentifié vers $uri");
            $_SESSION = array();
            session_destroy();
            header('Location: /public_html/index.php');
            exit;
        }

        $userRole = $_SESSION['role'];
        $permissions = require __DIR__ . '/../config/permissions.php';

        // 2. Recherche de l'URI dans le fichier config pour obtenir ses rôles autorisés
        $allowedRoles = [];
        
        // Normalisation de l'URI pour toujours matcher celles avec ou sans /public_html
        $normalizedUri = $uri;
        if (strpos($normalizedUri, '/public_html') !== 0) {
            $normalizedUri = '/public_html' . $uri;
        }

        // Si l'URI demandée a une définition précise 
        if (isset($permissions[$normalizedUri])) {
            $allowedRoles = $permissions[$normalizedUri];
        } else if (isset($permissions[$uri])) {
            $allowedRoles = $permissions[$uri];
        } else {
            // Permet de matcher des URL avec IDs : /public_html/home/animaux/view.php?id=... est envoyé à /public_html/home/animaux/view.php en $uri
            // On peut s'assurer que c'est bien couvert par le parser_url. 
            // Par sécurité, si la page est inconnue => refus.
            $security->logAction($ip, $_SESSION['nom'] ?? 'Unk', "ACCES REFUSE: Page inconnue ($uri)");
            // Redirection vers 403
            header("Location: /public_html/errors/403.php");
            exit;
        }

        // 3. Vérification du rôle
        if (!in_array($userRole, $allowedRoles) && !in_array('tous', $allowedRoles)) {
            // Gérant passe-partout (déjà couvert si 'gérant' est le array, mais sécurité additionnelle)
            if ($userRole !== 'gérant') {
                $username = $_SESSION['nom'] ?? 'Unk';
                $security->logAction($ip, $username, "ACCES REFUSE 403: Role $userRole vers $uri");
                header('Location: /public_html/errors/403.php');
                exit;
            }
        }
    }
}
?>