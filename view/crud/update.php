<?php
$data['formtype'] = 'update';
$ns = $this->getModel('fonctions');
$this->getBlock($data['class'] . '/errors', $data);
if (isset($data['values']) && is_array($data['values']) && (count($data['values']) == 1)) {
    foreach ($data['values'] as $current_key => &$ligne) {
        foreach ($ligne as $tablefield => &$val) {
            if ($data['fields'][$tablefield]['type'] == 'time') {
                $val = $ns->substr($val, 0, 5);
                if ($val == '00:00') {
                    $val = '';
                } else {
                    if ($val) {
                        $val = date('H:i', strtotime($val));
                    }
                }
            }
            if ($data['fields'][$tablefield]['type'] == 'date') {
                $val = $ns->substr($val, 0, 10);
                if ($val == '0000-00-00') {
                    $val = '';
                } else {
                    if ($val) {
                        $val = date('d/m/Y', strtotime($val));
                    }
                }
            }
            if ($data['fields'][$tablefield]['type'] == 'datetime') {
                $val = $ns->substr($val, 0, 16);
                if ($val == '0000-00-00 00:00:00') {
                    $val = '';
                } else {
                    if ($val) {
                        $val = date('d/m/Y H:i:s', strtotime($val));
                    }
                }
            }
        }
?>
<form class="clementine_crud-update_form clementine_crud-form" action="" method="post" accept-charset="utf-8" enctype="multipart/form-data">
    <table class="clementine_crud-update_table">
<?php
        if (!(isset($data['hidden_sections']['thead']) && ($data['hidden_sections']['thead']))) {
?>
        <thead>
            <tr>
<?php
            if (!(isset($data['hidden_sections']['names']) && ($data['hidden_sections']['names']))) {
?>
                <th class="clementine_crud-<?php echo $data['formtype']; ?>-title_column">Champ</th>
<?php
            }
            if (!(isset($data['hidden_sections']['values']) && ($data['hidden_sections']['values']))) {
?>
                <th class="clementine_crud-<?php echo $data['formtype']; ?>-value_column">Valeur</th>
<?php
            }
?>
            </tr>
        </thead>
<?php
        }
?>
        <tbody>
<?php
if (is_array($data['values']) && (count($data['values']) == 1)) {
    foreach ($data['values'] as $current_key => $ligne) {
        $this->getBlock($data['class'] . '/update_fields', array('current_key' => $current_key, 'ligne' => $ligne, 'alldata' => $data));
    }
}
?>
        </tbody>
    </table>
<?php
        $this->getBlock($data['class'] . '/update_actions', array('current_key' => $current_key, 'ligne' => $ligne, 'alldata' => $data));
?>
</form>
<?php
    }
}
?>
