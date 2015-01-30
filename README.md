Clementine Framework : module CRUD
==================================


Présentation
------------

Ce module fournit des formulaires (et quelques fonctionnalités supplémentaires) permettant d'interagir avec la base de données. 
Il est capable de gérer les liens entre les tables, les clés primaires ou étrangères, 
et permet de nombreuses adaptations par le biais des surcharges. Il n'utilise pas de code généré. 
Il s'adapte automatiquement lorsque la base de données évolue.

*On peut choisir de n'utiliser ce module que pour la partie modèle du MVC, auquel cas on s'en servira comme d'un ORM.*

**Formulaires créé :**
- creation et mise à jour
- suppression
- affichage
- listing avec tri par colonnes, pagination et moteur de recherche, tout en AJAX (si vous le voulez)
- export des listings en fichier XLS (listings complets ou résultats de recherches)
- flux RSS (bientôt ? dev à terminer...)

*Bien entendu tout est fait pour que tous reste surchargeable.*

**Types de champs**

Le module CRUD proposera par défaut des éléments HTML adaptés en fonction du type SQL des champs :
- checkbox
- select
- textarea
- password
- radio
- hidden
- html non échappé
- file (avec upload en AJAX, barres de progression, formats autorisés, génération de miniatures, protection de l'URL d'accès aux fichiers)
- date (avec datepicker)
- *mettez ici ce que vous voulez* : vous pouvez surcharger les types champ par champ ou définir vos propres mappings

**Autres possibilités**

Il permet aussi de :
- choisir l'ordre des champs, leur affichage ou non, leurs intitulés, la façon dont ils sont représentés au niveau HTML
- surcharger le nettoyage des champs, les contrôles d'erreurs
- définir des champs personnalisés (notamment au niveau SQL)
- choisir les champs et tables à ne jamais modifier


Utilisation
-----------

* **Installer le module `crud`, en le déclarant comme dépendance du site**

Ajouter au fichier `app/local/site/etc/modules.ini` :

```ini
[depends_1]
crud=4
```

Il suffit ensuite de lancer l'installeur.

* **Créer un module dérivé de CRUD**

Ajouter au fichier `app/local/site/etc/config.ini` :

```ini
; exemple pour un module de gestion d'annonces
[clementine_inherit]
annonce=crud
```

* **Définir les tables qui doivent être gérées par CRUD**

Créer un fichier `app/local/site/model/siteAnnonceModel.php` :

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

Mais on peut aller beaucoup plus loin. 

*To be continued...*
---

**Affichage**

- Renommer un champ
- Modifier l'ordre des champs
- Gestion d'erreurs et champs obligatoires
- Types de champs : changer pour un type existant, créer un nouveau type, champs custom...
- Surcharger les formulaires : formulaire complet, éléments de formulaires, types de champs, champ spécifique, traitements JS/AJAX...

**Structures complexes**

- Gérer plusieurs tables et leurs relations : 
 + inner join
 + left join, group by et tables en lecture seule
