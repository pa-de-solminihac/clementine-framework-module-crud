<?php
class crudCrudController extends crudCrudController_Parent
{

    /**
     * =========
     * Attributs
     * =========
     */

    /**
     * mapping_to_HTML : mapping des champs SQL => HTML pour les formulaires
     *                   defaults to 'input type="text"'
     *
     */
    public $mapping_to_HTML = array(
        'checkbox'     => 'checkbox',
        'bit'          => 'checkbox',
        'boolean'      => 'checkbox',
        'togglebutton' => 'togglebutton',
        'select'       => 'select',
        'enum'         => 'select',
        'set'          => 'select',
        'number'       => 'number',
        'tinyint'      => 'number',
        'int'          => 'number',
        'smallint'     => 'number',
        'mediumint'    => 'number',
        'bigint'       => 'number',
        'textarea'     => 'textarea',
        'tinytext'     => 'textarea',
        'text'         => 'textarea', // le type SQL "text" => textarea, si on veut un input type="text" on peut choisir le type "input"
        'input'        => 'text',
        'mediumtext'   => 'textarea',
        'longtext'     => 'textarea',
        'password'     => 'password',
        'tel'          => 'tel',
        'url'          => 'url',
        'email'        => 'email',
        'search'       => 'search',
        'timestamp'    => 'datetime',
        'datetime'     => 'datetime',
        'date'         => 'date',
        'time'         => 'time',
        'month'        => 'month',
        'week'         => 'week',
        'number'       => 'number',
        'range'        => 'range',
        'color'        => 'color',
        'radio'        => 'radio',
        'html'         => 'html',
        'file'         => 'file',
        'hidden'       => 'hidden',
        'span'         => 'span',
    );

    /**
     * formId : id aléatoire pour les formulaires générés
     */
    public $formId;

    /**
     * _class : nom de la classe instanciée
     */
    protected $_class;

    /**
     * _crud : modèle CRUD instancié
     */
    protected $_crud;

    /**
     * options : desactive l'autoload des valeurs des clés étrangères
     *           utile pour des raisons de performances
     */
    private $options = array(
        'url_parameters' => array(),
        'autoload_foreign_keys_values' => false,
    );

    public function setOption($key, $val)
    {
        $this->options[$key] = $val;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function getOption($key)
    {
        if (array_key_exists($key, $this->options)) {
            return $this->options[$key];
        }
        return $false;
    }

    /**
     * ============
     * Constructeur
     * ============
     */

    /**
     * __construct : instancie le modèle et récupère la liste des champs dans $this->data['fields']
     */
    public function __construct($request, $params = null)
    {
        $this->formId = uniqid();
        $this->data['formId'] = $this->formId;
        $class = strtolower(substr(get_class($this), 0, -10));
        $this->_class = $class;
        //cette classe est destinee a etre surchargee elle ne doit servir a rien sinon !
        if ($this->_class == 'crud') {
            return false;
        }
        $this->set_options($request, $params);
        $this->_crud = $this->getModel($this->_class, $params);
        if (!isset($this->data['class'])) {
            $this->data['class'] = $this->_class;
        }
        if (!isset($this->data['fields'])) {
            $to_merge = array();
            $to_merge['fields'] = $this->_crud->fields;
            $this->merge_fields($to_merge);
        }
    }

    /**
     * ============
     * Actions CRUD
     * ============
     */

    /**
     * indexAction : liste des enregistrements
     *               peut renvoyer un export XLS si $_GET['export_xls'],
     *               sans noms de colonnes si $_GET['export_xls_onlydata']
     *               compatible AJAX
     *
     * @access public
     * @return void
     */
    public function indexAction($request, $params = null)
    {
        $this->need_privileges($request, $params);
        $this->need_privileges_index($request, $params);
        $this->data['return_json'] = 0;
        // cette classe est destinee a etre surchargee, elle ne doit servir a rien sinon !
        if (get_class($this) == 'CrudController') {
            $this->trigger404();
        }
        $this->setFormLinks('update');
        $this->data['url_parameters'] = $this->getOption('url_parameters');
        $this->data['more_classes_wrap'] = array();
        $this->data['more_classes_table'] = array();
        $this->data['more_classes_xlsbutton'] = array();
        $this->data['more_classes_createbutton'] = array();
        $this->data['more_classes_link'] = array();
        $this->data['button_label_create'] = 'Nouveau';
        $this->data['button_label_xls'] = 'Exporter';
        // autoclick configurable dans le .ini
        $config = $this->getModuleConfig();
        $this->data['datetime_format'] = $config['default_datetime_format'];
        $this->data['date_format'] = $config['default_date_format'];
        $this->data['time_format'] = $config['default_time_format'];
        if (!isset($this->data['autoclick'])) {
            $this->data['autoclick'] = 0;
        }
        if (isset($config['autoclick'])) {
            $this->data['autoclick'] = $config['autoclick'];
        }
        // recupere les valeurs postees
        $this->get_unquoted_gpc($params);
        $to_merge = array();
        $to_merge['tables'] = $this->_crud->tables;
        // fonctions pour faciliter la surcharge
        $this->add_fields($request, $params);
        $this->add_fields_index($request, $params);
        $to_merge['metas'] = $this->_crud->metas;
        // gestion des champs masques
        foreach ($this->_crud->metas['hidden_fields'] as $key => $val) {
            if ($val) {
                $to_merge['metas']['hidden_fields'][$key] = 1;
            }
        }
        $to_merge['mapping'] = $this->mapping_to_HTML;
        $this->merge_defaults($to_merge);
        $this->override_fields($request, $params);
        $this->override_fields_index($request, $params);
        $this->move_fields($request, $params);
        $this->move_fields_index($request, $params);
        $this->rename_fields($request, $params);
        $this->rename_fields_index($request, $params);
        $this->hide_sections($request, $params);
        $this->hide_sections_index($request, $params);
        $this->hide_fields($request, $params);
        $this->hide_fields_index($request, $params);
        $this->override_url($request, $params);
        $this->override_url_index($request, $params);
        // export XLS si on ajoute dans l'URL &export_xls&sEcho=1
        if (isset($params['get']['export_xls'])) {
            $this->data['export_xls'] = 1;
            if (isset($params['get']['export_xls_onlydata'])) {
                $this->data['export_xls_onlydata'] = 1;
            }
            $this->data['return_json'] = 1;
            if (!defined('__NO_DEBUG_DIV__')) {
                define('__NO_DEBUG_DIV__', true);
            }
            $params['limit'] = false;
            $params['sql_calc_found_rows'] = false;
        }
        // paging
        if (isset($params['get']['iDisplayStart'])) {
            $this->data['return_json'] = 1;
            if (!defined('__NO_DEBUG_DIV__')) {
                define('__NO_DEBUG_DIV__', true);
            }
            if (isset($params['get']['iDisplayLength']) && ($params['get']['iDisplayLength'] != '-1')) {
                $params['limit'] = (int)$params['get']['iDisplayStart'] . ', ' . (int)$params['get']['iDisplayLength'];
                $params['sql_calc_found_rows'] = true;
            }
        }
        // liste des champs affiches
        $champs_affiches = array();
        $champs_recherche = array();
        $firstrow = $this->data['fields'];
        foreach ($firstrow as $tablefield => $val) {
            $fieldmeta = $this->data['fields'][$tablefield];
            $hidden = 0;
            if (isset($this->data['metas']['hidden_fields'][$tablefield]) && $this->data['metas']['hidden_fields'][$tablefield]) {
                $hidden = 1;
            }
            if (!$hidden) {
                $champs_affiches[] = $tablefield;
                $champs_recherche[] = $tablefield;
            }
            if (isset($this->data['metas']['search_fields'][$tablefield])) {
                $champs_recherche[] = $tablefield;
            }
        }
        // sorting / ordering
        if (isset($params['get']['iSortCol_0'])) {
            $this->data['return_json'] = 1;
            if (!defined('__NO_DEBUG_DIV__')) {
                define('__NO_DEBUG_DIV__', true);
            }
            $order_by = array();
            $sort_ways = array(
                'asc' => 'ASC',
                'desc' => 'DESC'
            );
            for ($i = 0; $i < (int)$params['get']['iSortingCols']; ++$i) {
                if ($params['get']['bSortable_' . (int)$params['get']['iSortCol_' . $i]] == "true") {
                    if (isset($champs_affiches[(int)$params['get']['iSortCol_' . $i]]) && isset($sort_ways[$params['get']['sSortDir_' . $i]])) {
                        $sort_field = $champs_affiches[(int)$params['get']['iSortCol_' . $i]];
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
        $filter_where = $this->handle_ajax_filtering($champs_recherche, $this->data['metas'], $params);
        if ($filter_where) {
            if (!isset($params['where'])) {
                $params['where'] = ' 1 ';
            }
            $params['where'].= ' AND (' . $filter_where . ') ';
        }
        $cssjs = $this->getModel('cssjs');
        // charge les valeurs pour les clés étrangères
        if ($this->getOption('autoload_foreign_keys_values')) {
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
        $this->register_ui_scripts('index', $params);
        if ($cssjs->is_registered_foot('clementine_crud-datatables')) {
            if (isset($params['get']['iDisplayLength'])) {
                $values = $this->_crud->getList($params);
            } else {
                // on charge quand meme un element
                $fake_params = $params;
                if (!isset($fake_params['limit'])) {
                    $fake_params['limit'] = '1'; // limit a 1 pour eviter le contenu qui apparait en flash et éviter du gaspillage de ressources
                }
                $values = $this->_crud->getList($fake_params);
            }
        } else {
            $values = $this->_crud->getList($params);
        }
        $to_merge['values'] = $values;
        $this->merge_values($to_merge);
        // prise en compte des champs ajoutés
        $this->merge_added_fields($params);
        // recupere le nombre total de resultats (hors limit)
        if (isset($params['sql_calc_found_rows']) && $params['sql_calc_found_rows']) {
            $this->data['nb_total_values'] = $this->getModel('db')->found_rows();
        } else {
            $this->data['nb_total_values'] = count($this->data['values']);
        }
        // par défaut, on masque les boutons afficher, dupliquer et XLS
        if (!isset($this->data['hidden_sections']['readbutton'])) {
            $this->hideSection('readbutton');
        }
        if (!isset($this->data['hidden_sections']['duplicatebutton'])) {
            $this->hideSection('duplicatebutton');
        }
        if (!isset($this->data['hidden_sections']['xlsbutton'])) {
            $this->hideSection('xlsbutton');
        }
        $this->alter_values($request, $params);
        $this->alter_values_index($request, $params);
        // export xls si demande
        if (isset($this->data['return_json']) && $this->data['return_json'] && isset($this->data['export_xls'])) {
            if (isset($this->data['export_xls'])) {
                $a_exporter = unserialize($this->getBlockHtml($this->data['class'] . '/index', $this->data, $request));
                if (isset($this->data['export_xls_onlydata'])) {
                    return $a_exporter;
                }
                $ns = $this->getModel('fonctions');
                $ns->matrix2xls($a_exporter['filename'], $a_exporter['donnees'], $a_exporter['header_titles']);
            }
        }
    }

    /**
     * createAction : création d'un nouvel enregistrement
     *
     * @access public
     * @return void
     */
    public function createAction($request, $params = null)
    {
        $config = $this->getModuleConfig();
        $this->need_privileges($request, $params);
        $this->need_privileges_create_or_update($request, $params);
        $ns = $this->getModel('fonctions');
        // cette classe est destinee a etre surchargee, elle ne doit servir a rien sinon !
        if (get_class($this) == 'CrudController') {
            $this->trigger404();
        }
        $this->setFormLinks('create');
        $this->data['url_parameters'] = $this->getOption('url_parameters');
        $this->data['more_classes_wrap'] = array();
        $this->data['more_classes_form'] = array();
        $this->data['more_classes_field_wrap'] = array();
        $this->data['more_classes_field_key'] = array();
        $this->data['more_classes_field_val_div'] = array();
        $this->data['more_classes_field_val'] = array();
        $this->data['more_classes_field_comment'] = array();
        $this->data['more_classes_field_checkbox'] = array();
        $this->data['more_classes_backbutton'] = array();
        $this->data['more_classes_savebutton'] = array();
        $this->data['more_classes_delbutton'] = array();
        $this->data['button_label_back'] = 'Annuler';
        $this->data['button_label_save'] = 'Enregistrer';
        $this->data['button_label_del'] = 'Supprimer';
        $errors = array();
        // recupere les valeurs postees
        $this->get_unquoted_gpc($params);
        // charge les metadonnees
        $to_merge = array();
        $to_merge['tables'] = $this->_crud->tables;
        $to_merge['metas'] = $this->_crud->metas;
        /*$to_merge['fields']  = $this->_crud->fields;*/
        $to_merge['mapping'] = $this->mapping_to_HTML;
        $this->merge_defaults($to_merge);
        $this->merge_values($to_merge);
        // fonctions pour faciliter la surcharge
        $this->add_fields($request, $params);
        $this->add_fields_create_or_update($request, $params);
        $this->override_fields($request, $params);
        $this->override_fields_create_or_update($request, $params);
        $this->move_fields($request, $params);
        $this->move_fields_create_or_update($request, $params);
        $this->rename_fields($request, $params);
        $this->rename_fields_create_or_update($request, $params);
        $this->wrap_fields($request, $params);
        $this->wrap_fields_create_or_update($request, $params);
        $this->hide_sections($request, $params);
        $this->hide_sections_create_or_update($request, $params);
        $this->hide_fields($request, $params);
        $this->hide_fields_create_or_update($request, $params);
        $this->override_url($request, $params);
        $this->override_url_create_or_update($request, $params);
        // charge les valeurs pour les clés étrangères
        if ($this->getOption('autoload_foreign_keys_values')) {
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
        // get multipart_uploads default value from config file
        foreach ($this->data['fields'] as $tablefield => $fieldmeta) {
            if (!empty($this->data['fields'][$tablefield]['type']) && ($this->data['fields'][$tablefield]['type'] == 'file')) {
                if (!isset($this->data['fields'][$tablefield]['parameters']['multipart_uploads'])) {
                    $this->data['fields'][$tablefield]['parameters']['multipart_uploads'] = $config['multipart_uploads'];
                }
            }
        }
        // enregistre les valeurs si possible
        $last_insert_ids = 0;
        if (count($request->POST)) {
            // gere l'upload de fichiers
            $ret = $this->handle_uploading($params, $errors);
            if ($ret) {
                return $ret;
            }
            // nettoie les valeurs postées
            $params['post'] = $this->sanitize($params['post'], $params);
            $params['post'] = $this->alter_post($params['post'], $params);
            $validate_errs = $this->validate($params['post'], $params['get'], $params);
            $move_errs = array();
            $uploaded_files = array();
            if (!count($validate_errs) && !count($errors)) {
                if ($ns->ifGet('int', 'duplicate')) {
                    $params['duplicate'] = 1;
                }
                $result = $this->handle_uploaded_files($params, $errors, 'create');
                $uploaded_files = $result['uploaded_files'];
                $move_errs = $result['move_errs'];
            }
            if (!count($validate_errs) && !count($errors) && !count($move_errs)) {
                // enregistre les valeurs postees
                if (!isset($params['dont_start_transaction'])) {
                    $params['dont_start_transaction'] = false;
                }
                $module_name = $this->getCurrentModule();
                $err = $this->getHelper('errors');
                $err->flush($module_name);
                if (!$last_insert_ids = $this->_crud->create($params['post'], $params)) {
                    $create_errors = $err->get($module_name, 'create');
                    $errmsg = 'erreur rencontree lors de la creation';
                    if (__DEBUGABLE__ && Clementine::$config['clementine_debug']['display_errors']) {
                        $errmsg .= ' : ' . print_r($create_errors, true);
                    }
                    $errors[] = $errmsg;
                } else {
                    if (!isset($params['url_retour'])) {
                        $query_string = array();
                        foreach ($last_insert_ids as $table => $champs) {
                            foreach ($champs as $champ => $val) {
                                $query_string[$table . '-' . $champ] = $val;
                            }
                        }
                        $params['url_retour'] = __WWW__ . '/' . $this->_class . '/index?' . http_build_query($query_string);
                    }
                }
                $err->flush($module_name);
            } else {
                $errors = array_merge($errors, $validate_errs);
            }
        }
        // charge les donnees
        $this->register_ui_scripts('create', $params);
        $values = array(
            0 => ''
        );
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
            $values = array(
                0 => $ns->array_first($this->_crud->get($params['get']))
            );
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
        $to_merge['values'] = $values;
        $to_merge['errors'] = $errors;
        $this->merge_defaults($to_merge);
        $this->merge_values($to_merge);
        $this->merge_added_fields($params);
        $this->alter_values($request, $params);
        $this->alter_values_create_or_update($request, $request, $params);
        if (!isset($params['dont_handle_errors'])) {
            $params['dont_handle_errors'] = false;
        }
        if (count($params['post'])) {
            // si on a surchargé createAction ou updateAction en leur passant "dont_handle_errors',
            // leur appel de handle_uploading peut faire qu'elles retournent retourne dontGetBlock au lieu de $errors...
            // il faut alors faire suivre le return directement !
            if ($errors == $this->dontGetBlock()) {
                return $errors;
            }
            if (!$params['dont_handle_errors']) {
                return $this->handle_errors($request, $errors, $params);
            } else {
                return $errors;
            }
        }
    }

    /**
     * readAction : affichage d'un enregistrement
     *
     * @access public
     * @return void
     */
    public function readAction($request, $params = null)
    {
        $this->need_privileges($request, $params);
        $this->need_privileges_read($request, $params);
        // cette classe est destinee a etre surchargee, elle ne doit servir a rien sinon !
        if (get_class($this) == 'CrudController') {
            $this->trigger404();
        }
        $this->setFormLinks('read');
        $this->data['url_parameters'] = $this->getOption('url_parameters');
        $this->data['more_classes_wrap'] = array();
        $this->data['more_classes_field_wrap'] = array();
        $this->data['more_classes_field_key'] = array();
        $this->data['more_classes_field_val'] = array();
        $this->data['more_classes_img'] = array();
        $errors = array();
        // recupere les valeurs postees
        $this->get_unquoted_gpc($params);
        // charge les valeurs pour les clés étrangères
        if ($this->getOption('autoload_foreign_keys_values')) {
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
        $values = $this->_crud->get($params['get'], $params);
        if (!is_array($values) || (count($values) !== 1)) {
            $errors[] = 'element non trouvé';
            $values = array();
        }
        $to_merge = array();
        $to_merge['values'] = $values;
        $to_merge['errors'] = $errors;
        $to_merge['tables'] = $this->_crud->tables;
        $to_merge['metas'] = $this->_crud->metas;
        /*$to_merge['fields']  = $this->_crud->fields;*/
        $to_merge['mapping'] = $this->mapping_to_HTML;
        $this->merge_defaults($to_merge);
        $this->merge_values($to_merge);
        // prise en compte des champs ajoutés
        $this->merge_added_fields($params);
        // fonctions pour faciliter la surcharge
        $this->add_fields($request, $params);
        $this->add_fields_read($request, $params);
        $this->override_fields($request, $params);
        $this->override_fields_read($request, $params);
        $this->move_fields($request, $params);
        $this->move_fields_read($request, $params);
        $this->rename_fields($request, $params);
        $this->rename_fields_read($request, $params);
        $this->wrap_fields($request, $params);
        $this->wrap_fields_read($request, $params);
        $this->hide_sections($request, $params);
        $this->hide_sections_read($request, $params);
        $this->hide_fields($request, $params);
        $this->hide_fields_read($request, $params);
        $this->override_url($request, $params);
        $this->override_url_read($request, $params);
        // pas d'element, ou en tout cas pas accessible... on renvoie un header 404
        if (!count($this->data['values'])) {
            if (__DEBUGABLE__ && Clementine::$config['clementine_debug']['display_errors']) {
                $this->getHelper('debug')->unknown_element();
            }
            $this->trigger404();
        }
        // charge les valeurs pour les clés étrangères
        //if ($this->getOption('autoload_foreign_keys_values')) {
            //TODO: (code obsolète supprimé)
        //}
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
        $this->alter_values($request, $params);
        $this->alter_values_read($request, $params);
        $this->register_ui_scripts('read', $params);
    }

    /**
     * updateAction : edition et sauvegarde d'un enregistrement
     *
     * @access public
     * @return void
     */
    public function updateAction($request, $params = null)
    {
        $config = $this->getModuleConfig();
        $this->need_privileges($request, $params);
        $this->need_privileges_create_or_update($request, $params);
        // cette classe est destinee a etre surchargee, elle ne doit servir a rien sinon !
        if (get_class($this) == 'CrudController') {
            $this->trigger404();
        }
        $this->setFormLinks('update');
        $this->data['url_parameters'] = $this->getOption('url_parameters');
        $this->data['more_classes_wrap'] = array();
        $this->data['more_classes_form'] = array();
        $this->data['more_classes_field_wrap'] = array();
        $this->data['more_classes_field_key'] = array();
        $this->data['more_classes_field_val_div'] = array();
        $this->data['more_classes_field_val'] = array();
        $this->data['more_classes_field_comment'] = array();
        $this->data['more_classes_field_checkbox'] = array();
        $this->data['more_classes_backbutton'] = array();
        $this->data['more_classes_savebutton'] = array();
        $this->data['more_classes_delbutton'] = array();
        $this->data['button_label_back'] = 'Annuler';
        $this->data['button_label_save'] = 'Enregistrer';
        $this->data['button_label_del'] = 'Supprimer';
        // recupere les valeurs postees
        $this->get_unquoted_gpc($params);
        // charge les metadonnees
        $to_merge = array();
        $to_merge['tables'] = $this->_crud->tables;
        $to_merge['metas'] = $this->_crud->metas;
        /*$to_merge['fields']  = $this->_crud->fields;*/
        $to_merge['mapping'] = $this->mapping_to_HTML;
        $this->merge_defaults($to_merge);
        $this->merge_values($to_merge);
        // fonctions pour faciliter la surcharge
        $this->add_fields($request, $params);
        $this->add_fields_create_or_update($request, $params);
        $this->override_fields($request, $params);
        $this->override_fields_create_or_update($request, $params);
        $this->move_fields($request, $params);
        $this->move_fields_create_or_update($request, $params);
        $this->rename_fields($request, $params);
        $this->rename_fields_create_or_update($request, $params);
        $this->wrap_fields($request, $params);
        $this->wrap_fields_create_or_update($request, $params);
        $this->hide_sections($request, $params);
        $this->hide_sections_create_or_update($request, $params);
        $this->hide_fields($request, $params);
        $this->hide_fields_create_or_update($request, $params);
        $this->override_url($request, $params);
        $this->override_url_create_or_update($request, $params);
        // charge les valeurs pour les clés étrangères
        if ($this->getOption('autoload_foreign_keys_values')) {
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
        // get multipart_uploads default value from config file
        foreach ($this->data['fields'] as $tablefield => $fieldmeta) {
            if (!empty($this->data['fields'][$tablefield]['type']) && ($this->data['fields'][$tablefield]['type'] == 'file')) {
                if (!isset($this->data['fields'][$tablefield]['parameters']['multipart_uploads']) && $config['multipart_uploads']) {
                    $this->data['fields'][$tablefield]['parameters']['multipart_uploads'] = $config['multipart_uploads'];
                }
            }
        }
        // enregistre les valeurs si possible
        $errors = array();
        if (count($request->POST)) {
            // gere l'upload de fichiers
            $ret = $this->handle_uploading($params, $errors);
            if ($ret) {
                return $ret;
            }
            // nettoie les valeurs postées
            $params['post'] = $this->sanitize($params['post'], $params);
            $params['post'] = $this->alter_post($params['post'], $params);
            $validate_errs = $this->validate($params['post'], $params['get'], $params);
            $move_errs = array();
            $uploaded_files = array();
            if (!count($validate_errs) && !count($errors)) {
                $result = $this->handle_uploaded_files($params, $errors);
                $uploaded_files = $result['uploaded_files'];
                $move_errs = $result['move_errs'];
            }
            if (!count($validate_errs) && !count($errors) && !count($move_errs)) {
                // enregistre les valeurs postees
                if (!isset($params['dont_start_transaction'])) {
                    $params['dont_start_transaction'] = false;
                }
                if (!$this->_crud->update($params['post'], $params['get'], $params)) {
                    $errors[] = 'erreur rencontree lors de la sauvegarde';
                }
            } else {
                // TODO : suppression des fichiers uploades (dans un cron ?)
                $errors = array_merge($errors, $validate_errs, $move_errs);
            }
            if (!isset($params['url_retour'])) {
                $params['url_retour'] = __WWW__ . '/' . $this->_class . '/index?' . http_build_query($params['get']);
            }
        }
        // charge les donnees
        $this->register_ui_scripts('update', $params);
        // on ne passe pas de parametres supplementaires ici, c'est volontaire
        $values = $this->_crud->get($params['get']);
        if (!is_array($values) || (count($values) !== 1)) {
            $errors[] = 'element non trouvé';
            $values = array();
        }
        $to_merge = array();
        $to_merge['values'] = $values;
        $to_merge['errors'] = $errors;
        $this->merge_defaults($to_merge);
        $this->merge_values($to_merge);
        // prise en compte des champs ajoutés
        $this->merge_added_fields($params);
        $this->alter_values($request, $params);
        $this->alter_values_create_or_update($request, $params);
        if (!isset($params['dont_handle_errors'])) {
            $params['dont_handle_errors'] = false;
        }
        if (count($params['post'])) {
            // si on a surchargé createAction ou updateAction en leur passant "dont_handle_errors',
            // leur appel de handle_uploading peut faire qu'elles retournent retourne dontGetBlock au lieu de $errors...
            // il faut alors faire suivre le return directement !
            if ($errors == $this->dontGetBlock()) {
                return $errors;
            }
            if (!$params['dont_handle_errors']) {
                return $this->handle_errors($request, $errors, $params);
            } else {
                return $errors;
            }
        }
    }

    /**
     * deletetmpfile : supprime un fichier uploade durant la session courante (donc par l'utilisateur courant)
     *
     * @access public
     * @return void
     */
    public function deletetmpfileAction($request, $params = null)
    {
        $this->need_privileges($request, $params);
        $this->need_privileges_deletetmpfile($request, $params);
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
        $index = false;
        $fileSessionKey = false;
        $formId = $request->get('string', 'clementine_crud_formId');
        $session_crud_uploaded_files = 'crud_uploaded_files';
        if (!empty($params['crud_uploaded_files'])) {
            $session_crud_uploaded_files = $params['crud_uploaded_files'];
        }
        if (!empty($_SESSION[$session_crud_uploaded_files][$formId])) {
            foreach ($_SESSION[$session_crud_uploaded_files][$formId] as $field => $fieldFiles) {
                $fileSessionKey = array_search($filename, $fieldFiles);
                if ($fileSessionKey !== false) {
                    break;
                }
            }
            // si le fichier demandé est trouvé, on vide tous les fichiers correspondant à ce slot (pas le fichier seul)
            if ($fileSessionKey !== false) {
                foreach ($_SESSION[$session_crud_uploaded_files][$formId][$field] as $tmpfile) {
                    if (file_exists(__FILES_ROOT__ . '/tmp/' . $tmpfile)) {
                        unlink(__FILES_ROOT__ . '/tmp/' . $tmpfile);
                    }
                }
                $_SESSION[$session_crud_uploaded_files][$formId][$field] = array();
            }
        }
        return $this->dontGetBlock();
    }

    /**
     * deleteAction : suppression d'un enregistrement
     *
     * @access public
     * @return void
     */
    public function deleteAction($request, $params = null)
    {
        $this->need_privileges($request, $params);
        $this->need_privileges_delete($request, $params);
        // cette classe est destinee a etre surchargee, elle ne doit servir a rien sinon !
        if (get_class($this) == 'CrudController') {
            $this->trigger404();
        }
        $ns = $this->getModel('fonctions');
        // recupere les valeurs postees
        $this->get_unquoted_gpc($params);
        // transmet les donnees
        $errors = array();
        $to_merge = array();
        $to_merge['errors'] = $errors;
        $to_merge['tables'] = $this->_crud->tables;
        $to_merge['metas'] = $this->_crud->metas;
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
                $oldvalues_list = $this->_crud->get($params['get']);
                if (count($oldvalues_list)) {
                    $oldvalues = $ns->array_first($oldvalues_list);
                }
                // fix memory leak
                unset($oldvalues_list);
            }
            if (!$this->_crud->delete($params['get'], $params)) {
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
                $href = __WWW__ . '/' . $this->_class . '/index?id=';
                if (isset($params['url_retour'])) {
                    $href = $params['url_retour'];
                }
                foreach ($this->getOption('url_parameters') as $key => $val) {
                    $href = $ns->add_param($href, $key, $val, 1);
                }
                $ns->redirect($href);
            }
        }
    }

    /**
     * ===================================================================
     * Customizations : classes CSS utilisées pour générer les formulaires
     * ===================================================================
     */

    /**
     * addClass : ajoute une classe CSS sur les formulaires
     *            utilisable depuis les hooks alter_values_*
     *
     * @param mixed $type : clé de tableau $this->data['more_classes_*'] correspondant à la partie du formulaire concernée
     * @access public
     * @return void
     */
    public function addClass($type, $class)
    {
        if (isset($this->data[$type])) {
            $this->data[$type] = array_merge($this->data[$type], array(
                $class => $class
            ));
        } else {
            $this->data[$type] = array(
                $class => $class
            );
        }
    }

    /**
     * removeClass : enlève une classe CSS sur les formulaires
     *               utilisable depuis les hooks alter_values_*
     *
     * @param mixed $type : clé de tableau $this->data['more_classes_*'] correspondant à la partie du formulaire concernée
     * @access public
     * @return void
     */
    public function removeClass($type, $class)
    {
        unset($this->data[$type][$class]);
    }

    public function addClasses($type, $classes)
    {
        foreach ($classes as $class) {
            $this->addClass($type, $class);
        }
    }

    public function removeClasses($type, $classes)
    {
        foreach ($classes as $class) {
            $this->removeClass($type, $class);
        }
    }

    /**
     * ======================================
     * Customizations : affichage ou masquage
     * ======================================
     */

    /**
     * hideField : raccourci pour masquer un champ dans une vue
     *             utilisable depuis le hook hide_fields
     *
     * @param mixed $tablefield : $table.$field à masquer
     * @access public
     * @return void
     */
    public function hideField($tablefield)
    {
        $this->data['metas']['hidden_fields'][$tablefield] = true;
        return true;
    }

    /**
     * hideFields : appelle hideField pour plusieurs champs (permet de réunir les appels en un seul pour plus de lisibilité)
     *              utilisable depuis le hook hide_fields
     *
     * @param mixed $tablefields : liste de $table.$field à masquer
     * @access public
     * @return void
     */
    public function hideFields($tablefields)
    {
        foreach ($tablefields as $tablefield) {
            $this->hideField($tablefield);
        }
        return true;
    }

    /**
     * unhideField : raccourci pour forcer l'affichage d'un champ dans une vue
     *               utilisable depuis le hook hide_fields
     *
     * @param mixed $tablefield : $table.$field à afficher
     * @access public
     * @return void
     */
    public function unhideField($tablefield)
    {
        $this->data['metas']['hidden_fields'][$tablefield] = 0;
        return true;
    }

    /**
     * unhideFields : appelle unhideField pour plusieurs champs (permet de réunir les appels en un seul pour plus de lisibilité)
     *                utilisable depuis le hook hide_fields
     *
     * @param mixed $tablefields : liste de $table.$field dont forcer l'affichage
     * @access public
     * @return void
     */
    public function unhideFields($tablefields)
    {
        foreach ($tablefields as $tablefield) {
            $this->unhideField($tablefield);
        }
        return true;
    }

    /**
     * hideAllFields : masque tous les champs
     *                 utilisable depuis le hook hide_fields
     *
     * @access public
     * @return void
     */
    public function hideAllFields()
    {
        foreach ($this->_crud->fields as $key => $val) {
            $this->data['metas']['hidden_fields'][$key] = true;
        }
        return true;
    }

    /**
     * unhideAllFields : force l'affichage de tous les champs
     *                   utilisable depuis le hook hide_fields
     *
     * @access public
     * @return void
     */
    public function unhideAllFields()
    {
        foreach ($this->_crud->fields as $key => $val) {
            $this->data['metas']['hidden_fields'][$key] = false;
        }
        return true;
    }

    /**
     * setMandatoryField : raccourci pour rendre un champ obligatoire
     *                     utilisable depuis le hook override_fields
     *
     * @param mixed $tablefield : $table.$field à rendre obligatoire
     * @access public
     * @return void
     */
    public function setMandatoryField($tablefield)
    {
        $this->data['metas']['mandatory_fields'][$tablefield] = true;
        return true;
    }

    /**
     * unsetMandatoryField : raccourci pour rendre un champ facultatif
     *                       utilisable depuis le hook override_fields
     *
     * @param mixed $tablefield : $table.$field à rendre facultatif
     * @access public
     * @return void
     */
    public function unsetMandatoryField($tablefield)
    {
        $this->data['metas']['mandatory_fields'][$tablefield] = false;
        return true;
    }

    /**
     * setMandatoryFields : appelle setMandatoryField pour plusieurs champs (permet de réunir les appels en un seul pour plus de lisibilité)
     *                      utilisable depuis le hook override_fields
     *
     * @param mixed $tablefields : liste de $table.$field à rendre obligatoires
     * @access public
     * @return void
     */
    public function setMandatoryFields($tablefields)
    {
        foreach ($tablefields as $tablefield) {
            $this->setMandatoryField($tablefield);
        }
        return true;
    }

    /**
     * unsetMandatoryFields : appelle unsetMandatoryField pour plusieurs champs (permet de réunir les appels en un seul pour plus de lisibilité)
     *                        utilisable depuis le hook override_fields
     *
     * @param mixed $tablefields : liste de $table.$field à rendre facultatifs
     * @access public
     * @return void
     */
    public function unsetMandatoryFields($tablefields)
    {
        foreach ($tablefields as $tablefield) {
            $this->unsetMandatoryFields($tablefield);
        }
        return true;
    }

    /**
     * setMandatoryAllFields : rend tous les champs obligatoires
     *                         utilisable depuis le hook override_fields
     *
     * @access public
     * @return void
     */
    public function setMandatoryAllFields()
    {
        foreach ($this->_crud->fields as $key => $val) {
            $this->data['metas']['mandatory_fields'][$key] = true;
        }
        return true;
    }

    /**
     * unsetMandatoryAllFields : rend tous les champs facultatifs
     *                           utilisable depuis le hook override_fields
     *
     * @access public
     * @return void
     */
    public function unsetMandatoryAllFields()
    {
        foreach ($this->_crud->fields as $key => $val) {
            $this->data['metas']['mandatory_fields'][$key] = false;
        }
        return true;
    }

    /**
     * hideSection : raccourci pour masquer une section dans une vue
     *               utilisable depuis le hook hide_sections
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
     * hideSections : appelle hideSection pour plusieurs sections (permet de réunir les appels en un seul pour plus de lisibilité)
     *                utilisable depuis le hook hide_sections
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
     * unhideSection : raccourci pour demasquer une section dans une vue
     *                utilisable depuis le hook hide_sections
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
     * unhideSections : appelle unhideSection pour plusieurs sections (permet de réunir les appels en un seul pour plus de lisibilité)
     *                  utilisable depuis le hook hide_sections
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
     * =======================================
     * Customizations : champs supplementaires
     * =======================================
     */

    /**
     * addField : ajoute un champ "virtuel", sans valeur mais qui sera utilisé
     *            dans la génération des formulaires
     *            utile pour rajouter des champs calcules dans la page listing
     *            utilisable depuis le hook add_fields
     *
     * @param mixed $tablefield : nom du champ virtuel sous la forme $table.$field
     * @param mixed $before_tablefield : champ $table.$field avant lequel positionner le champ
     * @param mixed $fieldmeta : tableau de meta informations sur le champ,
     *                           par exemple : array('type' => 'varchar',
     *                                               'fieldvalues' => array('Foo' => 'foo',
     *                                                                      'Bar' => 'bar'),
     *                                               'default_value' => 'Bar')
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
            list($before_table, $before_field) = explode('.', $before_tablefield, 2);
            if (!$ns->array_insert_before(array(
                $tablefield => $fieldmeta
            ) , $this->data['fields'], $before_table . '.' . $before_field)) {
                $this->data['fields'][$tablefield] = $fieldmeta;
            }
        } else {
            $this->data['fields'][$tablefield] = $fieldmeta;
        }
        // ... dans les fields...
        if (isset($fieldmeta['default_value'])) {
            $default_val = $fieldmeta['default_value'];
            $this->data['fields'][$tablefield]['default_value'] = $default_val;
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
                if (!$before_tablefield || !$ns->array_insert_before(array(
                    $tablefield => $default_val
                ) , $row, $before_table . '.' . $before_tablefield)) {
                    $row[$tablefield] = $default_val;
                }
                $this->data['values'][$key] = $row;
            }
        }
        return true;
    }

    /**
     * ====================================
     * Customizations : position des champs
     * ====================================
     */

    /**
     * moveField : deplace un des champ utilisés pour la génération des
     *             formulaires dans l'entete et dans chacune des lignes du
     *             tableau de valeurs
     *             utilisable depuis le hook move_fields
     *
     * @param mixed $tablefield : $table.$field
     * @param mixed $before_tablefield : champ $table.$field avant lequel positionner le champ
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
            if (!$ns->array_insert_before(array(
                $tablefield => $fieldmeta
            ) , $this->data['fields'], $before_tablefield)) {
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
     * ===================================
     * Customizations : renomme des champs
     * ===================================
     */

    /**
     * getFieldName : raccourci pour récupérer le nom d'un champ
     *
     * @param mixed $tablefield : $table.$field
     * @param mixed $name
     * @access public
     * @return void
     */
    public function getFieldName($tablefield)
    {
        if (!empty($this->data['metas']['title_mapping'][$tablefield])) {
            return $this->data['metas']['title_mapping'][$tablefield];
        }
        $field = str_replace('_', ' ', preg_replace('/.*\./', '', $tablefield));
        return ucfirst($field);
    }

    /**
     * mapFieldName : raccourci pour renommer un champ dans une vue
     *                utilisable depuis le hook rename_fields
     *
     * @param mixed $tablefield : $table.$field à renommer
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
     *                  utilisable depuis le hook rename_fields
     *
     * @param mixed $tablefield : $table.$field dont on veut annuler le renommage
     * @param mixed $name : si différent de false, on n'annulera le renommage que
     *                      s'il a exactement la valeur $name
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
     * mapFieldNames : raccourci pour renommer plusieurs champs dans une vue
     *                 utilisable depuis le hook rename_fields
     *
     * @param mixed $tablefield_from_to : tableau associatif contenant FROM en clé, et TO en valeur
     *                                    toujours au format $table.$field
     * @access public
     * @return void
     */
    public function mapFieldNames($tablefield_from_to)
    {
        foreach ($tablefield_from_to as $from => $to) {
            $this->mapFieldName($from, $to);
        }
        return true;
    }

    //TODO: public function unmapFieldNames()

    /**
     * =====================================================
     * Customizations : type, paramètres, valeurs des champs
     * =====================================================
     */

    /**
     * overrideField : forcer le type et les paramètres d'un champ
     *                 utilisable depuis le hook override_fields
     *
     * @param mixed $tablefield : $table.$field
     * @param mixed $metas : tableau de metadonnees du champ, array(
     *          'type' => type choisi dans le tableau $this->mapping_to_HTML
     *          'size' => taille du champ
     *          'comment' => texte d'aide
     *          'custom_attr' => tableau clé-valeur d'attributs supplémentaires pour les champs input et textarea
     *      )
     * @param mixed $parameters : tableau de paramètres spécifiques selon le type choisi
     *      Par exemple pour un type 'file' on pourra avoir :
     *      $parameters = array(
     *          'max_filesize' => 10000000,
     *          'extensions'   => array('jpg', 'pdf'),
     *          'dest_dir'     => __FILES_ROOT__ . '/files/media',
     *      );
     * @access public
     * @return void
     */
    public function overrideField($tablefield, $metas = null, $parameters = null)
    {
        if (!empty($metas)) {
            foreach ($metas as $meta => $val) {
                $this->data['fields'][$tablefield][$meta] = $val;
            }
        }
        if (!empty($parameters)) {
            $ns = $this->getModel('fonctions');
            if (!isset($this->data['fields'][$tablefield]['parameters'])) {
                $this->data['fields'][$tablefield]['parameters'] = array();
            }
            $this->data['fields'][$tablefield]['parameters'] = $ns->array_replace_recursive($this->data['fields'][$tablefield]['parameters'], $parameters);
        }
        return true;
    }

    public function overrideFields($fields_metas)
    {
        foreach ($fields_metas as $field => $metas) {
            $this->overrideField($field, $metas);
        }
    }

    /**
     *  overrideUrlButton : force l'url d'un boutton utilisable depuis le hook override_urls
     *
     *  @param $button peut valoir back, del, updatebutton, readbutton, duplicatebutton, delbutton, create
     *  @param $url ce que l'on veut
     *  @access public
     *  @return void
     *
     */
    public function overrideUrlButton($button, $url = null)
    {
        if (!empty($url)) {
            $this->data['button_url_' . $button] = $url;
        }
    }

    /**
     * overrideUrlsButton : force l'url de plusieurs boutons. La clé représente le nom du bouton et la valeur associé est son url
     *
     *  @param $button_urls est un array de la forme array('nomBouton' => 'url')
     *  @access public
     *  @return void
     */
    public function overrideUrlsButtons($button_urls)
    {
        foreach ($button_urls as $button => $url) {
            $this->overrideUrl($button, $url);
        }
    }

    /**
     * overrideUrlRow : force l'url des liens présent dans les lignes de l'index du crud
     *
     * @param $url est l'url desiré
     * @access public
     * @return void
     *
     */
    public function overrideUrlRow($url)
    {
        if (!empty($url)) {
            $this->data['row_url'] = $url;
        }
    }

    /**
     * getFieldValues : raccourci pour récupérer les valeurs possibles d'un champ SELECT par exemple
     *
     * @param mixed $tablefield : $table.$field
     * @param mixed $name
     * @access public
     * @return void
     */
    public function getFieldValues($tablefield)
    {
        if (!empty($this->data['fields'][$tablefield]['fieldvalues'])) {
            return $this->data['fields'][$tablefield]['fieldvalues'];
        }
        return false;
    }

    /**
     * setFieldValues : affectation des valeurs possibles d'un champ
     *                  le champ sera affiché comme un SELECT
     *                  utilisable depuis le hook override_fields
     *
     * @param mixed $tablefield : $table.$field
     * @param mixed $values
     * @access public
     * @return void
     */
    public function setFieldValues($tablefield, $values)
    {
        if (isset($this->data['fields'][$tablefield])) {
            $this->data['fields'][$tablefield]['type'] = 'select';
            $this->data['fields'][$tablefield]['fieldvalues'] = (array) $values;
            return true;
        }
        return false;
    }

    /**
     * unsetFieldValues : raccourci pour vider les valeurs possibles d'un champ
     *                    dans une vue, ce qui fait du champ un SELECT
     *                    utilisable depuis le hook override_fields
     *
     * @param mixed $tablefield
     * @param mixed $values
     * @access public
     * @return void
     */
    public function unsetFieldValues($tablefield, $values)
    {
        if (isset($this->data['fields'][$tablefield])) {
            unset($this->data['fields'][$tablefield]['fieldvalues']);
            return true;
        }
        return false;
    }

    /**
     * getFieldValue : récupérer la valeur affectée à un champ
     *
     * @param mixed $tablefield : $table.$field auquel affecter une valeur par défaut
     * @access public
     * @return void
     */
    public function getFieldValue($tablefield)
    {
        $ns = $this->getModel('fonctions');
        $first_key = $ns->array_first_key($this->data['values']);
        if (!empty($this->data['values'][$first_key][$tablefield])) {
            return $this->data['values'][$first_key][$tablefield];
        }
        return false;
    }

    /**
     * setDefaultValue : affecter une valeur par défaut à un champ
     *                   utilisable depuis le hook alter_values
     *
     * @param mixed $tablefield : $table.$field auquel affecter une valeur par défaut
     * @param mixed $default_value : valeur par défaut
     * @access public
     * @return void
     */
    public function setDefaultValue($tablefield, $default_value = '', $params = null)
    {
        if ($this->data['formtype'] != 'create' && empty($params['force_default_value'])) {
            return false;
        }
        if (!count($this->data['values'])) {
            return false;
        }
        $ns = $this->getModel('fonctions');
        $first_key = $ns->array_first_key($this->data['values']);
        if (empty($this->data['values'][$first_key][$tablefield])) {
            $this->data['values'][$first_key][$tablefield] = $default_value;
        }
        return $this->data['values'][$first_key][$tablefield];
    }

    /**
     * setFormLinks : définit le type de liens générés
     *                valeurs possibles :
     *                - "update" liens vers crud/update (default)
     *                - "read" liens vers crud/read
     *                - "none" pas de liens
     *                - * pour lier vers crud/*
     *                utilisable depuis le hook alter_values
     *
     * @param mixed $link_type : valeur par défaut
     * @access public
     * @return void
     */
    public function setFormLinks($formtype = "update")
    {
        $this->data['formtype'] = $formtype;
    }

    /**
     * renameOption : renomme une OPTION d'un champ SELECT
     *                utilisable depuis le hook override_fields
     *
     * @param mixed $tablefield : $table.$field
     * @param mixed $from : current name
     * @param mixed $to : new name. if empty, style="display: none" will be added
     * @access public
     * @return void
     */
    public function renameOption($tablefield, $from, $to)
    {
        if (isset($this->data['fields'][$tablefield]['fieldvalues'][$from])) {
            $this->data['fields'][$tablefield]['fieldvalues'][$from] = $to;
            return true;
        }
        return false;
    }

    /**
     * renameOptions : raccourci pour renommer plusieurs options d'un champ SELECT
     *                 utilisable depuis le hook override_fields
     *
     * @param mixed $tablefield : $table.$field à renommer
     * @param mixed $from_to_array
     * @access public
     * @return void
     */
    public function renameOptions($tablefield, $from_to_array)
    {
        foreach ($from_to_array as $from => $to) {
            if (isset($this->data['fields'][$tablefield]['fieldvalues'][$from])) {
                $this->data['fields'][$tablefield]['fieldvalues'][$from] = $to;
            }
        }
        return true;
    }

    /**
     * ====================================
     * Customizations : regroupe des champs
     * ====================================
     */

    /**
     * wrapFields : envelopper des groupes de champs dans des blocks
     *
     * @param mixed $wrapper : tableau de la forme suivante :
     *      array(
     *          $from_fieldkey => $opening_block,
     *          $to_fieldkey => $closing_block,
     *      )
     *      avec :
     *          $from_fieldkey : premier champ qu'on veut envelopper (id HTML)
     *          $to_fieldkey : dernier champ qu'on veut envelopper (id HTML)
     *          $opening_block : block contenant le code HTML d'ouberture dans lequel on veut envelopper
     *          $closing_block : block contenant le code HTML de fermeture dans lequel on veut envelopper
     * @access public
     * @return void
     */
    public function wrapFields($wrapper)
    {
        $ns = $this->getModel('fonctions');
        if (!is_array($wrapper)) {
            $this->getHelper('debug')->wrapFields_wrong_params($wrapper);
        }
        $wrapper_keys = array_keys($wrapper);
        $from_fieldkey = $wrapper_keys[0];
        $opening_block = $wrapper[$from_fieldkey];
        if (count($wrapper_keys) > 1) {
            $to_fieldkey = $wrapper_keys[1];
            $closing_block = $wrapper[$to_fieldkey];
        } else {
            $to_fieldkey = $from_fieldkey;
            $closing_block = null;
        }
        // contrôles de cohérence
        $from_tablefield = str_replace('-', '.', $from_fieldkey);
        $to_tablefield = str_replace('-', '.', $to_fieldkey);
        if (!isset($this->data['fields'][$from_tablefield])) {
            $this->getHelper('debug')->unknown_field($from_fieldkey);
        }
        if (!isset($this->data['fields'][$to_tablefield])) {
            $this->getHelper('debug')->unknown_field($to_fieldkey);
        }
        // enregistrement du wrapper
        $wrapper = array(
            'opening_block' => $opening_block,
            'closing_block' => $closing_block,
            'from_fieldkey' => $from_fieldkey,
            'to_fieldkey' => $to_fieldkey
        );
        if (empty($this->data['wrappers']['open'][$from_fieldkey])) {
            $this->data['wrappers']['open'][$from_fieldkey] = array();
        }
        if (empty($this->data['wrappers']['open'][$to_fieldkey])) {
            $this->data['wrappers']['open'][$to_fieldkey] = array();
        }
        $this->data['wrappers']['open'][$from_fieldkey][] = $wrapper;
        $this->data['wrappers']['close'][$to_fieldkey][] = $wrapper;
    }

    /**
     * ===================================================
     * Customizations : nettoyage et validation des champs
     * ===================================================
     */

    /**
     * sanitize : filtre les valeurs du tableau $insecure_array
     *            renvoie le tableau filtré
     *            hook destiné à être surchargée
     *
     * @param mixed $insecure_array
     * @access public
     * @return void
     */
    public function sanitize($insecure_array, $params = null)
    {
        // cette fonction est destinée à être surchargée
        // par défaut, on sanitize les dates puis on appelle la fonction sanitize du modele
        $secure_array = $this->_crud->sanitizeValues($insecure_array);
        foreach ($secure_array as $fieldkey => $fieldval) {
            $tablefield = str_replace('-', '.', $fieldkey);
            if (isset($this->data['fields'][$tablefield]) && isset($this->data['fields'][$tablefield]['type'])) {
                switch ($this->data['fields'][$tablefield]['type']) {
                case 'date':
                    $secure_array[$fieldkey] = filter_var($secure_array[$fieldkey], FILTER_VALIDATE_REGEXP, array(
                        "options" => array(
                            "regexp" => "/^\d{4}-\d{2}-\d{2}$/"
                        )
                    ));
                    break;
                case 'time':
                    $secure_array[$fieldkey] = filter_var($secure_array[$fieldkey], FILTER_VALIDATE_REGEXP, array(
                        "options" => array(
                            "regexp" => "/^([01][0-9]|(2[0-3])):[0-5][0-9]$/"
                        )
                    ));
                    break;
                case 'datetime':
                    // padding date to datetime format
                    $date_format = '0000-00-00 00:00';
                    $date_padding_length = max(0, mb_strlen($date_format) - mb_strlen($secure_array[$fieldkey]));
                    $date_padding_string = mb_substr($date_format, - $date_padding_length);
                    if ($date_padding_length > 0) {
                        $secure_array[$fieldkey] = $secure_array[$fieldkey] . $date_padding_string;
                    }
                    $secure_array[$fieldkey] = filter_var($secure_array[$fieldkey], FILTER_VALIDATE_REGEXP, array(
                        "options" => array(
                            "regexp" => "/^\d{4}-\d{2}-\d{2} ([01][0-9]|(2[0-3])):[0-5][0-9]$/"
                        )
                    ));
                    break;
                }
            }
        }
        return $secure_array;
    }

    /**
     * alter_post : permet d'altérer les valeurs du tableau $insecure_array posté
     *              hook destiné à être surchargée
     *
     * @param mixed $insecure_array
     * @access public
     * @return void
     */
    public function alter_post($insecure_array, $params = null)
    {
        // cette fonction est destinée à être surchargée
        // pour générer des valeur à partir des champs custom par exemple
        // par défaut, on ne fait rien
        return $insecure_array;
    }

    /**
     * validate : valide les donnees avant creation ou mise à jour
     *            renvoie un tableau listant les erreurs rencontrees
     *            hook destiné à être surchargée
     *
     * @param mixed $insecure_values : tableau associatif 'table-champ' => 'valeur', par exemple $_POST
     * @param mixed $insecure_primary_key : tableau associatif 'table-champ' => 'valeur', par exemple $_GET
     * @access public
     * @return void
     */
    public function validate($insecure_values, $insecure_primary_key = null, $params = null)
    {
        // fonction destinée à être surchargée
        // par défaut : on vérifie juste si les champs obligatoires sont bien présents
        $my_errors = array();
        if (!empty($this->data['metas']['mandatory_fields'])) {
            foreach ($this->data['metas']['mandatory_fields'] as $tablefield => $is_mandatory) {
                if ($is_mandatory) {
                    $fieldkey = str_replace('.', '-', $tablefield);
                    if (!isset($insecure_values[$fieldkey]) || $insecure_values[$fieldkey] == '') {
                        if ($this->data['fields'][$tablefield]['type'] == 'file') {
                            if (!isset($insecure_values[$fieldkey . '-hidden']) || $insecure_values[$fieldkey . '-hidden'] == '') {
                                $my_errors[$fieldkey] = 'le champ ' . $this->getFieldName($tablefield) . ' est obligatoire';
                            }
                        } else {
                            $my_errors[$fieldkey] = 'le champ ' . $this->getFieldName($tablefield) . ' est obligatoire';
                        }
                    }
                }
            }
        }
        return $my_errors;
    }

    /**
     * =============================
     * Helpers : gestion des erreurs
     * =============================
     */

    public function handle_errors($request, $errors, $params = null)
    {
        // si on a surchargé createAction ou updateAction en leur passant "dont_handle_errors',
        // leur appel de handle_uploading peut faire qu'elles retournent retourne dontGetBlock au lieu de $errors...
        // il faut alors faire suivre le return directement !
        if ($errors == $this->dontGetBlock()) {
            return $errors;
        }
        $request = $this->getRequest();
        $ns = $this->getModel('fonctions');
        $values = $ns->array_first($this->data['values']);
        if (!count($errors)) {
            if (empty($params['url_retour'])) {
                $url_retour = __WWW__ . '/' . $this->_class . '/index?id=';
            } else {
                $url_retour = $params['url_retour'];
            }
            foreach ($this->getOption('url_parameters') as $key => $val) {
                $url_retour = $ns->add_param($url_retour, $key, $val, 1);
            }
            if (!empty($params['url_retour_parameters'])) {
                foreach ($params['url_retour_parameters'] as $url_parameter => $tablefield) {
                    if (!empty($values[$tablefield])) {
                        $url_retour = $ns->add_param($url_retour, $url_parameter, $values[$tablefield], 1);
                    }
                }
            }
            if ($request->AJAX) {
                echo '2';
                echo $url_retour;
                // pas un dontGetBlock ici car on ne veut pas que du code s'exécute après
                die();
            } else {
                $ns->redirect($url_retour);
            }
        } else {
            if ($request->AJAX) {
                // valeur de retour pour AJAX
                echo '1';
            }
            $this->getBlock($this->_class . '/errors', array(
                'errors' => $errors
            ), $request);
            die();
        }
    }

    /**
     * =============================
     * Helpers : gestion des uploads
     * =============================
     */

    public function handle_uploading(&$params, &$errors)
    {
        $ns = $this->getModel('fonctions');
        // determine upload_max_filesize
        $default_upload_max_filesize = $ns->get_max_filesize();
        $session_crud_uploaded_files = 'crud_uploaded_files';
        if (!empty($params['crud_uploaded_files'])) {
            $session_crud_uploaded_files = $params['crud_uploaded_files'];
        }
        if (!isset($_SESSION[$session_crud_uploaded_files])) {
            $_SESSION[$session_crud_uploaded_files] = array();
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
                    if ((isset($fieldmeta['parameters']) && (isset($fieldmeta['parameters']['max_filesize']) && $fileslot['size'] <= $fieldmeta['parameters']['max_filesize'])) || ($fileslot['size'] <= $default_upload_max_filesize)) {
                        $infosfichier = pathinfo($fileslot['name']);
                        $filename_upload = strtolower($infosfichier['filename']);
                        $extension_upload = strtolower($infosfichier['extension']);
                        $visiblename = $ns->urlize(basename($filename_upload)) . '.' . $extension_upload;
                        $fullname = uniqid() . '-' . $visiblename;
                        // $fullname = basename($fileslot['tmp_name']) . '.' . $extension_upload;
                        if (!(isset($fieldmeta['parameters']) && isset($fieldmeta['parameters']['extensions']) && count($fieldmeta['parameters']['extensions'])) || in_array($extension_upload, $fieldmeta['parameters']['extensions'])) {
                            if (move_uploaded_file($fileslot['tmp_name'], __FILES_ROOT__ . '/tmp/' . $fullname)) {
                                // enregistre le fichier dans la liste des fichiers uploadés
                                $request = $this->getRequest();
                                $formId = $request->post('string', 'clementine_crud_formId');
                                if (empty($_SESSION[$session_crud_uploaded_files][$formId][$fieldkey])) {
                                    $_SESSION[$session_crud_uploaded_files][$formId][$fieldkey] = array();
                                }
                                $_SESSION[$session_crud_uploaded_files][$formId][$fieldkey][] =$fullname;
                                // ecrase la valeur postee
                                $params['post'][$fieldkey . '-hidden'] = $fullname;
                                if ($is_ajax_upload) {
                                    echo '0';
                                    echo $fullname;
                                    echo ':';
                                    echo $visiblename;
                                    echo ':';
                                    return $this->dontGetBlock();
                                }
                            } else {
                                $err = 'Problème lors du déplacement du fichier';
                                $errors[] = $err;
                                if ($is_ajax_upload) {
                                    echo '1';
                                    echo $err;
                                    return $this->dontGetBlock();
                                }
                            }
                        } else {
                            $err = 'L\'extension du fichier n\'est pas supportée';
                            $errors[] = $err;
                            if ($is_ajax_upload) {
                                echo '1';
                                echo $err;
                                return $this->dontGetBlock();
                            }
                        }
                    } else {
                        $err = 'Le fichier est trop volumineux';
                        $errors[] = $err;
                        if ($is_ajax_upload) {
                            echo '1';
                            echo $err;
                            return $this->dontGetBlock();
                        }
                    }
                } else {
                    if (array_key_exists('name', $fileslot) && array_key_exists('tmp_name', $fileslot) && !$fileslot['tmp_name']) {
                        $err = 'Le fichier est trop volumineux pour ce serveur';
                        $errors[] = $err;
                        if ($is_ajax_upload) {
                            echo '1';
                            echo $err;
                            return $this->dontGetBlock();
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
                    $oldvalues_list = $this->_crud->get($params['get']);
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
                // cherche en session si plusieurs fichiers sont liés, et si c'est le cas on les joint en un fichier unique : PDF si ce ne sont que des JPG, PNG, PDF...
                // TODO: faire un ZIP sinon ?
                $formId = $params['post']['clementine_crud_formId'];
                $joint_file = null;
                $session_crud_uploaded_files = 'crud_uploaded_files';
                if (!empty($params['crud_uploaded_files'])) {
                    $session_crud_uploaded_files = $params['crud_uploaded_files'];
                }
                if (!empty($_SESSION[$session_crud_uploaded_files][$formId][$fieldkey_nothidden]) && count($_SESSION[$session_crud_uploaded_files][$formId][$fieldkey_nothidden]) > 1) {
                    $images = array();
                    foreach ($_SESSION[$session_crud_uploaded_files][$formId][$fieldkey_nothidden] as $i => $filename) {
                        $images[$i] = __FILES_ROOT__ . '/tmp/' . $filename;
                    }
                    $pdf = new Imagick($images);
                    $pdf->setImageFormat('pdf');
                    $joint_file_uniqid = uniqid();
                    $joint_file_dir = __FILES_ROOT__ . '/tmp';
                    $joint_file_basename = 'merge-' . $joint_file_uniqid;
                    $joint_file_extension = '.pdf';
                    $joint_file = $joint_file_dir . '/' . $joint_file_basename . $joint_file_extension;
                    $pdf->writeImages($joint_file, true);
                }
                // si le fichier a ete modifie, ou si on demande a le supprimer
                if ($file_changed || $remove_file) {
                    $src_tmpfile_name = $val;
                    $src_tmpfile_path = __FILES_ROOT__ . '/tmp/' . $val;
                    if ($joint_file) {
                        $src_tmpfile_name = $joint_file_basename . $joint_file_extension;
                        $src_tmpfile_path = $joint_file;
                    }
                    if ($file_changed && $src_tmpfile_name && !rename($src_tmpfile_path, $destdir . $src_tmpfile_name)) {
                        $move_errs[] = 'Impossible de déplacer le fichier ' . $tablefield_nothidden . ' vers sa destination. Problème de permissions ?';
                        $move_errs[] = 'rename(' . $src_tmpfile_path . ', ' . $destdir . $src_tmpfile_name;
                    } else {
                        // supprime le fichier precedent (sauf si duplication)
                        if (is_array($oldvalues) && array_key_exists($tablefield_nothidden, $oldvalues)) {
                            $previous_file = str_replace('__CLEMENTINE_CONTENUS_WWW_ROOT__', __FILES_ROOT__, $oldvalues[$tablefield_nothidden]);
                            // on ne touche pas aux fichiers précédents lors d'une duplication !
                            if ($previous_file && is_file($previous_file) && empty($params['duplicate'])) {
                                unlink($previous_file);
                            }
                        }
                        if ($src_tmpfile_name && !$remove_file) {
                            $uploaded_files[$fieldkey_nothidden] = $destdir . $src_tmpfile_name;
                            $params['post'][$fieldkey_nothidden] = str_replace(__FILES_ROOT__, '__CLEMENTINE_CONTENUS_WWW_ROOT__', $destdir) . $src_tmpfile_name;
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
                                    $args['filename'] = $destdir . $src_tmpfile_name;
                                    if (!isset($thumb['dest_dir'])) {
                                        // pas de dossier destination demande, on le determine a partir des dimensions demandees et du dossier destdir d'origine
                                        if (isset($args['canevaswidth']) && isset($args['canevasheight'])) {
                                            $thumb['dest_dir'] = $destdir . '/' . (int)$args['canevaswidth'] . 'x' . (int)$args['canevasheight'];
                                        } else {
                                            // on n'a pas fourni assez d'infos pour determiner le dossier de destination, tant pis pour cette miniature
                                            continue;
                                        }
                                    }
                                    $thumb['dest_dir'] = preg_replace('@//*@', '/', $thumb['dest_dir'] . '/'); // securite
                                    $args['save_filename'] = $thumb['dest_dir'] . $src_tmpfile_name;
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
                            $args['filename'] = $destdir . $src_tmpfile_name;
                            $args['save_filename'] = $destdir . $src_tmpfile_name;
                            $ns->img_resize($args);
                        }
                    }
                } else {
                    // on reste sur l'ancienne valeur
                    if (is_array($oldvalues) && array_key_exists($tablefield_nothidden, $oldvalues)) {
                        $params['post'][$fieldkey_nothidden] = $oldvalues[$tablefield_nothidden];
                    }
                }
                // supprime les fichiers temporaires devenus inutiles puisque joints en un PDF
                if ($joint_file) {
                    foreach ($images as $filename_to_delete) {
                        if (file_exists($filename_to_delete)) {
                            unlink($filename_to_delete);
                        }
                    }
                }
            }
        }
        $retour = array(
            'uploaded_files' => $uploaded_files,
            'move_errs' => $move_errs
        );
        return $retour;
    }

    /**
     * ================
     * Helpers : autres
     * ================
     */

    public function merge_defaults($to_merge)
    {
        $ns = $this->getModel('fonctions');
        $default_fields = array(
            'errors',
            'tables',
            'fields',
            'mapping',
            'wrappers',
            'metas'
        );
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

    // prise en compte des champs ajoutés
    public function merge_added_fields($params = null)
    {
        foreach ($this->data['values'] as $key => $val) {
            foreach ($this->data['fields'] as $fkey => $fval) {
                $default_val = '';
                if ($this->data['formtype'] == 'create' || !empty($params['force_default_value'])) {
                    if (isset($this->data['fields'][$fkey]['default_value'])) {
                        $default_val = $this->data['fields'][$fkey]['default_value'];
                    }
                }
                if (empty($this->data['values'][$key][$fkey])) {
                    $this->data['values'][$key][$fkey] = $default_val;
                }
            }
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
     * register_ui_scripts : appels cssjs->register_*
     *
     * @param mixed $params
     * @access public
     * @return void
     */
    public function register_ui_scripts($mode = 'index', $params = null)
    {
        $request = $this->getRequest();
        $cssjs = $this->getModel('cssjs');
        // jQuery
        $cssjs->register_foot('jquery', array(
            'src' => $this->getHelper('jquery')->getUrl()
        ));
        if (in_array($mode, array('create', 'update'))) {
            // plupload : ajax upload
            $cssjs->register_css('plupload_clementine', array(
                'src' => __WWW_ROOT_PLUPLOAD__ . '/skin/css/clementine-plupload.css'
            ));
            $cssjs->register_foot('moxie', array(
                'src' => __WWW_ROOT_PLUPLOAD__ . '/skin/js/moxie.js'
            ));
            $cssjs->register_foot('plupload', array(
                'src' => __WWW_ROOT_PLUPLOAD__ . '/skin/js/plupload.dev.js'
            ));
            $cssjs->register_foot('plupload.i18n', array(
                'src' => __WWW_ROOT_PLUPLOAD__ . '/skin/js/i18n/' . $request->LANG . '.js'
            ));
            $cssjs->register_foot('clementine_crud-plupload', $this->getBlockHtml($this->_class . '/js_plupload', $this->data, $request));
            //validation AJAX
            $cssjs->register_foot('valid_' . $mode . '_ajax', $this->getBlockHtml($this->_class . '/valid_' . $mode . '_ajax', $this->data, $request));
            $cssjs->register_foot($this->_class . '_datepicker', $this->getBlockHtml($this->_class . '/js_datepicker', $this->data));
        }
        if (in_array($mode, array('index', 'create', 'update'))) {
            $cssjs->register_css('valid_' . $mode . '_css', array(
                'src' => __WWW_ROOT_CRUD__ . '/skin/css/clementine_crud.css'
            ));
            // table autoclick effects
            $cssjs->register_foot('clementine_crud-list_table_autoclick', $this->getBlockHtml($this->_class . '/js_list_table_autoclick', $this->data, $request));
            // dataTables : sortable tables
            $cssjs->register_css('clementine-jquery.dataTables', array(
                'src' => __WWW_ROOT_JQUERYDATATABLES__ . '/skin/css/clementine-dataTables.css'
            ));
            $cssjs->register_css('jquery.dataTables', array(
                'src' => __WWW_ROOT_JQUERYDATATABLES__ . '/skin/css/jquery.dataTables.css'
            ));
            $cssjs->register_foot('jquery.dataTables', array(
                'src' => __WWW_ROOT_JQUERYDATATABLES__ . '/skin/js/jquery.dataTables.min.js'
            ));
            $cssjs->register_foot('clementine_crud-datatables', $this->getBlockHtml($this->_class . '/js_datatables', $this->data, $request));
            // alert on delbutton
            $cssjs->register_foot('clementine_crud-delbutton_confirm', $this->getBlockHtml($this->_class . '/js_delbutton_confirm', $this->data, $request));
        }
    }

    /**
     * handle_ajax_filtering : datatables ajax filtering (recherche dans les champs affichés uniquement)
     *
     * @param mixed $champs_recherche
     * @param mixed $metas
     * @param mixed $params
     * @access public
     * @return void
     */
    public function handle_ajax_filtering($champs_recherche, $metas, $params = null)
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
            // boucler sur les champs dans lesquels rechercher -> liste des champs par défaut si non définie
            // pouvoir définir dans quels champs rechercher
            // $metas fields_search
            foreach ($champs_recherche as $champ_recherche) {
                $nom_champ_affiche = $champ_recherche;
                if (isset($this->data['fields'][$champ_recherche]) && $this->data['fields'][$champ_recherche]['type'] == 'custom_field') {
                    if (isset($metas['custom_fields'][$champ_recherche])) {
                        $nom_champ_affiche = $metas['custom_fields'][$champ_recherche];
                    } else {
                        // champ custom_field qui n'a aucune définition SQL
                        continue;
                    }
                }
                if (isset($metas['custom_search'][$champ_recherche])) {
                    if ($metas['custom_search'][$champ_recherche] != '0') {
                        // si le custom_field est un GROUP_CONCAT par exemple, on ne peut pas faire un like directement sur GROUP_CONCAT(monchamp), donc on le fait directement sur monchamp
                        $filter_where.= "\n    " . $metas['custom_search'][$champ_recherche] . " LIKE '%" . $db->escape_string($sSearch) . "%' OR ";
                        // recherche aussi la version encodée en HTML
                        $filter_where.= "\n    " . $metas['custom_search'][$champ_recherche] . " LIKE '%" . $db->escape_string($ns->htmlentities($sSearch, ENT_QUOTES)) . "%' OR ";
                    }
                } else {
                    $filter_where.= "\n    " . $nom_champ_affiche . " LIKE '%" . $db->escape_string($sSearch) . "%' OR ";
                    // recherche aussi la version encodée en HTML
                    $filter_where.= "\n    " . $nom_champ_affiche . " LIKE '%" . $db->escape_string($ns->htmlentities($sSearch, ENT_QUOTES)) . "%' OR ";
                }
            }
            $filter_where = substr($filter_where, 0, -3);
        }
        return $filter_where;
    }

    /**
     * =====================================
     * Hooks pour centraliser les surcharges
     * =====================================
     */

    public function set_options($request = null, $params = null)
    {
    }

    /**
     * override_fields : surcharge les types de champs
     *
     * @access public
     * @return void
     */
    public function override_fields($request, $params = null)
    {
        /*$this->overrideField('table.field', 'file', array(*/
            /*'max_filesize' => 10000000,*/
            /*'extensions'   => array('jpg', 'pdf'),*/
            /*'dest_dir'     => __FILES_ROOT__ . '/files/media',*/
        /*));*/
    }

    public function override_fields_index($request, $params = null)
    {
    }

    public function override_fields_create_or_update($request, $params = null)
    {
    }

    public function override_fields_read($request, $params = null)
    {
    }

    /**
     * override_url : fonctions appelee par indexAction, updateAction et readAction
     *                pour modifier l'url des bouttons back, validate, delete
     *
     * @param mixed $params
     * @access public
     * @return void
     */
    public function override_url($request, $params = null)
    {
    }

    public function override_url_index($request, $params = null)
    {
    }

    public function override_url_create_or_update($request, $params = null)
    {
    }

    public function override_url_read($request, $params = null)
    {
    }

    /**
     * alter_values : fonctions appelee par indexAction, updateAction et readAction
     *                pour modifier les valeurs chargees AVANT de les transmettre
     *                à la vue. Pour changer le format d'une date, etc...
     *
     * @param mixed $params
     * @access public
     * @return void
     */
    public function alter_values($request, $params = null)
    {
    }

    public function alter_values_index($request, $params = null)
    {
    }

    public function alter_values_create_or_update($request, $params = null)
    {
    }

    public function alter_values_read($request, $params = null)
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
    public function rename_fields($request, $params = null)
    {
    }

    public function rename_fields_index($request, $params = null)
    {
    }

    public function rename_fields_create_or_update($request, $params = null)
    {
    }

    public function rename_fields_read($request, $params = null)
    {
    }

    /**
     * wrap_fields : fonction appellée par index, creation, read, et update
     *               pour envelopper des groupes de champs dans des balises html
     *
     * @param mixed $params
     * @access public
     * @return void
     */
    public function wrap_fields($request, $params = null)
    {
    }

    public function wrap_fields_index($request, $params = null)
    {
    }

    public function wrap_fields_create_or_update($request, $params = null)
    {
    }

    public function wrap_fields_read($request, $params = null)
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
    public function hide_fields($request, $params = null)
    {
    }

    public function hide_fields_index($request, $params = null)
    {
    }

    public function hide_fields_create_or_update($request, $params = null)
    {
    }

    public function hide_fields_read($request, $params = null)
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
    public function add_fields($request, $params = null)
    {
    }

    public function add_fields_index($request, $params = null)
    {
    }

    public function add_fields_create_or_update($request, $params = null)
    {
    }

    public function add_fields_read($request, $params = null)
    {
    }

    /**
     * move_fields : fonction appellée par index, creation, read, et update
     *               pour déplacer des champs par des appels a moveField
     *
     * @param mixed $params
     * @access public
     * @return void
     */
    public function move_fields($request, $params = null)
    {
    }

    public function move_fields_index($request, $params = null)
    {
    }

    public function move_fields_create_or_update($request, $params = null)
    {
    }

    public function move_fields_read($request, $params = null)
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
    public function hide_sections($request, $params = null)
    {
    }

    public function hide_sections_index($request, $params = null)
    {
    }

    public function hide_sections_create_or_update($request, $params = null)
    {
    }

    public function hide_sections_read($request, $params = null)
    {
    }

    /**
     * need_privileges : fonction appellée par tous les controleurs de type *Action
     *                   pour protéger ces pages
     *
     * @param mixed $params
     * @access public
     * @return void
     */
    public function need_privileges($request = null, $params = null)
    {
    }

    public function need_privileges_index($request = null, $params = null)
    {
    }

    public function need_privileges_create_or_update($request = null, $params = null)
    {
    }

    public function need_privileges_read($request = null, $params = null)
    {
    }

    public function need_privileges_delete($request = null, $params = null)
    {
    }

    public function need_privileges_deletetmpfile($request = null, $params = null)
    {
    }

}
