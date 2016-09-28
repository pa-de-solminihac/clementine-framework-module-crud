<?php
echo $data['label_open'];
echo $data['label_close'];
echo $data['div_open'];
$thisdata = array(
    'ligne' => $data['ligne'],
    'tablefield' => $data['tablefield']
);
$this->getBlock(
    $data['alldata']['class'] . '/read_file',
    array(
        'data' => $thisdata,
        'alldata' => $data['alldata']
    ),
    $request
);
echo $data['div_close'];
