<?php
$file = 'zoo/web/public_html/index.php';
$content = file_get_contents($file);

$body_start = strpos($content, '<body>');
$body_end = strpos($content, '</body>');

$new_html = <<<HTML
<body>
    <div class="gauche">
        <div class="gauche-content">
            <h2>Le Zoo <span style="font-weight: 300;">"SAE"</span></h2>
            <h3>Formulaire de connexion</h3>
            <p>Notre magnifique Zoo ne vous ouvre absolument pas ses portes, étant donné un manque évident d'ouvrir un Zoo hors d'un ordinateur...</p>
        </div>
    </div>
    <div class="droite">
        <div class="form-container">
            <h1>Connexion</h1>
            <p class="subtitle">Entrez vos identifiants de connexion pour accéder à l'interface.</p>
            
            <?php if (!empty(\$message)): ?>
                <p class="error-msg"><?php echo \$message; ?></p>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="input-group">
                    <label for="username">Nom d'utilisateur</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="input-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" required>
                    <!-- Eye icon placeholder if wanted -->
                </div>
                
                <button type="submit" id="connex">
                    Se connecter
                    <span class="btn-icon">➔</span>
                </button>
            </form>
            
            <a href="mdp_oublie.php" class="forgot-link">Mot de passe oublié ?</a>
            
            <p class="condition">En vous connectant vous acceptez bien évidemment les <a href="trup.php">conditions générales de la vente de votre âme au diable</a></p>
        </div>
    </div>
</body>
HTML;

$content = substr_replace($content, $new_html, $body_start, ($body_end + 7) - $body_start);
file_put_contents($file, $content);
