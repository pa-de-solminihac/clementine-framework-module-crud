<div class="clementine_crud-<?php echo $data['formtype']; ?>_div <?php echo implode(' ', $data['more_classes_wrap']); ?>">
<?php
if (is_array($data['values']) && (count($data['values']) == 1)) {
    foreach ($data['values'] as $current_key => $ligne) {
        $this->getBlock(
            $data['class'] . '/' . $data['formtype'] . '_fields',
            array(
                'current_key' => $current_key,
                'ligne' => $ligne,
                'alldata' => $data
            ),
            $request);
    }
}
$this->getBlock(
    $data['class'] . '/' . $data['formtype'] . '_actions',
    array(
        'current_key' => $current_key,
        'ligne' => $ligne,
        'alldata' => $data
    ),
    $request);
?>
</div>
