<?php
    session_start();
    if (isset($_POST["logout"]) && $_POST["logout"]!=null) {
        session_unset();
        session_destroy();
        header("location:authentif.php");
    }
?>

<html>
    <link rel="stylesheet" href="style.php">
    <body>
        <?php
            include "fonction.php";
            bandeau();
            $requete = "SELECT * FROM `Animaux`";
            include "connex.inc.php";
            $idcom = connex("zoo","myparam");
            $result = mysqli_query($idcom,$requete);
            echo "<br><br><br><br><br><br>";
            echo "<div class=\"test\" >";
            echo "<table>";
            echo "<tr>";
            echo "<th>Numéro</th><th>Nom</th><th>Date de naissance</th><th>Poids</th><th>Régime alimentaire</th><th>rfid puce</th><th>Numéro du soignant attribué</th><th>Numéro d'enclo</th><th>Numéro d'espèce</th>";
            echo "</tr>";
            while ($ligne = mysqli_fetch_array($result)) {
                echo "<tr>";
                echo "<td>$ligne[0]</td><td>$ligne[1]</td><td>$ligne[2]</td><td>$ligne[3]</td><td>$ligne[4]</td><td>$ligne[5]</td><td>$ligne[6]</td><td>$ligne[7]</td><td>$ligne[8]</td>";
                echo "</tr>";
            }
            echo "</table>";
        ?>
    </body>
</html>