<?php
$ns = $this->getModel('fonctions');
$ligne = $data['data']['ligne'];
$tablefield = $data['data']['tablefield'];
$this_url = $request->EQUIV[$request->LANG];
$file_cmspath = $ns->htmlentities($ligne[$tablefield]);
$file_path = str_replace('__CLEMENTINE_CONTENUS_WWW_ROOT__', __FILES_ROOT__, $file_cmspath);
$file_url = str_replace('__CLEMENTINE_CONTENUS_WWW_ROOT__', __WWW_ROOT__, $file_cmspath);
$visible_name = preg_replace('/^[^-]*-/', '', basename($file_cmspath));
$mimetype = $ns->get_mime_type($file_path);
$href = $ns->mod_param($this_url, 'file', $tablefield);
foreach ($data['alldata']['url_parameters'] as $key => $val) {
    $href = $ns->add_param($href, $key, $val, 1);
}
$extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
$is_image = in_array($extension, array('gif', 'jpg', 'jpeg', 'png'));
if ($is_image) {
?>
<img src="<?php echo $href; ?>" alt="<?php echo $visible_name; ?>" class="<?php echo implode(' ', $data['alldata']['more_classes_img']); ?>" />
<?php
} else {
?>
<a href="<?php echo $href; ?>" target=""><?php echo $visible_name; ?></a>
<?php
}
