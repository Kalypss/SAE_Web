<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Mot de passe oublié</title>
    <style>
        .centre {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            width: 100%;
            height: 100vh;
            text-align: center;
            background: linear-gradient(135deg, #1A3A3A, #a997df);
        }
        h1 { margin-bottom: 20px; font-size: 3rem; }
        p { font-size: 1.5rem; max-width: 600px; line-height: 1.5; margin-bottom: 40px; }
        .back-btn {
            background-color: white;
            color: black;
            padding: 15px 30px;
            border-radius: 20px;
            text-decoration: none;
            font-weight: bold;
            transition: 0.3s;
        }
        .back-btn:hover { background-color: #333; color: white; }
    </style>
</head>
<body>
    <div class="centre">
        <h1>Mot de passe oublié ?</h1>
        <p>Tu n'avais qu'à t'en souvenir !<br><br>Le support informatique est actuellement en grève à durée indéterminée. Reviens quand tu auras retrouvé la mémoire.</p>
        <a href="index.php" class="back-btn">← Retourner pleurer devant l'accueil</a>
    </div>
</body>
</html>
