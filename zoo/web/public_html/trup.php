<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Conditions Générales de Vente</title>
    <style>
        body {
            background-color: #f7f7f7;
            color: #333;
            display: block;
            height: auto;
            overflow: auto;
            font-family: 'Poppins', serif;
        }
        .paper-container {
            max-width: 750px;
            margin: 60px auto;
            background: #fff;
            padding: 60px 80px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05),
                        0 1px 3px rgba(0,0,0,0.1);
            position: relative;
            border-radius: 4px;
        }
        .paper-container::before {
            content: '';
            position: absolute;
            top: 0; left: 0; width: 100%; height: 6px;
            background: linear-gradient(90deg, #bfa1f6, #1A3A3A);
            border-radius: 4px 4px 0 0;
        }
        .doc-header {
            text-align: center;
            margin-bottom: 50px;
            border-bottom: 2px solid #eee;
            padding-bottom: 20px;
        }
        .doc-header h1 {
            font-size: 2rem;
            color: #111;
            letter-spacing: -1px;
            margin-bottom: 10px;
            text-transform: uppercase;
            font-weight: 600;
        }
        .doc-header p {
            color: #777;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .article {
            margin-bottom: 35px;
        }
        .article h2 {
            font-size: 1.2rem;
            color: #1A3A3A;
            margin-bottom: 15px;
            font-weight: 600;
            display: flex;
            align-items: center;
        }
        .article h2::before {
            content: "§";
            color: #bfa1f6;
            margin-right: 10px;
            font-size: 1.5rem;
            font-weight: 300;
        }
        .article p, .article ul {
            font-size: 0.95rem;
            line-height: 1.8;
            color: #555;
            text-align: justify;
        }
        .article ul {
            padding-left: 20px;
            margin-top: 10px;
        }
        .article li {
            margin-bottom: 8px;
            padding-left: 5px;
        }
        .stamp {
            margin-top: 60px;
            padding-top: 30px;
            border-top: 1px dashed #ccc;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .signature-box {
            text-align: center;
            color: #000;
        }
        .signature-line {
            width: 200px;
            border-bottom: 1px solid #000;
            margin-top: 40px;
            margin-bottom: 10px;
        }
        .back-btn {
            display: inline-block;
            background-color: #111;
            color: #fff;
            padding: 12px 25px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .back-btn:hover {
            background-color: #bfa1f6;
            color: #111;
            transform: translateY(-2px);
        }
        
        @media (max-width: 768px) {
            .paper-container {
                margin: 20px;
                padding: 40px 30px;
            }
            .stamp {
                flex-direction: column;
                gap: 40px;
            }
        }
    </style>
</head>
<body>
    <div class="paper-container">
        <div class="doc-header">
            <h1>Conditions du site</h1>
            <p>Conditions Générales d'Utilisation & de Vente</p>
        </div>

        <div class="article">
            <h2>Préambule</h2>
            <p>Par la présente tentative de connexion (réussie ou non), l'Utilisateur se soumet de plein gré, et en pleine possession de ses moyens, aux clauses stipulées ci-dessous, réputées valables pour l'éternité et au-delà, sans possibilité de résiliation anticipée, de litige ou de réincarnation favorable.</p>
        </div>

        <div class="article">
            <h2>Transfert de propriété spirituelle</h2>
            <ul>
                <li>Vous acceptez solennellement de céder l'intégralité de votre âme, ainsi que ses potentiels dividendes futurs, de façon permanente au Diable (ci-après légalement désigné comme "L'Administrateur Réseau").</li>
                <li>En cas de rupture de contrat ou de tentative d'utilisation d'un VPN, vous acceptez d'incarner, de plein droit, le premier animal croisé sur l'Hôtel de l'Enclos n°4. L'espèce choisie sera par défaut le "Paresseux", sans aucune dérogation possible formelle.</li>
            </ul>
        </div>

        <div class="article">
            <h2>Conditions Particulières de Télésurveillance</h2>
            <ul>
                <li>Vos moindres faits, gestes et hésitations maladives de curseur sur cette interface sont strictement surveillés depuis les Enfers et journalisés en Base de Données Oracle par un Gérant sous-payé.</li>
                <li>Aucun remboursement, qu'il soit pécuniaire, moral ou occulte, ne sera accordé consécutivement à l'absorption mystique de votre libre-arbitre par notre système back-end.</li>
            </ul>
        </div>

        <div class="article">
            <h2>Cas de Force Majeure</h2>
            <p>Conformément à l'article 666 du Code du Cyber-Enfer, prendre la fuite, éteindre son routeur en panique ou arracher le câble secteur ne constituera en aucun cas une annulation légitime du présent contrat. Le simple fait d'avoir posé les yeux sur la présente documentation engage ipso facto votre descendance sur douze (12) générations.</p>
        </div>

        <div class="stamp">
            <a href="index.php" class="back-btn">← J'ai compris, je signe avec mon sang</a>
            
            <div class="signature-box">
                <div class="signature-line"></div>
                <small>Signature (électronique ou sanguine)</small>
            </div>
        </div>
    </div>
</body>
</html>
