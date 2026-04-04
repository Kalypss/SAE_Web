<?php
    if (isset($_POST["logout"]) && $_POST["logout"]!=null) {
        session_unset();
        session_destroy();
        header("location:authentif.php");
        exit();
    }
    // changer par commencer par tester si session créer puis l'id 
    // là le train donc pas le temps 
    if (isset($_POST["id"]) && isset($_POST["mdp"]) && $_POST["id"]!= null && $_POST["mdp"]!= null) {
        $id = $_POST["id"];
        $mdp = $_POST["mdp"];
        $hash = hash('sha256',$mdp);
        include "connex.inc.php";
        $idcom = connex("zoo","myparam");
        if ($idcom && ! isset($_SESSION["sql"])) {
            $requete = "SELECT `nom_personnel`, `prenom_personnel`,`type_personnel` FROM `Personnel` WHERE `id_personnel` = $id AND `pwd_personnel` = '$hash'";
            $result = mysqli_query($idcom,$requete);
            if ($ligne = mysqli_fetch_array($result)) {
                session_start();
                $_SESSION["nom"]=$ligne[0];
                $_SESSION["prenom"]=$ligne[1];
                $_SESSION["type"]=$ligne[2];
                $_SESSION["sql"]=$idcom;
            } else {
                header("location:authentif.php");
                exit();
            }
        } else {
            header("location:authentif.php");
            exit();
        }
    } else {
        session_start();
        if (! isset($_SESSION["nom"])) {
            header("location:authentif.php");    
            exit();
        }
    }
?>
<html>
    <link rel="stylesheet" href="style.php">
    <body>
        <?php
            include "fonction.php";
            bandeau();
            
            echo "Welcome to the Zoo !";
        ?>

        <form action="home.php" method="post">
            <input type="text" name="search">
            <input type="submit" value="Entrer" name="enter">
            
        </form>
        
        <?php
            if (isset($_POST["search"]) && $_POST["search"] != null) {
                $type = isset($_SESSION["type"]) ? $_SESSION["type"] : '';
                switch ($type) {
                    case 'soignant':
                        $requete = "SELECT * FROM `Animaux` WHERE nom_animal LIKE '%".$_POST['search']."%' OR regime_alimentaire_animal LIKE '%".$_POST['search']."%'";
                        include "connex.inc.php";
                        $idcom = connex("zoo","myparam");
                        $result = mysqli_query($idcom,$requete);
                        echo "<br><div class=\"test\" >";
                        echo "<table> <tr>";
                        echo "<th>Numéro</th><th>Nom</th><th>Date de naissance</th><th>Poids</th><th>Régime alimentaire</th><th>rfid puce</th><th>Numéro du soignant attribué</th><th>Numéro d'enclo</th><th>Numéro d'espèce</th>";
                        echo "</tr>";
                        while ($ligne = mysqli_fetch_array($result)) {
                            echo "<tr>";
                            echo "<td>$ligne[0]</td><td>$ligne[1]</td><td>$ligne[2]</td><td>$ligne[3]</td><td>$ligne[4]</td><td>$ligne[5]</td><td>$ligne[6]</td><td>$ligne[7]</td><td>$ligne[8]</td>";
                            echo "</tr>";
                        }
                        echo "</table>";
                        break;
                    
                    default:
                        echo "vous n'avez pas de rôle en tant que personnel";
                        break;
                }
            }
        ?>

    </body>
</html>