<?php
// Paramètres de connexion Oracle (Localhost pour le moment)
// Pour le rendu final, vous remplacerez par : define("MYHOST","10.1.16.56/oracle2"); etc.
// En passant par Docker, le nom d'hôte de la DB est le nom de son service ("oracle-db")
define("MYHOST","oracle-db:1521/FREEPDB1"); 
define("MYUSER","sae_zoo");                 // Votre nom d'utilisateur Oracle
define("MYPASS","MdPoracleflo83!");         // Votre mot de passe Oracle
?>

