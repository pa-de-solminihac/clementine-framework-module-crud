
            // plupload general
            if (!plupload_crudform) {
                return undefined;
            }

            jQuery('input[type=file]').each(function () {
                var this_id = jQuery(this).attr('id');
                if (!jQuery('#' + this_id + '-hidden').val()) {
                    return undefined;
                }
                jQuery(this).hide();
                jQuery('#' + this_id + '-removecontainer').hide();
                jQuery('#' + this_id + '-infoscontainer').hide();
                jQuery('#' + this_id + '-after').show();
                jQuery('#' + this_id + '-getfile').show();
            });

            // plupload_finished onclick
            jQuery(document).on('click.plupload_finished', 'a.plupload_finished', function() {
                var this_id = jQuery(this).attr('id');
                if (!this_id) {
                    return undefined;
                }
                var this_href = jQuery(this).attr('href');
                var fin_chaine = parseInt(this_id.length - '-after'.length);
                var dom_file_elem = this_id.substring(0, fin_chaine);
                if (this_href) {
                    jQuery.ajax({
                        url: this_href,
                        async: false,
                        type: "get",
                        success: function(data) {
                            if (!dom_file_elem) {
                                return undefined;
                            }
                            jQuery('#' + this_id).html('');
                            jQuery('#' + dom_file_elem).show();
                            // consequence de : "masque le champ upload autrement car le hide() plante le positionnement du flash sous IE"
                            jQuery('#' + dom_file_elem).css('visibility', 'visible');
                            jQuery('#' + dom_file_elem).css('position', 'relative');
                            jQuery('#' + dom_file_elem).css('zIndex', '1');
                            jQuery('#' + dom_file_elem + '-uplcontainer > .moxie-shim:first').css('position', 'absolute');
                            jQuery('#' + dom_file_elem + '-uplcontainer > form:first').css('position', 'absolute');
                            if (jQuery('#' + dom_file_elem + '-uplcontainer > .moxie-shim:first').hasClass('flash') || jQuery('#' + dom_file_elem + '-uplcontainer > form:first').hasClass('flash')) {
                                jQuery('#' + dom_file_elem + '-uplcontainer > .moxie-shim:first').css('zIndex', '2');
                                jQuery('#' + dom_file_elem + '-uplcontainer > form:first').css('zIndex', '2');
                            } else {
                                jQuery('#' + dom_file_elem + '-uplcontainer > .moxie-shim:first').css('zIndex', '1');
                                jQuery('#' + dom_file_elem + '-uplcontainer > form:first').css('zIndex', '1');
                            }
                            jQuery('#' + dom_file_elem + '-infoscontainer').show();
                            // transmision du nom de fichier
                            jQuery('#' + dom_file_elem + '-hidden').val('');
                        }
                    });
                } else {
                    if (!dom_file_elem) {
                        return undefined;
                    }
                    jQuery('#' + this_id).html('');
                    jQuery('#' + this_id).siblings().filter('.plupload_getfile').html('');
                    jQuery('#' + dom_file_elem).show();
                    jQuery('#' + dom_file_elem + '-infoscontainer').show();
                    // raz du nom de fichier
                    jQuery('#' + dom_file_elem + '-hidden').val('');
                }
                return false;
            });

            // enqueue submit if submit asked before uploads are finished
            plupload_crudform.bind('submit', function () {
                var pending_uploads = (plupload_crudform.data('pending_uploads') != undefined) && (plupload_crudform.data('pending_uploads') != 0);
                if (!pending_uploads) {
                    return undefined;
                }
                // enqueue submit action
                plupload_crudform_submit.attr('disabled', 'disabled');
                plupload_crudform_submit.addClass('plupload_disabled');
                plupload_crudform_submit.val('...');
                plupload_crudform.data('automatic_submit', true);
                return false;
            });

