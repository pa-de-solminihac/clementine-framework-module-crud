<script type="text/javascript">
    // si jQuery est chargé
    if (typeof(jQuery) != "undefined") {
        jQuery(document).ready(function() {

            var plupload_crudform = '';
            // plupload par fichier
<?php
foreach ($data['fields'] as $tablefield => $fieldmeta) {
    if (isset($fieldmeta['type']) && $fieldmeta['type'] == 'file') {
        $browsebutton = str_replace('.', '-', $tablefield);
        $rand = uniqid();
?>
            plupload_crudform = jQuery('#<?php echo $browsebutton; ?>').closest('form');
            plupload_crudform_submit = jQuery(plupload_crudform.find('input[type=submit]:first').get(0));
            if (plupload_crudform) {
                var formurl = plupload_crudform.attr('action');
                if (!formurl) {
                    formurl = document.location.href;
                }
                // pour pouvoir envoyer facilement le nom du champ dans l'URL
                if (formurl.indexOf('?') == -1) {
                    formurl += '?';
                }
                var uploader_<?php echo $rand; ?> = new plupload.Uploader({
                    runtimes : 'html5,flash,html4',
                    flash_swf_url : '<?php echo __WWW_ROOT_JSTOOLS__; ?>/skin/plupload/plupload.flash.swf',
                    browse_button : '<?php echo $browsebutton; ?>',
                    container : '<?php echo $browsebutton; ?>-uplcontainer',
<?php
    // max_filesize
    if (isset($fieldmeta['parameters']) && isset($fieldmeta['parameters']['max_filesize'])) {
?>
                    max_file_size : '<?php echo $fieldmeta['parameters']['max_filesize']; ?>b',
<?php
    } else {
?>
                    max_file_size : '<?php echo $this->getModel('fonctions')->get_max_filesize(); ?>b',
<?php
    }
    // extensions autorisees
    if (isset($fieldmeta['parameters']) && isset($fieldmeta['parameters']['extensions'])) {
?>
                    filters : [
                        {title : "Fichiers acceptés", extensions : "<?php echo implode(',', $fieldmeta['parameters']['extensions']); ?>"}
                    ],
<?php
    }
?>
                    multi_selection: false,
                    // unique_names: true,
                    url : formurl + '&plupload_field_name=<?php echo $browsebutton; ?>',
                    init: {
                        FilesAdded: function(up, file) {
                            if (!jQuery('#<?php echo $browsebutton; ?>-after').length) {
                                jQuery('#<?php echo $browsebutton; ?>').after(' <a href="" id="<?php echo $browsebutton; ?>-after" class="plupload_finished" />');
                            }
                            pending_uploads = 1;
                            if (plupload_crudform.data('pending_uploads') != undefined) {
                                pending_uploads = parseInt(plupload_crudform.data('pending_uploads')) + 1;
                            }
                            plupload_crudform.data('pending_uploads', pending_uploads);
                            uploader_<?php echo $rand; ?>.start();
                        },
                        UploadProgress: function(up, file) {
                            jQuery('#<?php echo $browsebutton; ?>-after').html(file.percent + "%");
                            jQuery('#<?php echo $browsebutton; ?>-after').attr('href', '');
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
                            if (retval == '1') {
                                alert(msg.substring(1));
                                jQuery('#<?php echo $browsebutton; ?>-after').html('erreur');
                                jQuery('#<?php echo $browsebutton; ?>-after').attr('href', '');
                                retour = 0;
                            } else if (retval == '2') {
                                document.location = msg.substring(1);
                                retour = 0;
                            } else {
                                var noms = msg.substring(1).split(':');
                                var temp_name = noms[0];
                                var orig_name = noms[1];
                                jQuery('#<?php echo $browsebutton; ?>-infoscontainer').hide();
                                jQuery('#<?php echo $browsebutton; ?>-after').attr('href', '<?php echo __WWW__ . '/' . $data['class'] . '/deletetmpfile?file='; ?>' + temp_name);
                                jQuery('#<?php echo $browsebutton; ?>-after').html('supprimer le fichier ' + orig_name);
                                // transmision du nom de fichier
                                jQuery('#<?php echo $browsebutton; ?>-hidden').val(temp_name);
                                // masque le champ upload autrement car le hide() plante le positionnement du flash sous IE
                                jQuery('#<?php echo $browsebutton; ?>').css('position', 'absolute');
                                jQuery('#<?php echo $browsebutton; ?>').css('zIndex', '-1');
                                jQuery('#<?php echo $browsebutton; ?>').css('visibility', 'hidden');
                                jQuery('#<?php echo $browsebutton; ?>-uplcontainer > .plupload:first').css('position', 'absolute');
                                jQuery('#<?php echo $browsebutton; ?>-uplcontainer > .plupload:first').css('zIndex', '-2');
                                if ((plupload_crudform.data('automatic_submit') != undefined) && (plupload_crudform.data('automatic_submit') == true)) {
                                    plupload_crudform.submit();
                                }
                            }
                        },
                        Error: function(up, err) {
                            alert(err.message);
                        }
                    }
                });
                uploader_<?php echo $rand; ?>.init();
            }
<?php
    }
}
?>

            // plupload general
            if (plupload_crudform) {

                jQuery('input[type=file]').each(function () {
                    var this_id = jQuery(this).attr('id');
                    if (jQuery('#' + this_id + '-hidden').val()) {
                        jQuery(this).hide();
                        jQuery('#' + this_id + '-removecontainer').hide();
                        jQuery('#' + this_id + '-infoscontainer').hide();
                        jQuery('#' + this_id + '-after').show();
                        jQuery('#' + this_id + '-getfile').show();
                    }
                });

                // plupload_finished onclick
                jQuery(document).delegate('a.plupload_finished', 'click', function() {
                    var this_id = jQuery(this).attr('id');
                    if (this_id) {
                        var this_href = jQuery(this).attr('href');
                        var fin_chaine = parseInt(this_id.length - '-after'.length);
                        var dom_file_elem = this_id.substring(0, fin_chaine);

                        if (this_href) {
                            jQuery.ajax({
                                url: this_href,
                                    async: false,
                                    type: "get",
                                    success: function(data) {
                                        if (dom_file_elem) {
                                            jQuery('#' + this_id).html('');
                                            jQuery('#' + dom_file_elem).show();
                                            // consequence de : "masque le champ upload autrement car le hide() plante le positionnement du flash sous IE"
                                            jQuery('#' + dom_file_elem).css('visibility', 'visible');
                                            jQuery('#' + dom_file_elem).css('position', 'relative');
                                            jQuery('#' + dom_file_elem).css('zIndex', '1');
                                            jQuery('#' + dom_file_elem + '-uplcontainer > .plupload:first').css('position', 'absolute');
                                            if (jQuery('#' + dom_file_elem + '-uplcontainer > .plupload:first').hasClass('flash')) {
                                                jQuery('#' + dom_file_elem + '-uplcontainer > .plupload:first').css('zIndex', '2');
                                            } else {
                                                jQuery('#' + dom_file_elem + '-uplcontainer > .plupload:first').css('zIndex', '0');
                                            }
                                            jQuery('#' + dom_file_elem + '-infoscontainer').show();
                                            // transmision du nom de fichier
                                            jQuery('#' + dom_file_elem + '-hidden').val('');
                                        }
                                    }
                            });
                        } else {
                            if (dom_file_elem) {
                                jQuery('#' + this_id).html('');
                                jQuery('#' + this_id).siblings().filter('.plupload_getfile').html('');
                                jQuery('#' + dom_file_elem).show();
                                jQuery('#' + dom_file_elem + '-infoscontainer').show();
                                // raz du nom de fichier
                                jQuery('#' + dom_file_elem + '-hidden').val('');
                            }
                        }
                    }
                    return false;
                });

                // enqueue submit if submit asked before uploads are finished
                plupload_crudform.bind('submit', function () {
                    if ((plupload_crudform.data('pending_uploads') != undefined) && (plupload_crudform.data('pending_uploads') != 0)) {
                        // enqueue submit action
                        plupload_crudform_submit.attr('disabled', 'disabled');
                        plupload_crudform_submit.addClass('plupload_disabled');
                        plupload_crudform.data('automatic_submit', true);
                        return false;
                    }
                });

            }

        });
    }
</script>
