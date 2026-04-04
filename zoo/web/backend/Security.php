<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class Security {
    private string $logFile;
    private int $maxAttempts;
    private int $lockoutTime;

    public function __construct() {
        $this->logFile = __DIR__ . '/logs/connexion.log';
        $this->maxAttempts = 10; // Augmenté pour les phases de test
        $this->lockoutTime = 10; // Réduit à 10 secondes au lieu de 5 minutes
    }

    /**
     * Valide les entrées avec un regex pour éviter l'injection SQL basique
     * (Bien que l'utilisation de requêtes préparées soit le vrai rempart, c'est une couche en plus).
     */
    public function sanitizeInput(string $input): string {
        // Supprime les caractères souvent utilisés dans les injections SQL
        $clean = preg_replace('/[\'";=\-]/', '', $input);
        return trim(htmlspecialchars($clean, ENT_QUOTES, 'UTF-8'));
    }

    /**
     * Anti-spam : Limite de taux (Rate Limiting) basé sur la session
     */
    public function checkRateLimit(string $ip): bool {
        if (!isset($_SESSION['login_attempts'])) {
            $_SESSION['login_attempts'] = [
                'count' => 0,
                'last_attempt' => time()
            ];
        }

        $attempts = &$_SESSION['login_attempts'];
        $timeSinceLast = time() - $attempts['last_attempt'];

        // Si l'utilisateur a dépassé la limite de temps, réinitialiser
        if ($timeSinceLast > $this->lockoutTime) {
            $attempts['count'] = 0;
        }

        if ($attempts['count'] >= $this->maxAttempts) {
            $this->logAction($ip, 'SYSTEM', "BLOCKED (Rate Limit Exceeded)");
            return false;
        }

        return true;
    }

    /**
     * Incrémente les essais de connexion (quand échec)
     */
    public function incrementFailedAttempt() {
        if (isset($_SESSION['login_attempts'])) {
            $_SESSION['login_attempts']['count']++;
            $_SESSION['login_attempts']['last_attempt'] = time();
        }
    }
    
    /**
     * Remet à zéro les essais après réussite
     */
    public function resetAttempts() {
         $_SESSION['login_attempts']['count'] = 0;
    }

    /**
     * Enregistre un log avec toutes les infos de la connexion
     */
    public function logAction(string $ip, string $username, string $status): void {
        $timestamp = date("Y-m-d H:i:s");
        $logData = sprintf("[%s] IP: %s | USER: %s | STATUS: %s" . PHP_EOL, $timestamp, $ip, $username, $status);
        
        file_put_contents($this->logFile, $logData, FILE_APPEND | LOCK_EX);
    }
}
