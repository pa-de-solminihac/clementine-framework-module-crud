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
?>
<a href="<?php echo $ns->mod_param($this_url, 'file', $tablefield); ?>" target=""><?php echo $visible_name; ?></a>
