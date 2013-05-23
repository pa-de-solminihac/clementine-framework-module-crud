Clementine Framework : module CRUD
==================================


Description
-----------

Ce module fournit des formulaires permettant de gérer la base de données. 
Il est capable de gérer les liens entre les tables, les clés primaires ou étrangères, 
et permet de nombreuses adaptations par le biais des surcharges. Il n'utilise pas de code généré pour pouvoir 
évoluer automatiquement lorsque la base de données évolue.

On peut choisir de n'utiliser ce module que pour la partie modèle du MVC, auquel cas on s'en servira comme d'un ORM.

Formulaires gérés :
- creation et mise à jour
- suppression
- affichage
- listing avec tri par colonnesm pagination et recherche, tout en AJAX (si vous le voulez), exportable en fichier XLS
- flux RSS (bientôt ? dev à terminer)

Bien entendu tout est fait pour que tous reste surchargeable.

Le module CRUD proposera par défaut des éléments HTML adaptés en fonction du type SQL des champs :
- checkbox
- select
- textarea
- password
- radio
- hidden
- html non échappé
- file (avec upload en AJAX, barres de progression, formats autorisés, génération de miniatures)
- date (avec datepicker)
- mettez ici ce que vous voulez : vous pouvez surcharger les types champ par champ ou définir vos propres mappings

Il permet aussi de :
- choisir l'ordre des champs, leur affichage ou non, leurs intitulés, la façon dont ils sont représentés au niveau HTML
- surcharger le nettoyage des champs, les contrôles d'erreurs
- définir des champs personnalisés (notamment au niveau SQL)
- choisir les champs et tables à ne jamais modifier


Utilisation
-----------

* Créer un module dérivé de CRUD, en utilisant l'adoption 

Ajouter au fichier app/local/site/etc/config.ini :

```ini
; exemple pour un module de gestion d'annonces
[clementine_inherit]
annonce=crud
```

* Définir les tables qui doivent être gérées par CRUD

Créer un fichier app/local/site/model/siteAnnonceModel.php :

```php
class siteAnnonceModel extends siteAnnonceModel_Parent /* extends CrudModel */
{
    public function _init($params = null)
    {
        $this->tables = array(
            'annonce' => ''
        );
}
```

C'est tout.

Mais on peut aller beaucoup plus loin. _To be continued..._
