<?php
    // faire page pour chaque truc (animal, soin etc)
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    function soignant(){
        echo "<nav>
            <ul>
                <li><a href=\"home.php\">Home</a></li>    
                <li class=\"deroulant\"><a>Animaux</a>
                    <ul class=\"truc\">
                        <li><a href=\"animal.php\">Liste des Animaux</a></li>
                        <li><a href=\"animal_modif.php\">Modifications Animaux</a></li>
                    </ul>
                </li>
                <li><a href=\"soin.php\">Soins</a></li>
                <li><a href=\"nourriture.php\">Nourriture</a></li>
                <li><a href=\"profile.php\">Profile</a></li>
            </ul>
        </nav>" ;
    }
    function boutique(){
        echo "<nav>
                <ul>
                    <li class=\"deroulant\"><a>animal</a>
                        <ul class=\"truc\">
                            <li><a href=\"animal.php\">Animaux</a></li>
                            <li><a href=\"soin.php\">Chiffre d'affaire</a></li>
                        </ul>
                    </li>
                    <li><a href=\"soin.php\">Chiffre d'affaire</a></li>
                    <li><a href=\"profile.php\">Profile</a></li>
                </ul>
            </nav>" ;
    }


    function profile(){
        echo '<p>'. $_SESSION["nom"].'</p><br>
        <p>'. $_SESSION["prenom"].'</p><br>
        <p>'. $_SESSION["type"].'</p>' ;
    }

    function bandeau(){
        $type = isset($_SESSION["type"]) ? $_SESSION["type"] : '';
        switch ($type) {
                case 'soignant':
                    soignant();
                    break;
                case 'boutique':
                    boutique();
                    break;
                default:
                    soignant();
                    break;
            }
    }

    
    function logout(){
        echo "<form action=\"home.php\" method=\"post\">
            <input type=\"submit\" name=\"logout\" value=\"Log Out\">
        </form>";    
    }
?>