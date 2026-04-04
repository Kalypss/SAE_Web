<?php
function connex($base,$param)
{
	include($param.".inc.php");
	// Avertissement: $base est gardé pour compatibilité avec vos anciens appels mais la vraie bdd est dans MYHOST
	$idcom = oci_connect(MYUSER, MYPASS, MYHOST);
	if(!$idcom)
	{
		$e = oci_error();
    	echo "<script type=text/javascript>";
		echo "alert('Connexion Impossible : " . addslashes($e['message']) . "')</script>";
	}
	return $idcom;
}
?>

