<?php
$ns = $this->getModel('fonctions');
foreach ($data['plupload_block'] as $browseButton => $fieldMeta) {
    $md5BrowseButton = md5($browseButton);
?>
            plupload_crudform = jQuery('#<?php echo $browseButton; ?>').closest('form');
            plupload_crudform_submit = jQuery(plupload_crudform.find('input[type=submit]:first').get(0));
            if (!plupload_crudform) {
                return undefined;
            }
            var formurl = plupload_crudform.attr('action');
            if (!formurl) {
                formurl = document.location.href;
            }
            // pour pouvoir envoyer facilement le nom du champ dans l'URL
            if (formurl.indexOf('?') == -1) {
                formurl += '?';
            }
            if (typeof window.crud_plupload == 'undefined') {
                window.crud_plupload = {};
            }
            uploader_<?php echo $md5BrowseButton; ?> = new plupload.Uploader({
                runtimes : 'html5,flash,html4',
                required_features: 'send_browser_cookies',
                flash_swf_url : '<?php echo __WWW_ROOT_PLUPLOAD__; ?>/skin/js/Moxie.swf',
                browse_button : '<?php echo $browseButton; ?>',
                container : '<?php echo $browseButton; ?>-uplcontainer',
<?php
    // max_filesize
    if (isset($fieldMeta['parameters']) && isset($fieldMeta['parameters']['max_filesize'])) {
?>
                max_file_size : '<?php echo $fieldMeta['parameters']['max_filesize']; ?>b',
<?php
    } else {
?>
                max_file_size : '<?php echo $this->getModel('fonctions')->get_max_filesize(); ?>b',
<?php
    }
    // extensions autorisees
    if (isset($fieldMeta['parameters']) && isset($fieldMeta['parameters']['extensions'])) {
?>
                filters : [
                    {title : "Fichiers acceptés", extensions : "<?php echo implode(',', $fieldMeta['parameters']['extensions']); ?>"}
                ],
<?php
    }
?>
                multi_selection: false,
                // unique_names: true,
                url : formurl + '&plupload_field_name=<?php echo $browseButton; ?>',
                init: {
                    Init: function(uploader) {
                        // store uploader so we can use it to destroy
                        window.crud_plupload.uploader_<?php echo md5($browseButton); ?> = uploader;
                        // change label click too
                        var uploader_label_element = jQuery('label[for=<?php echo $browseButton; ?>]');
                        if (uploader_label_element) {
                            var shim_id = jQuery('#<?php echo $browseButton; ?>-uplcontainer > .moxie-shim:first > input[type=file]').attr('id');
                            if (shim_id) {
                                uploader_label_element.attr('for', shim_id);
                            }
                        }
                        // use callbacks with attribute data-oninit=""
                        var callback_code;
                        if (callback_code = jQuery('#<?php echo $browseButton; ?>').attr('data-oninit')) {
                            var callback_func = new Function('uploader', callback_code);
                            callback_func(uploader);
                        }
                    },
                    FilesAdded: function(up, file) {
                        if (!jQuery('#<?php echo $browseButton; ?>-after').length) {
                            jQuery('#<?php echo $browseButton; ?>').after(' <a href="" id="<?php echo $browseButton; ?>-after" class="plupload_finished delbutton" />');
                        }
                        jQuery('#<?php echo $browseButton; ?>-after').html("en cours");
                        jQuery('#<?php echo $browseButton; ?>-after').attr('href', '');
                        pending_uploads = 1;
                        if (plupload_crudform.data('pending_uploads') != undefined) {
                            pending_uploads = parseInt(plupload_crudform.data('pending_uploads')) + 1;
                        }
                        plupload_crudform.data('pending_uploads', pending_uploads);
                        uploader_<?php echo $md5BrowseButton; ?>.start();
                        // use callbacks with attribute data-onadd=""
                        var callback_code;
                        if (callback_code = jQuery('#<?php echo $browseButton; ?>').attr('data-onadd')) {
                            var callback_func = new Function('up', 'file', callback_code);
                            callback_func(up, file);
                        }
                    },
                    UploadProgress: function(up, file) {
                        pending_uploads = parseInt(plupload_crudform.data('pending_uploads'));
                        if (pending_uploads < 1) {
                            return undefined;
                        }
                        jQuery('#<?php echo $browseButton; ?>-after').html(file.percent + "%");
                        jQuery('#<?php echo $browseButton; ?>-after').attr('href', '');
                        // use callbacks with attribute data-onprogress=""
                        var callback_code;
                        if (callback_code = jQuery('#<?php echo $browseButton; ?>').attr('data-onprogress')) {
                            var callback_func = new Function('up', 'file', callback_code);
                            callback_func(up, file);
                        }
                    },
                    FileUploaded: function (up, file, info) {
                        pending_uploads = parseInt(plupload_crudform.data('pending_uploads')) - 1;
                        plupload_crudform.data('pending_uploads', pending_uploads);
                        if (pending_uploads == 0) {
                            plupload_crudform_submit.removeAttr('disabled');
                            plupload_crudform_submit.removeClass('plupload_disabled');
                        }
                        msg = info.response;
                        var retval = msg.substring(0, 1);
                        if (retval == '0') {
                            var noms = msg.substring(1).split(':');
                            var temp_filename = noms[0];
                            var orig_filename = noms[1];
                            jQuery('#<?php echo $browseButton; ?>-infoscontainer').hide();
<?php
    // url delete
    $deletetmpfile_href = __WWW__ . '/' . $data['class'] . '/deletetmpfile';
    if (!empty($data['url_parameters'])) {
        foreach ($data['url_parameters'] as $key => $val) {
            $deletetmpfile_href = $ns->add_param($deletetmpfile_href, $key, $val, 1);
        }
    }
    $deletetmpfile_href = $ns->mod_param($deletetmpfile_href, 'file', '');
?>
                            jQuery('#<?php echo $browseButton; ?>-after').attr('href', '<?php echo $deletetmpfile_href; ?>' + temp_filename);
                            jQuery('#<?php echo $browseButton; ?>-after').html('<i class="glyphicon glyphicon-trash"></i> supprimer <em>' + orig_filename + '</em>');
                            // transmision du nom de fichier
                            jQuery('#<?php echo $browseButton; ?>-hidden').val(temp_filename);
                            // masque le champ upload autrement car le hide() plante le positionnement du flash sous IE
                            jQuery('#<?php echo $browseButton; ?>').css('position', 'absolute');
                            jQuery('#<?php echo $browseButton; ?>').css('zIndex', '-1');
                            jQuery('#<?php echo $browseButton; ?>').css('visibility', 'hidden');
                            jQuery('#<?php echo $browseButton; ?>-uplcontainer > .moxie-shim:first').css('position', 'absolute');
                            jQuery('#<?php echo $browseButton; ?>-uplcontainer > .moxie-shim:first').css('zIndex', '-2');
                            //TODO: vérifier si form:first fonctionne toujours depuis la MAJ de plupload... notamment sous IE
                            jQuery('#<?php echo $browseButton; ?>-uplcontainer > form:first').css('position', 'absolute');
                            jQuery('#<?php echo $browseButton; ?>-uplcontainer > form:first').css('zIndex', '-2');
                            try {
                                document.getElementById('<?php echo $browseButton; ?>').setCustomValidity('');
                            } catch (e) {
                                // nothing
                            }
                            // use callbacks with attribute data-onupload=""
                            var callback_code;
                            if (callback_code = jQuery('#<?php echo $browseButton; ?>').attr('data-onupload')) {
                                var callback_func = new Function('up', 'file', 'info', callback_code);
                                callback_func(up, file, info);
                            }
                            if ((plupload_crudform.data('automatic_submit') != undefined) && (plupload_crudform.data('automatic_submit') == true)) {
                                plupload_crudform.submit();
                            }
                        } else if (retval == '2') {
                            document.location = msg.substring(1);
                            retour = 0;
                        } else if (retval == '1') {
                            // retval == 1 : erreur gérée
                            alert(msg.substring(1));
                            jQuery('#<?php echo $browseButton; ?>-after').html('erreur');
                            jQuery('#<?php echo $browseButton; ?>-after').attr('href', '');
                            retour = 0;
                        } else {
                            // erreur inattendue
                            alert('Erreur lors du transfert du fichier. Session expirée ?');
                            jQuery('#<?php echo $browseButton; ?>-after').html('erreur');
                            jQuery('#<?php echo $browseButton; ?>-after').attr('href', '');
                            retour = 0;
                        }
                    },
                    Error: function(up, err) {
                        // use callbacks with attribute data-onerror=""
                        var callback_code;
                        if (callback_code = jQuery('#<?php echo $browseButton; ?>').attr('data-onerror')) {
                            var callback_func = new Function('up', 'err', callback_code);
                            callback_func(up, err);
                        }
                        alert(err.message);
                    }
                }
            });
            //on ne doit pas pouvoir l'ouvrir depuis le clavier car on veut avoir le mouseenter !
            jQuery('#<?php echo $browseButton; ?>').attr('tabindex', '-1');
            //validation faite en AJAX... on évite les conflits avec plupload
            try {
                jQuery('#<?php echo $browseButton; ?>').get(0).setCustomValidity('');
            } catch (e) {
                // nothing
            }
            jQuery('[id^=<?php echo $browseButton; ?>]').each(function() {
                jQuery(this).removeAttr('required');
                if (!this.validationMessage) {
                    return undefined;
                }
                try {
                    this.setCustomValidity('');
                } catch (e) {
                    // nothing
                }
            });
<?php
    if (empty($data['onsenui'])) {
?>
            jQuery('#<?php echo $browseButton; ?>').on('mouseenter.clementine-crud-plupload', function () {
                jQuery('#<?php echo $browseButton; ?>').off('mouseenter.clementine-crud-plupload');
<?php
    }
?>
                jQuery('#<?php echo $browseButton; ?>').attr('data-md5', '<?php echo $md5BrowseButton; ?>');
                uploader_<?php echo $md5BrowseButton; ?>.init();
                jQuery('#<?php echo $browseButton; ?>').removeAttr('required');
                jQuery('[id^=<?php echo $browseButton; ?>]').each(function() {
                    jQuery(this).removeAttr('required');
                    if (!this.validationMessage) {
                        return undefined;
                    }
                    try {
                        this.setCustomValidity('');
                    } catch (e) {
                        // nothing
                    }
                });
<?php
    if (empty($data['onsenui'])) {
?>
                return false;
            });
<?php
    }
}
