<?php
class crudCrudController extends crudCrudController_Parent
{

    /**
     * mapping_to_HTML : defaults to 'input type="text"' in 'crud/update' block
     * 
     */
    public $mapping_to_HTML = array(
        'bit'        => 'checkbox',
        'boolean'    => 'checkbox',
        'enum'       => 'select',
        'set'        => 'select',
        'tinytext'   => 'textarea',
        'tinytext'   => 'textarea',
        'mediumtext' => 'textarea',
        'longtext'   => 'textarea',
        'text'       => 'textarea',
        'password'   => 'password',
        'radio'      => 'radio',
        'html'       => 'html',
        'file'       => 'file',
        'hidden'     => 'hidden');

    protected $_class;
    protected $_crud;

    /**
     * options : permet de desactiver l'autoload des valeurs des 
     *           clés étrangères, utile pour des raisons de performances
     * 
     */
    public $options = array('autoload_foreign_keys_values' => false);

    public function __construct($request)
    {
        // cette classe est destinee a etre surchargee, elle ne doit servir a rien sinon !
        $class = strtolower(substr(get_class($this), 0, -10));
        $this->_class = $class;
        if ($this->_class == 'crud') {
            return false;
        }
        $this->_crud = $this->getModel($this->_class);
        if (!isset($this->data['class'])) {
            $this->data['class'] = $this->_class;
        }
        if (!isset($this->data['fields'])) {
            $to_merge = array();
            $to_merge['fields']  = $this->_crud->fields;
            $this->merge_fields($to_merge);
        }
    }

    /**
     * indexAction : liste des enregistrements
     * 
     * @access public
     * @return void
     */
    public function indexAction($request, $params = null)
    {
        $this->data['return_json'] = 0;
        // cette classe est destinee a etre surchargee, elle ne doit servir a rien sinon !
        if (get_class($this) == 'CrudController') {
            $this->trigger404();
        }
        // autoclick configurable dans le .ini
        $config = $this->getModuleConfig();
        if (!isset($this->data['autoclick'])) {
            $this->data['autoclick'] = 0;
        }
        if (isset($config['autoclick'])) {
            $this->data['autoclick'] = $config['autoclick'];
        }
        // recupere les valeurs postees
        $this->get_unquoted_gpc($params);
        $to_merge = array();
        $to_merge['tables']  = $this->_crud->tables;
        // fonctions pour faciliter la surcharge
        $this->add_fields($params);
        $this->add_fields_index($params);
        $to_merge['metas']   = $this->_crud->metas;
        // gestion des champs masques
        foreach ($this->_crud->metas['hidden_fields'] as $key => $val) {
            if ($val) {
                $to_merge['metas']['hidden_fields'][$key] = 1;
            }
        }
        $to_merge['mapping'] = $this->mapping_to_HTML;
        $this->merge_defaults($to_merge);
        $this->override_fields($params);
        $this->override_fields_index($params);
        $this->hide_sections($params);
        $this->hide_sections_index($params);
        $this->hide_fields($params);
        $this->hide_fields_index($params);
        $this->move_fields($params);
        $this->move_fields_index($params);
        $this->rename_fields($params);
        $this->rename_fields_index($params);
        // export XLS si on ajoute dans l'URL &export_xls&sEcho=1
        if (isset($params['get']['export_xls'])) {
            $this->data['export_xls'] = 1;
            $this->data['return_json'] = 1;
            if (!defined('__NO_DEBUG_DIV__')) {
                define ('__NO_DEBUG_DIV__', true);
            }
            $params['limit'] = false;
            $params['sql_calc_found_rows'] = false;
        }
        // paging
        if (isset($params['get']['iDisplayStart'])) {
            $this->data['return_json'] = 1;
            if (!defined('__NO_DEBUG_DIV__')) {
                define ('__NO_DEBUG_DIV__', true);
            }
            if (isset($params['get']['iDisplayLength']) && ($params['get']['iDisplayLength'] != '-1')) {
                $params['limit'] = (int) $params['get']['iDisplayStart'] . ', ' . (int) $params['get']['iDisplayLength'];
                $params['sql_calc_found_rows'] = true;
            }
        }
        // liste des champs affiches
        $champs_affiches = array();
        $firstrow = $this->data['fields'];
        foreach ($firstrow as $tablefield => $val) {
            $fieldmeta = $this->data['fields'][$tablefield];
            $hidden = 0;
            if (isset($this->data['metas']['hidden_fields'][$tablefield]) && $this->data['metas']['hidden_fields'][$tablefield]) {
                $hidden = 1;
            }
            if (!$hidden) {
                $champs_affiches[] = $tablefield;
            }
        }
        // sorting / ordering
        if (isset($params['get']['iSortCol_0'])) {
            $this->data['return_json'] = 1;
            if (!defined('__NO_DEBUG_DIV__')) {
                define ('__NO_DEBUG_DIV__', true);
            }
            $order_by = array();
            $sort_ways = array('asc' => 'ASC', 'desc' => 'DESC');
            for ($i = 0 ; $i < (int) $params['get']['iSortingCols']; ++$i)
            {
                if ($params['get']['bSortable_' . (int) $params['get']['iSortCol_'.$i]] == "true") {
                    if (isset($champs_affiches[(int) $params['get']['iSortCol_' . $i]]) && isset($sort_ways[$params['get']['sSortDir_' . $i]])) {
                        $sort_field = $champs_affiches[(int) $params['get']['iSortCol_' . $i]];
                        $sort_way = $sort_ways[$params['get']['sSortDir_' . $i]];
                        $order = $sort_field . ' ' . $sort_way;
                        if (isset($this->data['metas']['custom_order_by'][$sort_field]) && isset($this->data['metas']['custom_order_by'][$sort_field][$sort_way])) {
                            $order = $this->data['metas']['custom_order_by'][$sort_field][$sort_way];
                        }
                        $order_by[] = $order;
                    }
                }
            }
            if (count($order_by)) {
                $params['order_by'] = implode(', ', $order_by);
            }
        }
        // filtering (recherche dans les champs affichés uniquement)
        $filter_where = $this->handle_ajax_filtering($champs_affiches, $this->data['metas'], $params);
        if ($filter_where) {
            if (!isset($params['where'])) {
                $params['where'] = ' 1 ';
            }
            $params['where'] .= ' AND (' . $filter_where . ') ';
        }
        $cssjs = $this->getModel('cssjs');
        // charge les valeurs pour les clés étrangères
        if ($this->options['autoload_foreign_keys_values']) {
            $db = $this->getModel('db');
            foreach ($this->_crud->fields as $tablefield => $fieldmeta) {
                if (isset($this->_crud->metas['foreign_keys'][$tablefield])) {
                    list($ref_table, $ref_field) = explode('.', $this->_crud->metas['foreign_keys'][$tablefield]);
                    if (!empty($this->_crud->metas['keys_labels'][$ref_table . '.' . $ref_field])) {
                        $distincts = $db->distinct_values($ref_table, $ref_field, $this->_crud->metas['keys_labels'][$ref_table . '.' . $ref_field]);
                    } else {
                        $distincts = $db->distinct_values($ref_table, $ref_field);
                    }
                    $this->setFieldValues($tablefield, $distincts);
                }
            }
        }
        // charge les valeurs
        $this->register_ui_scripts();
        if ($cssjs->is_registered_foot('clementine_crud-datatables')) {
            if (isset($params['get']['iDisplayLength'])) {
                $values  = $this->_crud->getList($params);
            } else {
                // on charge quand meme un element
                $fake_params = $params;
                if (!isset($fake_params['limit'])) {
                    $fake_params['limit'] = '1'; // limit a 1 pour eviter le contenu qui apparait en flash
                }
                $values  = $this->_crud->getList($fake_params);
            }
        } else {
            $values  = $this->_crud->getList($params);
        }
        $to_merge['values']  = $values;
        $this->merge_values($to_merge);
        // prise en compte des champs ajoutés
        foreach ($this->data['values'] as $key => $val) {
            foreach ($this->data['fields'] as $fkey => $fval) {
                if (!isset($this->data['values'][$key][$fkey])) {
                    $this->data['values'][$key][$fkey] = '';
                }
            }
        }
        // recupere le nombre total de resultats (hors limit)
        if (isset($params['sql_calc_found_rows']) && $params['sql_calc_found_rows']) {
            $this->data['nb_total_values'] = $this->getModel('db')->found_rows();
        } else {
            $this->data['nb_total_values'] = count($this->data['values']);
        }
        // par défaut, on masque les boutons dupliquer et XLS
        if (!isset($this->data['hidden_sections']['duplicatebutton'])) {
            $this->hideSection('duplicatebutton');
        }
        if (!isset($this->data['hidden_sections']['xlsbutton'])) {
            $this->hideSection('xlsbutton');
        }
        $this->alter_values($params);
        $this->alter_values_index($params);
    }

    /**
     * createAction : création d'un nouvel enregistrement
     * 
     * @access public
     * @return void
     */
    public function createAction($request, $params = null)
    {
        $ns = $this->getModel('fonctions');
        // cette classe est destinee a etre surchargee, elle ne doit servir a rien sinon !
        if (get_class($this) == 'CrudController') {
            $this->trigger404();
        }
        $errors = array();
        // recupere les valeurs postees
        $this->get_unquoted_gpc($params);
        // charge les metadonnees
        $to_merge = array();
        $to_merge['tables']  = $this->_crud->tables;
        $to_merge['metas']   = $this->_crud->metas;
        /*$to_merge['fields']  = $this->_crud->fields;*/
        $to_merge['mapping'] = $this->mapping_to_HTML;
        $this->merge_defaults($to_merge);
        $this->merge_values($to_merge);
        // fonctions pour faciliter la surcharge
        $this->add_fields($params);
        $this->add_fields_create_or_update($params);
        $this->override_fields($params);
        $this->override_fields_create_or_update($params);
        $this->hide_sections($params);
        $this->hide_sections_create_or_update($params);
        $this->hide_fields($params);
        $this->hide_fields_create_or_update($params);
        $this->move_fields($params);
        $this->move_fields_create_or_update($params);
        $this->rename_fields($params);
        $this->rename_fields_create_or_update($params);
        $this->reverse_translate_dates_gp($params);
        // charge les valeurs pour les clés étrangères
        if ($this->options['autoload_foreign_keys_values']) {
            $db = $this->getModel('db');
            foreach ($this->_crud->fields as $tablefield => $fieldmeta) {
                if (isset($this->_crud->metas['foreign_keys'][$tablefield])) {
                    list($ref_table, $ref_field) = explode('.', $this->_crud->metas['foreign_keys'][$tablefield]);
                    if (!empty($this->_crud->metas['keys_labels'][$ref_table . '.' . $ref_field])) {
                        $distincts = $db->distinct_values($ref_table, $ref_field, $this->_crud->metas['keys_labels'][$ref_table . '.' . $ref_field]);
                    } else {
                        $distincts = $db->distinct_values($ref_table, $ref_field);
                    }
                    $this->setFieldValues($tablefield, $distincts);
                }
            }
        }
        // enregistre les valeurs si possible
        $last_insert_ids = 0;
        if (count($_POST)) {
            // gere l'upload de fichiers
            $ret = $this->handle_uploading($params, $errors);
            if ($ret) {
                return $ret;
            }
            // nettoie les valeurs postées
            $params['post'] = $this->sanitize($params['post']);
            $validate_errs  = $this->validate($params['post'], $params['get']);
            $move_errs = array();
            $uploaded_files = array();
            if (!count($validate_errs) && !count($errors)) {
                if ($ns->ifGet('int', 'duplicate')) {
                    $params['duplicate'] = 1;
                }
                $result         = $this->handle_uploaded_files($params, $errors, 'create');
                $uploaded_files = $result['uploaded_files'];
                $move_errs      = $result['move_errs'];
            }
            if (!count($validate_errs) && !count($errors) && !count($move_errs)) {
                // enregistre les valeurs postees
                if (!isset($params['dont_start_transaction'])) {
                    $params['dont_start_transaction'] = false;
                }
                if (!$last_insert_ids = $this->_crud->createFromArray($params['post'], $params['dont_start_transaction'])) {
                    $errors[] = 'erreur rencontree lors de la creation';
                }
            } else {
                $errors = array_merge($errors, $validate_errs);
            }
        }
        // charge les donnees
        $this->register_ui_scripts(true);
        $values = array(0 => '');
        foreach ($this->_crud->fields as $tablefield => $fieldmeta) {
            $values[0][$tablefield] = '';
            if ($last_insert_ids) {
                if ($fieldmeta['type'] != 'custom_field') {
                    list($last_id_table, $last_id_field) = explode('.', $tablefield);
                    if (isset($last_insert_ids[$last_id_table]) && isset($last_insert_ids[$last_id_table][$last_id_field])) {
                        // reporte le last_insert_id dans les valeurs
                        $values[0][$tablefield] = $last_insert_ids[$last_id_table][$last_id_field];
                    }
                }
            }
        }
        // duplication si et seulement si demandee explicitement dans les parametres GET
        if ($ns->ifGet('int', 'duplicate')) {
            // un petit flag pour mettre dans la vue
            $this->data['duplicate'] = 1;
            // charge les donnees
            // on ne passe pas de parametres supplementaires ici, c'est volontaire
            $values = array(0 => $ns->array_first($this->_crud->getFromArray($params['get'])));
            // TODO: pour le moment on ne duplique pas les fichiers uploadés, donc on vide les champs
            // TODO: il faudrait les copier (pour que l'element dupliqué travaille sur un fichier bien à lui)
            foreach ($this->data['fields'] as $nom => $champ) {
                if ($champ['type'] == 'file') {
                    if (isset($values[0][$nom])) {
                        $values[0][$nom] = '';
                    }
                }
            }
            if (!is_array($values) || (count($values) !== 1)) {
                $errors[] = 'element non trouvé';
                $values = array();
            }
        }
        $to_merge = array();
        $to_merge['values']  = $values;
        $to_merge['errors']  = $errors;
        $this->merge_defaults($to_merge);
        $this->merge_values($to_merge);
        // prise en compte des champs ajoutés
        foreach ($this->data['values'] as $key => $val) {
            foreach ($this->data['fields'] as $fkey => $fval) {
                if (!isset($this->data['values'][$key][$fkey])) {
                    $this->data['values'][$key][$fkey] = '';
                }
            }
        }
        if (!isset($params['dont_handle_errors'])) {
            $params['dont_handle_errors'] = false;
        }
        if (count($params['post'])) {
            if (!$params['dont_handle_errors']) {
                return $this->handle_errors($errors);
            } else {
                return $errors;
            }
        }
        // how to hide fields
        // $this->hideField($tablefield);
        // how to map field names
        // $this->mapFieldName($tablefield, 'Champ FIELD de la table TABLE');
        // how to set field values
        // $this->setFieldValues($tablefield, $values);
        // how to hide view sections
        // $this->hideSection('section');
        $this->alter_values($params);
        $this->alter_values_create_or_update($params);
    }

    /**
     * readAction : affichage d'un enregistrement
     * 
     * @access public
     * @return void
     */
    public function readAction($request, $params = null)
    {
        // cette classe est destinee a etre surchargee, elle ne doit servir a rien sinon !
        if (get_class($this) == 'CrudController') {
            $this->trigger404();
        }
        $errors = array();
        // recupere les valeurs postees
        $this->get_unquoted_gpc($params);
        // charge les valeurs pour les clés étrangères
        if ($this->options['autoload_foreign_keys_values']) {
            $db = $this->getModel('db');
            foreach ($this->_crud->fields as $tablefield => $fieldmeta) {
                if (isset($this->_crud->metas['foreign_keys'][$tablefield])) {
                    list($ref_table, $ref_field) = explode('.', $this->_crud->metas['foreign_keys'][$tablefield]);
                    if (!empty($this->_crud->metas['keys_labels'][$ref_table . '.' . $ref_field])) {
                        $distincts = $db->distinct_values($ref_table, $ref_field, $this->_crud->metas['keys_labels'][$ref_table . '.' . $ref_field]);
                    } else {
                        $distincts = $db->distinct_values($ref_table, $ref_field);
                    }
                    $this->setFieldValues($tablefield, $distincts);
                }
            }
        }
        // charge les donnees
        $values = $this->_crud->getFromArray($params['get'], $params);
        if (!is_array($values) || (count($values) !== 1)) {
            $errors[] = 'element non trouvé';
            $values = array();
        }
        $to_merge = array();
        $to_merge['values']  = $values;
        $to_merge['errors']  = $errors;
        $to_merge['tables']  = $this->_crud->tables;
        $to_merge['metas']   = $this->_crud->metas;
        /*$to_merge['fields']  = $this->_crud->fields;*/
        $to_merge['mapping'] = $this->mapping_to_HTML;
        $this->merge_defaults($to_merge);
        $this->merge_values($to_merge);
        // prise en compte des champs ajoutés
        foreach ($this->data['values'] as $key => $val) {
            foreach ($this->data['fields'] as $fkey => $fval) {
                if (!isset($this->data['values'][$key][$fkey])) {
                    $this->data['values'][$key][$fkey] = '';
                }
            }
        }
        // fonctions pour faciliter la surcharge
        $this->add_fields($params);
        $this->add_fields_read($params);
        $this->override_fields($params);
        $this->override_fields_read($params);
        $this->hide_sections($params);
        $this->hide_sections_read($params);
        $this->hide_fields($params);
        $this->hide_fields_read($params);
        $this->move_fields($params);
        $this->move_fields_read($params);
        $this->rename_fields($params);
        $this->rename_fields_read($params);
        // pas d'element, ou en tout cas pas accessible... on renvoie un header 404
        if (!count($this->data['values'])) {
            if (__DEBUGABLE__ && Clementine::$config['clementine_debug']['display_errors']) {
                $this->getHelper('debug')->unknown_element();
            }
            $this->trigger404();
        }
        // charge les valeurs pour les clés étrangères
        if ($this->options['autoload_foreign_keys_values']) {
            // TODO: (code obsolète supprimé)
        }
        // affiche le fichier demande avec les memes droits que l'objet
        $ns = $this->getModel('fonctions');
        $tablefield = $ns->ifGet('string', 'file');
        if ($tablefield && isset($this->data['fields'][$tablefield]) && ($this->data['fields'][$tablefield]['type'] == 'file')) {
            $values = $ns->array_first($this->data['values']);
            if (isset($values[$tablefield]) && $values[$tablefield]) {
                $file_cmspath = $values[$tablefield];
                $file_path = str_replace('__CLEMENTINE_CONTENUS_WWW_ROOT__', __FILES_ROOT__, $file_cmspath);
                $visible_name = preg_replace('/^[^-]*-/', '', basename($file_cmspath));
                if (file_exists($file_path)) {
                    $ns->send_file($file_path, $visible_name);
                } else {
                    if (__DEBUGABLE__ && Clementine::$config['clementine_debug']['display_errors']) {
                        $this->getHelper('debug')->unknown_element();
                    }
                    $this->trigger404();
                }
                die();
            }
        }
        // how to hide fields           : $this->hideField($tablefield);
        // how to map field names       : $this->mapFieldName($tablefield, 'Champ FIELD de la table TABLE');
        // how to set field values      : $this->setFieldValues($tablefield, $values);
        // how to hide view sections    : $this->hideSection('section');
        $this->alter_values($params);
        $this->alter_values_read($params);
    }

    /**
     * updateAction : edition et sauvegarde d'un enregistrement
     * 
     * @access public
     * @return void
     */
    public function updateAction($request, $params = null)
    {
        // cette classe est destinee a etre surchargee, elle ne doit servir a rien sinon !
        if (get_class($this) == 'CrudController') {
            $this->trigger404();
        }
        $errors = array();
        // recupere les valeurs postees
        $this->get_unquoted_gpc($params);
        // charge les metadonnees
        $to_merge = array();
        $to_merge['tables']  = $this->_crud->tables;
        $to_merge['metas']   = $this->_crud->metas;
        /*$to_merge['fields']  = $this->_crud->fields;*/
        $to_merge['mapping'] = $this->mapping_to_HTML;
        $this->merge_defaults($to_merge);
        $this->merge_values($to_merge);
        // fonctions pour faciliter la surcharge
        $this->add_fields($params);
        $this->add_fields_create_or_update($params);
        $this->override_fields($params);
        $this->override_fields_create_or_update($params);
        $this->hide_sections($params);
        $this->hide_sections_create_or_update($params);
        $this->hide_fields($params);
        $this->hide_fields_create_or_update($params);
        $this->move_fields($params);
        $this->move_fields_create_or_update($params);
        $this->rename_fields($params);
        $this->rename_fields_create_or_update($params);
        $this->reverse_translate_dates_gp($params);
        // charge les valeurs pour les clés étrangères
        if ($this->options['autoload_foreign_keys_values']) {
            $db = $this->getModel('db');
            foreach ($this->_crud->fields as $tablefield => $fieldmeta) {
                if (isset($this->_crud->metas['foreign_keys'][$tablefield])) {
                    list($ref_table, $ref_field) = explode('.', $this->_crud->metas['foreign_keys'][$tablefield]);
                    if (!empty($this->_crud->metas['keys_labels'][$ref_table . '.' . $ref_field])) {
                        $distincts = $db->distinct_values($ref_table, $ref_field, $this->_crud->metas['keys_labels'][$ref_table . '.' . $ref_field]);
                    } else {
                        $distincts = $db->distinct_values($ref_table, $ref_field);
                    }
                    $this->setFieldValues($tablefield, $distincts);
                }
            }
        }
        // enregistre les valeurs si possible
        if (count($_POST)) {
            // gere l'upload de fichiers
            $ret = $this->handle_uploading($params, $errors);
            if ($ret) {
                return $ret;
            }
            // nettoie les valeurs postées
            $params['post'] = $this->sanitize($params['post']);
            $validate_errs  = $this->validate($params['post'], $params['get']);
            $move_errs = array();
            $uploaded_files = array();
            if (!count($validate_errs) && !count($errors)) {
                $result         = $this->handle_uploaded_files($params, $errors);
                $uploaded_files = $result['uploaded_files'];
                $move_errs      = $result['move_errs'];
            }
            if (!count($validate_errs) && !count($errors) && !count($move_errs)) {
                // enregistre les valeurs postees
                if (!isset($params['dont_start_transaction'])) {
                    $params['dont_start_transaction'] = false;
                }
                if (!$this->_crud->updateFromArray($params['post'], $params['get'], $params['dont_start_transaction'])) {
                    $errors[] = 'erreur rencontree lors de la sauvegarde';
                }
            } else {
                // TODO : suppression des fichiers uploades (dans un cron ?)
                $errors = array_merge($errors, $validate_errs, $move_errs);
            }
        }
        // charge les donnees
        $this->register_ui_scripts(true);
        // on ne passe pas de parametres supplementaires ici, c'est volontaire
        $values = $this->_crud->getFromArray($params['get']);
        if (!is_array($values) || (count($values) !== 1)) {
            $errors[] = 'element non trouvé';
            $values = array();
        }
        $to_merge = array();
        $to_merge['values']  = $values;
        $to_merge['errors']  = $errors;
        $this->merge_defaults($to_merge);
        $this->merge_values($to_merge);
        // prise en compte des champs ajoutés
        foreach ($this->data['values'] as $key => $val) {
            foreach ($this->data['fields'] as $fkey => $fval) {
                if (!isset($this->data['values'][$key][$fkey])) {
                    $this->data['values'][$key][$fkey] = '';
                }
            }
        }
        if (!isset($params['dont_handle_errors'])) {
            $params['dont_handle_errors'] = false;
        }
        if (count($params['post'])) {
            if (!$params['dont_handle_errors']) {
                return $this->handle_errors($errors);
            } else {
                return $errors;
            }
        }
        // how to hide fields           : $this->hideField($tablefield);
        // how to map field names       : $this->mapFieldName($tablefield, 'Champ FIELD de la table TABLE');
        // how to set field values      : $this->setFieldValues($tablefield, $values);
        // how to hide view sections    : $this->hideSection('section');
        $this->alter_values($params);
        $this->alter_values_create_or_update($params);
    }

    /**
     * deletetmpfile : supprime un fichier uploade durant la session courante (donc par l'utilisateur courant)
     * 
     * @access public
     * @return void
     */
    public function deletetmpfileAction($request)
    {
        // cette classe est destinee a etre surchargee, elle ne doit servir a rien sinon !
        if (get_class($this) == 'CrudController') {
            $this->trigger404();
        }
        $ns = $this->getModel('fonctions');
        $filename = $ns->ifGet('string', 'file');
        // securise le nom de fichier
        $filename = preg_replace('/[^a-zA-Z0-9-\.]/', '', $filename);
        $filename = preg_replace('/\.\.*/', '.', $filename);
        if ($filename == '.htaccess') {
            die();
        }
        $sesskey = false;
        if (isset($_SESSION['crud_uploaded_files'])) {
            $sesskey = array_search($filename, $_SESSION['crud_uploaded_files']);
        }
        if ($sesskey !== false) {
            unlink(__FILES_ROOT__ . '/tmp/' . $filename);
            unset($_SESSION['crud_uploaded_files'][$sesskey]);
        }
        return array('dont_getblock' => true);
    }

    /**
     * deleteAction : suppression d'un enregistrement
     * 
     * @access public
     * @return void
     */
    public function deleteAction($request, $params = null)
    {
        // cette classe est destinee a etre surchargee, elle ne doit servir a rien sinon !
        if (get_class($this) == 'CrudController') {
            $this->trigger404();
        }
        $ns = $this->getModel('fonctions');
        $errors = array();
        // recupere les valeurs postees
        $this->get_unquoted_gpc($params);
        // transmet les donnees
        $to_merge = array();
        $to_merge['errors']  = $errors;
        $to_merge['tables']  = $this->_crud->tables;
        $to_merge['metas']   = $this->_crud->metas;
        /*$to_merge['fields']  = $this->_crud->fields;*/
        $to_merge['mapping'] = $this->mapping_to_HTML;
        $this->merge_defaults($to_merge);
        $this->merge_values($to_merge);
        // enregistre les valeurs si possible
        if (count($params['get'])) {
            if (!isset($params['dont_start_transaction'])) {
                $params['dont_start_transaction'] = false;
            }
            // on ne passe pas de parametres supplementaires ici, c'est volontaire
            $oldvalues = '';
            if (is_array($params['get']) && count($params['get'])) {
                $oldvalues_list = $this->_crud->getFromArray($params['get']);
                if (count($oldvalues_list)) {
                    $oldvalues = $ns->array_first($oldvalues_list);
                }
                // fix memory leak
                unset($oldvalues_list);
            }
            if (!$this->_crud->deleteFromArray($params['get'], $params['dont_start_transaction'])) {
                $errors[] = 'erreur rencontree lors de la suppression';
            } else {
                // supprime aussi les fichiers
                if (is_array($oldvalues)) {
                    foreach ($oldvalues as $tablefield => $val) {
                        if (isset($this->data['fields'][$tablefield]) && ($this->data['fields'][$tablefield]['type'] == 'file')) {
                            // supprime le fichier precedent
                            $previous_file = str_replace('__CLEMENTINE_CONTENUS_WWW_ROOT__', __FILES_ROOT__, $oldvalues[$tablefield]);
                            if ($previous_file && is_file($previous_file)) {
                                unlink($previous_file);
                            }
                        }
                    }
                }
            }
            if (!count($errors)) {
                if (isset($params['url_retour'])) {
                    $ns->redirect($params['url_retour']);
                }
                $ns->redirect(__WWW__ . '/' . $this->_class . '/index?id=');
            }
        }
    }

    /**
     * hideSection : raccourci pour masquer une section dans une vue
     *               cette fonction s'appelle depuis le controleur de la vue,
     *               apres avoir rempli le tableau $this->data
     * 
     * @param mixed $section : nom de la section
     * @access public
     * @return void
     */
    public function hideSection($section)
    {
        if (!isset($this->data['hidden_sections'])) {
            $this->data['hidden_sections'] = array();
        }
        if (isset($this->data['hidden_sections'])) {
            $this->data['hidden_sections'][$section] = true;
            return true;
        }
        return false;
    }

    /**
     * unhideSection : raccourci pour demasquer une section dans une vue
     *                 cette fonction s'appelle depuis le controleur de la vue,
     *                 apres avoir rempli le tableau $this->data
     * 
     * @param mixed $section : nom de la section
     * @access public
     * @return void
     */
    public function unhideSection($section)
    {
        $this->data['hidden_sections'][$section] = 0;
        return true;
    }

    /**
     * hideField : raccourci pour masquer un champ dans une vue
     *             cette fonction s'appelle depuis le controleur de la vue,
     *             apres avoir rempli le tableau $this->data
     * 
     * @param mixed $tablefield 
     * @access public
     * @return void
     */
    public function hideField($tablefield)
    {
        $this->data['metas']['hidden_fields'][$tablefield] = true;
        return true;
    }

    /**
     * unhideField : raccourci pour demasquer un champ dans une vue
     *               cette fonction s'appelle depuis le controleur de la vue,
     *               apres avoir rempli le tableau $this->data
     * 
     * @param mixed $tablefield 
     * @access public
     * @return void
     */
    public function unhideField($tablefield)
    {
        $this->data['metas']['hidden_fields'][$tablefield] = 0;
        return true;
    }

    /**
     * hideAllFields : masque tous les champs
     * 
     * @param mixed $tablefield 
     * @access public
     * @return void
     */
    public function hideAllFields($tablefield)
    {
        foreach ($this->_crud->fields as $key => $val) {
            $this->data['metas']['hidden_fields'][$key] = true;
        }
        return true;
    }

    /**
     * unhideAllFields : demasque tous les champs
     * 
     * @param mixed $tablefield 
     * @access public
     * @return void
     */
    public function unhideAllFields($tablefield)
    {
        foreach ($this->_crud->fields as $key => $val) {
            $this->data['metas']['hidden_fields'][$key] = false;
        }
        return true;
    }

    /**
     * hideSections : appelle hideSection pour plusieurs sections (permet de réunir les appels en un seul pour plus de lisibilité)
     * 
     * @param mixed $sections 
     * @access public
     * @return void
     */
    public function hideSections($sections)
    {
        foreach ($sections as $section) {
            $this->hideSection($section);
        }
        return true;
    }

    /**
     * unhideSections : appelle unhideSection pour plusieurs sections (permet de réunir les appels en un seul pour plus de lisibilité)
     * 
     * @param mixed $sections 
     * @access public
     * @return void
     */
    public function unhideSections($sections)
    {
        foreach ($sections as $section) {
            $this->unhideSection($section);
        }
        return true;
    }

    /**
     * hideFields : appelle hideField pour plusieurs champs (permet de réunir les appels en un seul pour plus de lisibilité)
     * 
     * @param mixed $fields 
     * @access public
     * @return void
     */
    public function hideFields($fields)
    {
        foreach ($fields as $tablefield) {
            $this->hideField($tablefield);
        }
        return true;
    }

    /**
     * unhideFields : appelle unhideField pour plusieurs champs (permet de réunir les appels en un seul pour plus de lisibilité)
     * 
     * @param mixed $fields 
     * @access public
     * @return void
     */
    public function unhideFields($fields)
    {
        foreach ($fields as $tablefield) {
            $this->unhideField($tablefield);
        }
        return true;
    }

    /**
     * addField : ajoute un champ "virtuel", sans valeur mais qui sera utilisé 
     *            dans la génération des formulaires
     *            cette fonction s'appelle depuis le controleur de la vue,
     *            apres avoir rempli le tableau $this->data
     *            permet par exemple de rajouter des colonnes calculees dans la
     *            page listing
     * 
     * @param mixed $tablefield : $table.$field
     * @param mixed $before_tablefield : $table.$field
     * @param mixed $fieldsmeta : tableau de meta informations sur le champ, 
     *                            par exemple : array('type' => 'varchar', 
                                                      'fieldvalues' => array('Foo' => 'foo',
                                                                             'Bar' => 'bar'),
                                                      'default_value' => 'Bar')
     * @access public
     * @return void
     */
    public function addField($tablefield, $before_tablefield = null, $field_definition = null, $fieldmeta = null)
    {
        // ajoute le champ $tablefield dans les entetes...
        $ns = $this->getModel('fonctions');
        if (isset($fieldmeta['type'])) {
            $fieldmeta['custom_type'] = $fieldmeta['type'];
        }
        if (!$fieldmeta) {
            $fieldmeta = array();
        }
        $fieldmeta['type'] = 'custom_field';
        if (isset($field_definition)) {
            $this->_crud->addCustomField($tablefield, $field_definition);
        }
        if (!$fieldmeta) {
            $fieldmeta = array(
                'type' => 'custom_field',
                'custom_type' => 'varchar'
            );
        }
        if ($before_tablefield) {
            list ($before_table, $before_field) = explode('.', $before_tablefield, 2);
            if (!$ns->array_insert_before(array($tablefield => $fieldmeta), $this->data['fields'], $before_table . '.' . $before_field)) {
                $this->data['fields'][$tablefield] = $fieldmeta;
            }
        } else {
            $this->data['fields'][$tablefield] = $fieldmeta;
        }
        // ... et dans les valeurs
        if (isset($this->data['values']) && count($this->data['values'])) {
            foreach ($this->data['values'] as $key => $row) {
                unset($row[$tablefield]);
                $default_val = '';
                if (isset($fieldmeta['default_value'])) {
                    $default_val = $fieldmeta['default_value'];
                }
                // si la cle avant laquelle on veut inserer n'est pas trouvee, on ajoute a la fin a la place
                if (!$before_tablefield || !$ns->array_insert_before(array($tablefield => $default_val), $row, $before_table . '.' . $before_tablefield)) {
                    $row[$tablefield] = $default_val;
                }
                $this->data['values'][$key] = $row;
            }
        }
        return true;
    }

    /**
     * moveField : deplace un des champ utilisés pour la génération des
     *             formulaires dans l'entete et dans chacune des lignes du
     *             tableau de valeurs
     *             cette fonction s'appelle depuis le controleur de la vue,
     *             apres avoir rempli le tableau $this->data
     * 
     * @param mixed $tablefield : $table.$field
     * @param mixed $before_tablefield : $table.$field
     * @param mixed $type 
     * @access public
     * @return void
     */
    public function moveField($tablefield, $before_tablefield = null)
    {
        // deplace le champ $tablefield juste avant le champ $before_tablefield...
        $ns = $this->getModel('fonctions');
        // ... dans les entetes...
        $fieldmeta = $this->data['fields'][$tablefield];
        if ($before_tablefield) {
            $val = $this->data['fields'][$tablefield];
            unset($this->data['fields'][$tablefield]);
            if (!$ns->array_insert_before(array($tablefield => $fieldmeta), $this->data['fields'], $before_tablefield)) {
                $this->data['fields'][$tablefield] = $fieldmeta;
            }
        } else {
            $this->data['fields'][$tablefield] = $fieldmeta;
        }
        // pas besoin de deplacer les valeurs, ce serait une perte de 
        // performances inutile, puisque l'ordre est donne par data[fields]
        return true;
    }

    /**
     * mapFieldName : raccourci pour renommer un champ dans une vue
     *                cette fonction s'appelle depuis le controleur de la vue,
     *                apres avoir rempli le tableau $this->data
     * 
     * @param mixed $tablefield 
     * @param mixed $name 
     * @access public
     * @return void
     */
    public function mapFieldName($tablefield, $name)
    {
        $this->data['metas']['title_mapping'][$tablefield] = $name;
        return true;
    }

    /**
     * unmapFieldName : raccourci pour annuler le renommage d'un champ dans une vue
     *                  cette fonction s'appelle depuis le controleur de la vue,
     *                  apres avoir rempli le tableau $this->data
     * 
     * @param mixed $tablefield 
     * @param mixed $name : si différent de false, n'annule le renommage que 
     *                      s'il a la valeur $name
     * @access public
     * @return void
     */
    public function unmapFieldName($tablefield, $name = false)
    {
        if (isset($this->data['metas']['title_mapping'][$tablefield])) {
            if ($name === false || ($name !== false && $this->data['metas']['title_mapping'][$tablefield] == $name)) {
                unset($this->data['metas']['title_mapping'][$tablefield]);
            }
        }
        return true;
    }

    /**
     * setFieldValues : raccourci pour donner les valeurs possibles d'un champ
     *                  dans une vue, ce qui fait du champ un SELECT
     *                  cette fonction s'appelle depuis le controleur de la vue,
     *                  apres avoir rempli le tableau $this->data
     * 
     * @param mixed $tablefield 
     * @param mixed $values 
     * @access public
     * @return void
     */
    public function setFieldValues($tablefield, $values)
    {
        list($table, $field) = explode('.', $tablefield, 2);
        if (isset($this->data['fields'][$tablefield])) {
            $this->data['fields'][$tablefield]['fieldvalues'] = $values;
            return true;
        }
        return false;
    }

    /**
     * unsetFieldValues : raccourci pour vider les valeurs possibles d'un champ 
     *                    dans une vue, ce qui fait du champ un SELECT
     *                    cette fonction s'appelle depuis le controleur de la vue,
     *                    apres avoir rempli le tableau $this->data
     * 
     * @param mixed $table 
     * @param mixed $field 
     * @param mixed $values 
     * @access public
     * @return void
     */
    public function unsetFieldValues($table, $field, $values)
    {
        if (isset($this->data['fields'][$tablefield])) {
            unset($this->data['fields'][$tablefield]['fieldvalues']);
            return true;
        }
        return false;
    }

    /**
     * sanitize : filtre les valeurs du tableau $insecure_array 
     *            renvoie le tableau filtré
     * 
     * @param mixed $insecure_array 
     * @access public
     * @return void
     */
    public function sanitize($insecure_array)
    {
        // cette fonction est destinée à être surchargée
        // par défaut, on appelle la fonction sanitize du modele
        return $this->_crud->sanitizeValues($insecure_array);
    }

    /**
     * validate : valide les donnees avant creation ou mise à jour
     *            renvoie un tableau listant les erreurs rencontrees
     * 
     * @param mixed $insecure_values : tableau associatif 'table-champ' => 'valeur', par exemple $_POST
     * @param mixed $insecure_primary_key : tableau associatif 'table-champ' => 'valeur', par exemple $_GET
     * @access public
     * @return void
     */
    public function validate($insecure_values, $insecure_primary_key = null)
    {
        // par défaut aucune validation : fonction destinée à être surchargée
        return array();
    }

    public function handle_uploading(&$params, &$errors)
    {
        $ns = $this->getModel('fonctions');
        // determine upload_max_filesize
        $default_upload_max_filesize = $ns->get_max_filesize();
        if (!isset($_SESSION['crud_uploaded_files'])) {
            $_SESSION['crud_uploaded_files'] = array();
        }
        foreach ($this->data['fields'] as $tablefield => $fieldmeta) {
            if (isset($fieldmeta['type']) && $fieldmeta['type'] == 'file') {
                $fieldkey = str_replace('.', '-', $tablefield);
                $fileslot = array();
                $is_ajax_upload = 0;
                if (isset($_FILES[$fieldkey]['tmp_name']) && is_uploaded_file($_FILES[$fieldkey]['tmp_name'])) {
                    $fileslot = $_FILES[$fieldkey];
                } elseif (isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
                    $plupload_field_name = $ns->ifGet('string', 'plupload_field_name');
                    if ($fieldkey != $plupload_field_name) {
                        // ce n'est pas le bon champ de type 'file'
                        continue;
                    }
                    $fileslot = $_FILES['file'];
                    $is_ajax_upload = 1;
                    // pour eviter de voir le div de debug ressortir dans les messages d'erreur ajax quand le debug est active
                    define('__NO_DEBUG_DIV__', 1);
                }
                if (isset($fileslot['name'])) {
                    if ((isset($fieldmeta['parameters']) && (isset($fieldmeta['parameters']['max_filesize']) && $fileslot['size'] <= $fieldmeta['parameters']['max_filesize']))
                        || ($fileslot['size'] <= $default_upload_max_filesize)) {
                        $infosfichier = pathinfo($fileslot['name']);
                        $filename_upload  = strtolower($infosfichier['filename']);
                        $extension_upload = strtolower($infosfichier['extension']);
                        $visiblename = $ns->urlize(basename($filename_upload)) . '.' . $extension_upload;
                        $fullname = uniqid() . '-' . $visiblename;
                        // $fullname = basename($fileslot['tmp_name']) . '.' . $extension_upload;
                        if (!(isset($fieldmeta['parameters']) && isset($fieldmeta['parameters']['extensions']) && count($fieldmeta['parameters']['extensions']))
                            || in_array($extension_upload, $fieldmeta['parameters']['extensions'])) {
                            if (move_uploaded_file($fileslot['tmp_name'], __FILES_ROOT__ . '/tmp/' . $fullname)) {
                                // enregistre le fichier dans la liste des fichiers uploadés
                                $_SESSION['crud_uploaded_files'][] = $fullname;
                                // ecrase la valeur postee
                                $params['post'][$fieldkey . '-hidden'] = $fullname;
                                if ($is_ajax_upload) {
                                    echo '0';
                                    echo $fullname;
                                    echo ':';
                                    echo $visiblename;
                                    echo ':';
                                    return array('dont_getblock' => true);
                                }
                            } else {
                                $err = 'Problème lors du déplacement du fichier';
                                $errors[] = $err;
                                if ($is_ajax_upload) {
                                    echo '1';
                                    echo $err;
                                    return array('dont_getblock' => true);
                                }
                            }
                        } else {
                            $err = 'L\'extension du fichier n\'est pas supportée';
                            $errors[] = $err;
                            if ($is_ajax_upload) {
                                echo '1';
                                echo $err;
                                return array('dont_getblock' => true);
                            }
                        }
                    } else {
                        $err = 'Le fichier est trop volumineux';
                        $errors[] = $err;
                        if ($is_ajax_upload) {
                            echo '1';
                            echo $err;
                            return array('dont_getblock' => true);
                        }
                    }
                } else {
                    if (array_key_exists('name', $fileslot) && array_key_exists('tmp_name', $fileslot) && !$fileslot['tmp_name']) {
                        $err = 'Le fichier est trop volumineux pour ce serveur';
                        $errors[] = $err;
                        if ($is_ajax_upload) {
                            echo '1';
                            echo $err;
                            return array('dont_getblock' => true);
                        }
                    }
                }
                // else : fichier recu non attendu mais ce n'est pas forcement anormal, il peut venir d'une surcharge
            }
        }
    }

    public function handle_uploaded_files(&$params, &$errors, $mode = 'update')
    {
        $ns = $this->getModel('fonctions');
        // deplacement des fichiers uploades
        $move_errs = array();
        $uploaded_files = array();
        foreach ($params['post'] as $fieldkey => $val) {
            $tablefield = implode('.', explode('-', $fieldkey, 2)); // remplace table-champ-hidden par table.champ-hidden
            $tablefield_nothidden = preg_replace('/-hidden$/', '', $tablefield);
            // on ne s'interesse qu'aux champs hidden
            if ($tablefield_nothidden == $tablefield) {
                continue;
            }
            $fieldkey_nothidden = preg_replace('/-hidden$/', '', $fieldkey);
            if (isset($this->data['fields'][$tablefield_nothidden]) && ($this->data['fields'][$tablefield_nothidden]['type'] == 'file')) {
                // deplace le fichier vers son dossier destination : precisee ou par defaut
                $destdir = __FILES_ROOT__ . '/files/app/crud/' . $this->_class;
                if (isset($this->data['fields'][$tablefield_nothidden]['parameters']['dest_dir'])) {
                    $destdir = $this->data['fields'][$tablefield_nothidden]['parameters']['dest_dir'];
                }
                $destdir = preg_replace('@//*@', '/', $destdir . '/');
                if (!is_dir($destdir)) {
                    if (!file_exists($destdir)) {
                        mkdir($destdir, 0777, true);
                    }
                }
                // lors d'un create, on ignore cette etape puisqu'on n'a pas encore d'id sur lequel faire le lien
                $oldvalues = '';
                // on ne passe pas de parametres supplementaires ici, c'est volontaire
                if ($mode != 'create' && is_array($params['get']) && count($params['get'])) {
                    $oldvalues_list = $this->_crud->getFromArray($params['get']);
                    if (count($oldvalues_list)) {
                        $oldvalues = $ns->array_first($oldvalues_list);
                    }
                    // fix memory leak
                    unset($oldvalues_list);
                }
                $file_changed = 0;
                if (is_array($oldvalues) && array_key_exists($tablefield_nothidden, $oldvalues) && $oldvalues[$tablefield_nothidden] !== $val) {
                    // cas update
                    $file_changed = 1;
                } elseif (!$oldvalues) {
                    // cas create
                    $file_changed = 1;
                }
                $remove_file = isset($params['post'][$fieldkey_nothidden . '-remove']) && $params['post'][$fieldkey_nothidden . '-remove'] == '1';
                // si le fichier a ete modifie, ou si on demande a le supprimer
                if ($file_changed || $remove_file) {
                    if ($file_changed && $val && !rename(__FILES_ROOT__ . '/tmp/' . $val, $destdir . $val)) {
                        $move_errs[] = 'Impossible de déplacer le fichier ' . $tablefield_nothidden . ' vers sa destination. Problème de permissions ?';
                        $move_errs[] = 'rename(' . __FILES_ROOT__ . '/tmp/' . $val . ', ' . $destdir . $val;
                    } else {
                        // supprime le fichier precedent (sauf si duplication)
                        if (is_array($oldvalues) && array_key_exists($tablefield_nothidden, $oldvalues)) {
                            $previous_file = str_replace('__CLEMENTINE_CONTENUS_WWW_ROOT__', __FILES_ROOT__, $oldvalues[$tablefield_nothidden]);
                            // on ne touche pas aux fichiers précédents lors d'une duplication !
                            if ($previous_file && is_file($previous_file) && empty($params['duplicate'])) {
                                unlink($previous_file);
                            }
                        }
                        if ($val && !$remove_file) {
                            $uploaded_files[$fieldkey_nothidden] = $destdir . $val;
                            $params['post'][$fieldkey_nothidden] = str_replace(__FILES_ROOT__, '__CLEMENTINE_CONTENUS_WWW_ROOT__', $destdir) . $val;
                        } else {
                            $uploaded_files[$fieldkey_nothidden] = '';
                            $params['post'][$fieldkey_nothidden] = '';
                        }
                        // gestion des miniatures
                        if (isset($this->data['fields'][$tablefield_nothidden]['parameters']['thumbnails'])) {
                            // cree les miniatures : on force filename, et save_filename en fonction du dossier destdir demande pour chaque miniature (ou par defaut celui de l'image d'origine)
                            foreach ($this->data['fields'][$tablefield_nothidden]['parameters']['thumbnails'] as $key => $thumb) {
                                if (isset($thumb['resize_args']) && count($thumb['resize_args'])) {
                                    $args = $thumb['resize_args'];
                                    $args['filename'] = $destdir . $val;
                                    if (!isset($thumb['dest_dir'])) {
                                        // pas de dossier destination demande, on le determine a partir des dimensions demandees et du dossier destdir d'origine
                                        if (isset($args['canevaswidth']) && isset($args['canevasheight'])) {
                                            $thumb['dest_dir'] = $destdir . '/' . (int) $args['canevaswidth'] . 'x' . (int) $args['canevasheight'];
                                        } else {
                                            // on n'a pas fourni assez d'infos pour determiner le dossier de destination, tant pis pour cette miniature
                                            continue;
                                        }
                                    }
                                    $thumb['dest_dir'] = preg_replace('@//*@', '/', $thumb['dest_dir'] . '/'); // securite
                                    $args['save_filename'] = $thumb['dest_dir'] . $val;
                                    // on cree le dossier de destination de la miniatures
                                    if (!is_dir($thumb['dest_dir'])) {
                                        if (!file_exists($thumb['dest_dir'])) {
                                            mkdir($thumb['dest_dir'], 0777, true);
                                        }
                                    }
                                    $ns->img_resize($args);
                                }
                            }
                        }
                        // redimensionne l'image uploadee
                        $parametres = $this->data['fields'][$tablefield_nothidden]['parameters'];
                        if (isset($parametres['resize_args']) && count($parametres['resize_args'])) {
                            $args = $parametres['resize_args'];
                            $args['filename'] = $destdir . $val;
                            $args['save_filename'] = $destdir . $val;
                            $ns->img_resize($args);
                        }
                    }
                } else {
                    // on reste sur l'ancienne valeur
                    if (is_array($oldvalues) && array_key_exists($tablefield_nothidden, $oldvalues)) {
                        $params['post'][$fieldkey_nothidden] = $oldvalues[$tablefield_nothidden];
                    }
                }
            }
        }
        $retour = array(
            'uploaded_files' => $uploaded_files,
            'move_errs' => $move_errs);
        return $retour;
    }

    public function handle_errors($errors, $url_retour = null)
    {
        $request = $this->getRequest();
        $ns = $this->getModel('fonctions');
        if (!count($errors)) {
            if (!$url_retour) {
                $url_retour = __WWW__ . '/' . $this->_class . '/index?id=';
            }
            if ($request->AJAX) {
                echo '2';
                echo $url_retour;
                return array('dont_getblock' => true);
            } else {
                $ns->redirect($url_retour);
            }
        } else {
            if ($request->AJAX) {
                // valeur de retour pour AJAX
                echo '1';
                if ($this->canGetBlock($this->_class . '/errors')) {
                    $this->getBlock($this->_class . '/errors', array('errors' => $errors));
                } else {
                    $this->getBlock('crud/errors', array('errors' => $errors));
                }
                return array('dont_getblock' => true);
            } else {
                print_r($errors);
                die();
            }
        }
    }

    public function merge_defaults($to_merge)
    {
        $ns = $this->getModel('fonctions');
        $default_fields = array('errors', 'tables', 'fields', 'mapping', 'metas');
        foreach ($default_fields as $field) {
            if (!isset($this->data[$field])) {
                $this->data[$field] = array();
            }
            if (isset($to_merge[$field])) {
                $this->data[$field] = $ns->array_replace_recursive($to_merge[$field], $this->data[$field]);
            }
        }
    }

    public function merge_fields($to_merge)
    {
        $ns = $this->getModel('fonctions');
        if (!isset($this->data['fields'])) {
            $this->data['fields'] = array();
        }
        if (isset($to_merge['fields'])) {
            $this->data['fields'] = $ns->array_replace_recursive($to_merge['fields'], $this->data['fields']);
        }
    }

    public function merge_values($to_merge)
    {
        $ns = $this->getModel('fonctions');
        if (!isset($this->data['values'])) {
            $this->data['values'] = array();
        }
        if (isset($to_merge['values'])) {
            $this->data['values'] = $ns->array_replace_recursive($to_merge['values'], $this->data['values']);
        }
    }

    /**
     * get_unquoted_gpc : recupere $_GET, $_POST et $_COOKIE dans le tableau $params si necessaire
     *                    applique stripslashes dessus si get_magic_quotes_gpc() == true
     * 
     * @param mixed $params 
     * @access public
     * @return void
     */
    public function get_unquoted_gpc(&$params)
    {
        $request = $this->getRequest();
        if (!$params) {
            $params = array();
        }
        if (!isset($params['get'])) {
            $params['get'] = $request->GET;
        }
        if (!isset($params['post'])) {
            $params['post'] = $request->POST;
        }
        if (!isset($params['cookie'])) {
            $params['cookie'] = $request->COOKIE;
        }
        if (!isset($params['request'])) {
            $params['request'] = $request->REQUEST;
        }
        return $params;
    }

    /**
     * reverse_translate_dates_gp : 
     * 
     * @param mixed $params 
     * @access public
     * @return void
     */
    public function reverse_translate_dates_gp(&$params)
    {
        $ns = $this->getModel('fonctions');
        foreach ($params as $gpc_name => &$gpc_vals) {
            if (!in_array($gpc_name, array('get', 'post'))) {
                continue;
            }
            foreach ($gpc_vals as $tablefield => &$val) {
                $tablefield_array = explode('-', $tablefield, 2);
                if (count($tablefield_array) == 2) {
                    $key = $tablefield_array[0] . '.' . $tablefield_array[1];
                    if (isset($this->data['fields'][$key]) && isset($this->data['fields'][$key]['type'])) {
                        if ($this->data['fields'][$key]['type'] == 'date') {
                            list ($date) = explode(' ', $val, 2);
                            if ($val) {
                                list ($d, $m, $Y) = explode('/', $date, 3);
                                $val = $Y . '-' . $m . '-' . $d;
                            }
                        }
                        if ($this->data['fields'][$key]['type'] == 'datetime') {
                            if ($val) {
                                list ($date, $time) = explode(' ', $val, 2);
                                if ($date && $time) {
                                    list ($d, $m, $Y) = explode('/', $date, 3);
                                    list ($H, $i, $s) = explode(':', $time, 3);
                                    $val = $Y . '-' . $m . '-' . $d . ' ' . $H . ':' . $i . ':' . $s;
                                }
                            }
                        }
                    }
                }
            }
        }
        return $params;
    }

    public function register_ui_scripts($create_or_update = false)
    {
        $request = $this->getRequest();
        $cssjs = $this->getModel('cssjs');
        // jQuery and jQuery UI
        if (Clementine::$config['module_jstools']['use_google_cdn']) {
            $cssjs->register_js('jquery', array('src' => 'https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js'));
        } else {
            $cssjs->register_js('jquery', array('src' => __WWW_ROOT_JSTOOLS__ . '/skin/jquery/jquery.min.js'));
        }
        $cssjs->register_js('jquery-ui', array('src' => __WWW_ROOT_JSTOOLS__ . '/skin/jquery-ui/js/jquery-ui.custom.min.js'));
        if ($create_or_update) {
            // jquery.anytime
            $cssjs->register_css('jquery.anytime',  array('src' => __WWW_ROOT_CRUD__ . '/skin/js/anytime/anytimec.css'));
            $cssjs->register_js('jquery.anytime',   array('src' => __WWW_ROOT_CRUD__ . '/skin/js/anytime/anytimec.js'));
            $cssjs->register_js('jquery.anytimetz', array('src' => __WWW_ROOT_CRUD__ . '/skin/js/anytime/anytimetz.js'));
            $cssjs->register_js('clementine_crud-anytimejs', array('src' => __WWW_ROOT_CRUD__ . '/skin/js/anytime/clementine_crud_anytime.js'));
            $cssjs->register_foot('clementine_crud-anytime', $this->getBlockHtml('crud/js_anytime', $this->data));
            // plupload : ajax upload
            $cssjs->register_css('plupload_clementine', array('src' => __WWW_ROOT_JSTOOLS__ . '/skin/plupload/plupload_clementine.css'));
            $cssjs->register_js('plupload', array('src' => __WWW_ROOT_JSTOOLS__ . '/skin/plupload/plupload.js'));
            $cssjs->register_js('plupload.i18n', array('src' => __WWW_ROOT_JSTOOLS__ . '/skin/plupload/i18n/' . $request->LANG . '.js'));
            $cssjs->register_js('plupload.runtime.html5', array('src' => __WWW_ROOT_JSTOOLS__ . '/skin/plupload/plupload.html5.js'));
            $cssjs->register_js('plupload.runtime.flash', array('src' => __WWW_ROOT_JSTOOLS__ . '/skin/plupload/plupload.flash.js'));
            $cssjs->register_js('plupload.runtime.html4', array('src' => __WWW_ROOT_JSTOOLS__ . '/skin/plupload/plupload.html4.js'));
            $cssjs->register_foot('clementine_crud-plupload', $this->getBlockHtml('crud/js_plupload', $this->data));
        }
        // table hover effects
        $cssjs->register_foot('clementine_crud-list_table_hover', $this->getBlockHtml('crud/js_list_table_hover', $this->data));
        // dataTables : sortable tables
        $cssjs->register_css('jquery.dataTables',  array('src' => __WWW_ROOT_JSTOOLS__ . '/skin/js/jquery.dataTables/dataTables.css'));
        $cssjs->register_js('jquery.dataTables', array('src' => __WWW_ROOT_JSTOOLS__ . '/skin/js/jquery.dataTables/jquery.dataTables.min.js'));
        $cssjs->register_foot('clementine_crud-datatables', $this->getBlockHtml('crud/js_datatables', $this->data));
        // alert on delbutton
        $cssjs->register_foot('clementine_crud-delbutton_confirm', $this->getBlockHtml('crud/js_delbutton_confirm', $this->data));
    }

    /**
     * handle_ajax_filtering : datatables ajax filtering (recherche dans les champs affichés uniquement)
     * 
     * @param mixed $champs_affiches 
     * @param mixed $metas 
     * @param mixed $params 
     * @access public
     * @return void
     */
    public function handle_ajax_filtering($champs_affiches, $metas, $params = null)
    {
        $db = $this->getModel('db');
        $filter_where = '';
        $sSearch = "";
        // recupere la search string, considere % et _ comme des caractères normaux, et met * comme joker
        if (isset($params['get']['sSearch'])) {
            $sSearch = $params['get']['sSearch'];
            $sSearch = str_replace('_', '\\_', $sSearch);
            $sSearch = str_replace('%', '\\%', $sSearch);
            $sSearch = str_replace('*', '%', $sSearch);
        }
        if ($sSearch != "") {
            $ns = $this->getModel('fonctions');
            foreach ($champs_affiches as $champ_affiche) {
                $nom_champ_affiche = $champ_affiche;
                if (isset($this->data['fields'][$champ_affiche]) && $this->data['fields'][$champ_affiche]['type'] == 'custom_field') {
                    if (isset($metas['custom_fields'][$champ_affiche])) {
                        $nom_champ_affiche = $metas['custom_fields'][$champ_affiche];
                    } else {
                        // champ custom_field qui n'a aucune définition SQL
                        continue;
                    }
                }
                if (isset($metas['custom_search'][$champ_affiche])) {
                    if ($metas['custom_search'][$champ_affiche] != '0') {
                        // si le custom_field est un GROUP_CONCAT par exemple, on ne peut pas faire un like directement sur GROUP_CONCAT(monchamp), donc on le fait directement sur monchamp
                        $filter_where .= "\n    " . $metas['custom_search'][$champ_affiche] . " LIKE '%" . $db->escape_string($sSearch) . "%' OR ";
                        // recherche aussi la version encodée en HTML
                        $filter_where .= "\n    " . $metas['custom_search'][$champ_affiche] . " LIKE '%" . $db->escape_string($ns->htmlentities($sSearch, ENT_QUOTES)) . "%' OR ";
                    }
                } else {
                    $filter_where .= "\n    " . $nom_champ_affiche . " LIKE '%" . $db->escape_string($sSearch) . "%' OR ";
                    // recherche aussi la version encodée en HTML
                    $filter_where .= "\n    " . $nom_champ_affiche . " LIKE '%" . $db->escape_string($ns->htmlentities($sSearch, ENT_QUOTES)) . "%' OR ";
                }
            }
            $filter_where = substr($filter_where, 0, -3);
        }
        return $filter_where;
    }

    /**
     * override_fields : surcharge les types de champs
     * 
     * @access public
     * @return void
     */
    public function override_fields($params = null)
    {
        /*$this->data['fields']['table.field']['type']                       = 'file';*/
        /*$this->data['fields']['table.field']['parameters']                 = array();*/
        /*$this->data['fields']['table.field']['parameters']['max_filesize'] = 10000000;*/
        /*$this->data['fields']['table.field']['parameters']['extensions']   = array('jpg', 'pdf');*/
        /*$this->data['fields']['table.field']['parameters']['dest_dir']     = __FILES_ROOT__ . '/files/media';*/
    }

    public function override_fields_index($params = null)
    {
    }

    public function override_fields_create_or_update($params = null)
    {
    }

    public function override_fields_read($params = null)
    {
    }

    /**
     * alter_values : fonctions appelee par indexAction, updateAction et readAction
     *                pour passer sur toutes les valeurs chargees et les modifier avant
     *                de les transmettre à la vue. Pour changer le format d'une date, etc...
     *                NOTE : pas idéal niveau performances
     * 
     * @param mixed $params 
     * @access public
     * @return void
     */
    public function alter_values($params = null)
    {
    }

    public function alter_values_index($params = null)
    {
    }

    public function alter_values_create_or_update($params = null)
    {
    }

    public function alter_values_read($params = null)
    {
    }

    /**
     * rename_fields : fonction appellée par index, creation, read, et update
     *                 pour renommer les champs avant affichage, par des appels 
     *                 à mapFieldName normalement...
     * 
     * @param mixed $params 
     * @access public
     * @return void
     */
    public function rename_fields($params = null)
    {
    }

    public function rename_fields_index($params = null)
    {
    }

    public function rename_fields_create_or_update($params = null)
    {
    }

    public function rename_fields_read($params = null)
    {
    }

    /**
     * hide_fields : fonction appellée par index, creation, read, et update
     *               pour masquer les champs par des appels a hideField
     * 
     * @param mixed $params 
     * @access public
     * @return void
     */
    public function hide_fields($params = null)
    {
    }

    public function hide_fields_index($params = null)
    {
    }

    public function hide_fields_create_or_update($params = null)
    {
    }

    public function hide_fields_read($params = null)
    {
    }

    /**
     * add_fields : fonction appellée par index, creation, read, et update
     *              pour ajouter des champs par des appels a addField
     * 
     * @param mixed $params 
     * @access public
     * @return void
     */
    public function add_fields($params = null)
    {
    }

    public function add_fields_index($params = null)
    {
    }

    public function add_fields_create_or_update($params = null)
    {
    }

    public function add_fields_read($params = null)
    {
    }

    /**
     * move_fields : fonction appellée par index, creation, read, et update
     *               pour ajouter des champs par des appels a moveField
     * 
     * @param mixed $params 
     * @access public
     * @return void
     */
    public function move_fields($params = null)
    {
    }

    public function move_fields_index($params = null)
    {
    }

    public function move_fields_create_or_update($params = null)
    {
    }

    public function move_fields_read($params = null)
    {
    }

    /**
     * hide_sections : fonction appellée par index, creation, read, et update
     *                 pour masquer des sections par des appels a hideSection
     * 
     * @param mixed $params 
     * @access public
     * @return void
     */
    public function hide_sections($params = null)
    {
    }

    public function hide_sections_index($params = null)
    {
    }

    public function hide_sections_create_or_update($params = null)
    {
    }

    public function hide_sections_read($params = null)
    {
    }

}
?>
