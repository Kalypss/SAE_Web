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
        ?>
    </body>
</html>