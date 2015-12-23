<div class="clementine_crud-<?php echo $data['formtype']; ?>_div <?php echo implode(' ', $data['more_classes_wrap']); ?>">
    <form class="clementine_crud-<?php echo $data['formtype']; ?>_form clementine_crud-form <?php echo implode(' ', $data['more_classes_form']); ?>" action="<?php echo $request->FULLURL; ?>" method="post" accept-charset="utf-8" enctype="multipart/form-data">
        <input type="hidden" id="clementine_crud_formId" name="clementine_crud_formId" value="<?php echo $data['formId']; ?>" />
<?php
if (is_array($data['values']) && (count($data['values']) == 1)) {
    foreach ($data['values'] as $current_key => $ligne) {
        Clementine::getBlock(
            $data['class'] . '/' . $data['formtype'] . '_fields',
            array(
                'current_key' => $current_key,
                'ligne' => $ligne,
                'alldata' => $data
            ),
            $request);
    }
}
Clementine::getBlock(
    $data['class'] . '/' . $data['formtype'] . '_actions',
    array(
        'current_key' => $current_key,
        'ligne' => $ligne,
        'alldata' => $data
    ),
    $request);
?>
    </form>
</div>
