<?php
/**
 * Fichier centralisé des permissions (RBAC Mapping).
 * Retourne un tableau associatif : ['/chemin/du/fichier.php' => ['role1', 'role2']]
 * Remarque : 'tous' signifie tout utilisateur "connecté".
 */
return [
    // Authentification & Base
    '/public_html/index.php' => ['tous'],       // Géré différemment car public
    '/public_html/logout.php' => ['tous'],
    '/public_html/dashboard.php' => ['tous'],
    '/public_html/home/profil/view.php' => ['tous'],
    '/public_html/home/profil/mot-de-passe.php' => ['tous'],
    '/public_html/home/recherche.php' => ['tous'],

    // Animaux
    '/public_html/home/animaux/index.php' => ['gérant', 'veterinaire', 'soignant'],
    '/public_html/home/animaux/ajouter.php' => ['gérant', 'veterinaire'],
    '/public_html/home/animaux/view.php' => ['gérant', 'veterinaire', 'soignant'],
    '/public_html/home/animaux/modifier.php' => ['gérant', 'veterinaire'],
    '/public_html/home/animaux/supprimer.php' => ['gérant'],

    // Soins & Alimentation
    '/public_html/home/soins/index.php' => ['gérant', 'veterinaire'],
    '/public_html/home/soins/ajouter.php' => ['gérant', 'veterinaire'],
    '/public_html/home/soins/view.php' => ['gérant', 'veterinaire'],
    '/public_html/home/alimentation/ajouter.php' => ['gérant', 'soignant'],

    // Espèces
    '/public_html/home/especes/index.php' => ['gérant', 'veterinaire'],
    '/public_html/home/especes/ajouter.php' => ['gérant'],
    '/public_html/home/especes/view.php' => ['gérant', 'veterinaire'],
    '/public_html/home/especes/modifier.php' => ['gérant'],
    '/public_html/home/cohabitations/index.php' => ['gérant', 'veterinaire'],

    // Infrastructure (Enclos, Zones, Réparations)
    '/public_html/home/enclos/index.php' => ['gérant', 'veterinaire', 'technique'],
    '/public_html/home/enclos/ajouter.php' => ['gérant', 'technique'],
    '/public_html/home/enclos/view.php' => ['gérant', 'veterinaire', 'technique'],
    '/public_html/home/enclos/modifier.php' => ['gérant', 'technique'],
    '/public_html/home/particularites/index.php' => ['gérant', 'technique'],
    '/public_html/home/zones/index.php' => ['gérant'],
    '/public_html/home/reparations/index.php' => ['gérant', 'technique'],
    '/public_html/home/reparations/ajouter.php' => ['gérant', 'technique'],
    '/public_html/home/prestataires/index.php' => ['gérant', 'technique'],
    '/public_html/home/prestataires/ajouter.php' => ['gérant'],
    // Personnel
    '/public_html/home/personnel/index.php' => ['gérant'],  
    '/public_html/home/personnel/ajouter.php' => ['gérant'],
    '/public_html/home/personnel/view.php' => ['gérant'],
    '/public_html/home/personnel/modifier.php' => ['gérant'],
    '/public_html/home/personnel/historique.php' => ['gérant'],
    // Public / Commercial (Visiteurs, Parrainages, Boutiques...)
    '/public_html/home/visiteurs/index.php' => ['gérant', 'boutique'],
    '/public_html/home/visiteurs/view.php' => ['gérant', 'boutique'],
    '/public_html/home/visiteurs/ajouter.php' => ['gérant', 'boutique'],
    '/public_html/home/parrainages/index.php' => ['gérant', 'boutique', 'soignant'],
    '/public_html/home/parrainages/ajouter.php' => ['gérant', 'boutique'],
    '/public_html/home/prestations/index.php' => ['gérant', 'boutique'],
    '/public_html/home/boutiques/index.php' => ['gérant', 'boutique'],
    '/public_html/home/boutiques/view.php' => ['gérant', 'boutique'],
    '/public_html/home/chiffre-affaires/ajouter.php' => ['gérant', 'boutique']
];