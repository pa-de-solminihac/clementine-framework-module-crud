<?php
echo $data['label_open'];
echo $data['label_close'];
echo $data['div_open'];
if (!empty($data['fieldmeta']['fieldvalues']) && isset($data['fieldmeta']['fieldvalues'][$data['ligne'][$data['tablefield']]])) {
    echo $data['fieldmeta']['fieldvalues'][$data['ligne'][$data['tablefield']]];
} else {
    echo $ns->htmlentities($data['ligne'][$data['tablefield']]);
}
echo $data['div_close'];
