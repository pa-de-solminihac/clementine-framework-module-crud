
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
                //jQuery('#' + this_id + '-addfile').show();
                jQuery('#' + this_id + '-getfile').show();
            });

            // plupload_finished onclick
            jQuery(document).off('click.plupload_finished').on('click.plupload_finished', 'a.plupload_finished', function() {
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
                        async: true,
                        xhrFields: {
                            withCredentials: true,
                        },
                        type: "get",
                        success: function(data) {
                            if (!dom_file_elem) {
                                return undefined;
                            }
                            jQuery('#' + this_id).html('');
                            jQuery('#' + dom_file_elem).show();
                            jQuery('#' + dom_file_elem + '-after').hide();
                            jQuery('#' + dom_file_elem + '-addfile').hide();
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
                            // use callbacks with attribute data-ondelete=""
                            var callback_code;
                            if (callback_code = jQuery('#' + dom_file_elem).attr('data-ondelete')) {
                                var callback_func = new Function('data', callback_code);
                                callback_func(data);
                            }
                        }
                    });
                } else {
                    if (!dom_file_elem) {
                        return undefined;
                    }
                    jQuery('#' + this_id).html('');
                    jQuery('#' + this_id).siblings().filter('.plupload_getfile').html('');
                    jQuery('#' + dom_file_elem).show();
                    jQuery('#' + dom_file_elem + '-after').hide();
                    jQuery('#' + dom_file_elem + '-addfile').hide();
                    jQuery('#' + dom_file_elem + '-infoscontainer').show();
                    // raz du nom de fichier
                    jQuery('#' + dom_file_elem + '-hidden').val('');
                }
                // refresh plupload element
                var uploader_md5 = jQuery('#' + dom_file_elem).attr('data-md5');
                if (uploader_md5) {
                    window['uploader_' + uploader_md5].refresh();
                }
                return false;
            });

            // plupload_addfile onclick
            jQuery(document).off('click.plupload_addfile').on('click.plupload_addfile', 'a.plupload_addfile', function(e) {
                e.stopPropagation();
                e.stopImmediatePropagation();
                e.preventDefault();
                var this_id = jQuery(this).attr('id');
                if (!this_id) {
                    return undefined;
                }
                var this_href = jQuery(this).attr('href');
                var fin_chaine = parseInt(this_id.length - '-addfile'.length);
                var dom_file_elem = this_id.substring(0, fin_chaine);
                if (!dom_file_elem) {
                    return undefined;
                }
                jQuery('#' + this_id).html('');
                jQuery('#' + dom_file_elem).show();
                jQuery('#' + dom_file_elem + '-after').hide();
                // TODO: triggering click on plupload shim does not work in IE
                var isIE = /(MSIE|Trident\/|Edge\/)/i.test(navigator.userAgent);
                if (!isIE) {
                    var shim_selector = '#' + dom_file_elem + '-uplcontainer > .moxie-shim input[type=file]:first';
                    jQuery(shim_selector).trigger('click');
                }
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
                // refresh plupload element
                var uploader_md5 = jQuery('#' + dom_file_elem).attr('data-md5');
                if (uploader_md5) {
                    window['uploader_' + uploader_md5].refresh();
                }
                // use callbacks with attribute data-onaddfile=""
                var callback_code;
                if (callback_code = jQuery('#' + dom_file_elem).attr('data-onaddfile')) {
                    var callback_func = new Function('e', callback_code);
                    callback_func(e);
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

