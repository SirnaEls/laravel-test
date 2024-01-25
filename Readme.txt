Contexte : projet Laravel Octopus (webview)


Ressources : 
 - 1 fichier "octopus.sql" décrivant les structures des tables SQL pour les paniers DLC et les produits de panier DLC
 - 4 fichiers PHP :
	> 2 modèles : panier DLC et produit de panier DLC (un panier peut contenir des produits, qui sont utilisés pour imprimer des étiquettes de DLC secondaire)
	> 1 contrôleur utilisé pour la partie back-office des paniers DLC
	> 1 vue : le formulaire pour éditer les propriétés d'un panier DLC


Exercices (cas concrets) :

	Objectif 1 - Correction de bug :
	 - Page concernée : le formulaire d'édition en back-office d'un panier DLC (seul fichier PHP dans views/)
	 - Problème : quand on sélectionne ou déselectionne une des 2 options "Afficher les images des produits" ou "Ajouter l'heure précise sur l'étiquette" et qu'on valide le formulaire, les 2 options prennent la valeur choisie.
	 - Exemple : 
		1. J'ouvre la page : les 2 options sont décochées
		2. Je coche l'option "Afficher les images des produits" puis je valide
		3. Je retourne sur la page : les 2 options sont cochées
		4. Je décoche l'option "Ajouter l'heure précise sur l'étiquette" puis je valide
		5. Je retourne sur la page : les 2 options sont décochées
		
	Objectif 2 - Gestion d'un type d'étiquette par panier :
	 - Besoin : 
		> On veut pouvoir gérer plusieurs formats d'étiquette au niveau d'un panier DLC (modèle "DlcBasket") : soit "DLC", soit "Vente libre service".
		> Dans le cas d'un format "Vente libre service", on veut pouvoir choisir un sous-format "Simple" ou "Complet"
		> Par défaut, le format doit être "DLC" pour les nouveaux paniers créés et ceux existants
		> Si un utilisateur sélectionne le format "Vente libre service" dans le formulaire, il doit pouvoir choisir entre les sous-formats "Simple" et "Complet" (choix obligatoire, mais le champ doit être vide par défaut : on veut que l'utilisateur fasse le choix consciemment)
	 - À faire :
		1. Rédiger le fichier de migration de la BDD via Laravel pour modifier la table "dlc_basket" pour répondre au besoin
		2. Modifier le formulaire et tous les fichiers nécessaires pour prendre en compte cette modification
		
	Objectif 3 - Script/commande :
	 - Besoin : identifier les paniers DLC (DlcBasket) existants qui n'ont aucun produit (DlcBasketProduct)
	 - À faire : requête SQL ou fichier PHP Command (Laravel) pour obtenir la liste voulue