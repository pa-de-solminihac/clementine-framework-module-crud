clementine-framework-module-crud
================================

Ce module fournit des formulaires permettant de gérer la base de données. 
Il est capable de gérer les liens entre les tables, les clés primaires ou étrangères, 
et permet de nombreuses adaptations par le biais des surcharges. Il n'utilise pas de code généré pour pouvoir 
évoluer automatiquement lorsque la base de données évolue.

Formulaires gérés :
- creation
- mise à jour
- suppression
- affichage
- listing avec pagination et recherche en AJAX, exportable en fichier XLS
- flux RSS (bientôt ? dev à terminer)

Il permet de choisir l'ordre des champs, leur affichage ou non, leurs intitulés, la façon dont ils sont présentés.
Champs custom
Upload en AJAX avec barre de progression
Surcharger le nettoyage des champs, les contrôles d'erreurs
