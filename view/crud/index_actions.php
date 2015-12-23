<?php
$ns = Clementine::getModel('fonctions');
$current_key = $ns->htmlentities($data['current_key']);
$arrayUrl = array('updatebutton' => __WWW__ . '/' . $data['alldata']['class'] . '/update?' . $current_key,
                  'readbutton' => __WWW__ . '/' . $data['alldata']['class'] . '/read?' . $current_key,
                  'duplicatebutton' => __WWW__ . '/' . $data['alldata']['class'] . '/create?duplicate=1&' . $current_key,
                  'delbutton' => __WWW__ . '/' . $data['alldata']['class'] . '/delete?' . $current_key);

foreach ($arrayUrl as $key => &$value) {
    if (isset($data['alldata']['button_url_' . $key])) {
        $value = $data['alldata']['button_url_' . $key] . $current_key;
    }
}

$sections = array(
    'updatebutton' => array(
        'url' => $arrayUrl['updatebutton'],
        'icon' => 'glyphicon glyphicon-edit',
        'label' => 'Modifier',
    ),
    'readbutton' => array(
        'url' => $arrayUrl['readbutton'],
        'icon' => 'glyphicon glyphicon-file',
        'label' => 'Afficher',
    ),
    'duplicatebutton' => array(
        'url' => $arrayUrl['duplicatebutton'],
        'icon' => 'glyphicon glyphicon-duplicate',
        'label' => 'Dupliquer',
    ),
    'delbutton' => array(
        'url' => $arrayUrl['delbutton'],
        'icon' => 'glyphicon glyphicon-trash',
        'label' => 'Supprimer',
    ),
);
if (empty($data['crud-sections'])) {
    $data['crud-sections'] = $sections;
} else {
    // merge overrides and default sections
    // special values :
    //     'getParent' : import section from parent, usefull if you want to fix a menu item's position
    //     null : unset menu item
    $data['crud-sections'] = $ns->array_override($sections, $data['crud-sections']);
}
// complete urls and generate html code
foreach ($data['crud-sections'] as $section_key => $section_metas) {
    if (empty($data['alldata']['hidden_sections'][$section_key])) {
        $href = $data['crud-sections'][$section_key]['url'];
        foreach ($data['alldata']['url_parameters'] as $key => $val) {
            $href = $ns->add_param($href, $key, $val, 1);
        }
        $data['crud-sections'][$section_key]['url'] = $href;
    } else {
        unset($data['crud-sections'][$section_key]);
    }
}
// extract first and last_elements
if (!empty($data['crud-sections'])) {
    $first_section_key = $ns->array_first_key($data['crud-sections']);
    $last_section_key = $ns->array_last_key($data['crud-sections']);
?>
<div class="dropdown">
    <button class="btn-link dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="true">
        <span class="glyphicon glyphicon-option-vertical"></span>
    </button>
    <ul class="dropdown-menu dropdown-menu-right" role="menu">
<?php
    foreach ($data['crud-sections'] as $section_key => $section) {
        if ($section_key == $last_section_key) {
?>
        <li class="divider"></li>
<?php
        }
?>
        <li>
            <a class="clementine_crud-<?php echo $section_key; ?> clementine_crud-list-<?php 
        echo $section_key;
        echo ' ';
        echo $section_key;
        if ($section_key == 'delbutton') {
            echo ' btn-danger ';
        }
        ?>" href="<?php echo $section['url']; ?>">
                    <i class="<?php echo $section['icon']; ?>"></i>
                    <?php echo $section['label']; ?>
                </a>
            </li>
<?php
    }
?>
    </ul>
</div>
<?php
}
