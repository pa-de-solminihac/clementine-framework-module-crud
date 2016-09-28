<?php
echo $data['label_open'];
echo $data['label_close'];
echo $data['div_open'];
if ($data['ligne'][$data['tablefield']]) {
    echo '✓';
} else {
    echo '✕';
}
echo $data['div_close'];
