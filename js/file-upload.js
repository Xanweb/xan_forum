
(function ($, window) {
    
    var i18n = {
        Too_many_files: 'Too many files',
        Invalid_file_extension: 'Invalid file extension',
        Max_file_size_exceeded: 'Max file size exceeded',
        Error_deleting_attachment: 'Something went wrong while deleting this attachment, please refresh and try again.',
        Confirm_remove_attachment: 'Remove this attachment?',
        Unspecified_error_occurred: 'An unspecified error occurred.',
        dictDefaultMessage: "Drop files here to upload",
        dictFallbackMessage: "Your browser does not support drag'n'drop file uploads.",
    };
    
    var methods = {

        init: function (options) {
            var $me = $(this);
            
            $me.on('click', '.ccm-attachment-toggle', function(e){
                e.preventDefault();
                $me.find('.ccm-attachment-container').each(function () {
                    if ($(this).is(':visible')) {
                        $(this).toggle();
                    }
                });
                $(this).nextAll('div.ccm-attachment-container:first').show();
            });
            
            $me.find('.ccm-attachment-dropzone').each(function () {
                var $dropZone = $(this);
                if (!($dropZone.attr('data-dropzone-applied'))) {
                    $dropZone.dropzone({
                        dictDefaultMessage: i18n.dictDefaultMessage,
                        dictFallbackMessage: i18n.dictFallbackMessage,
                        clickable: true,
                        accept: function (file, done) {
                            var errors = [];
                            var attachmentCount = this.files.length;
                            if ((options.maxFiles > 0) && attachmentCount > options.maxFiles) {
                                errors.push(i18n.Too_many_files);
                            }
                            var requiredExtensions = options.fileExtensions;
                            if (file.name.split('.').pop().toLowerCase() && requiredExtensions.indexOf(file.name.split('.').pop().toLowerCase()) == -1 && requiredExtensions != '') {
                                errors.push(i18n.Invalid_file_extension);
                            }
                            if ((options.maxFileSize > 0) && file.size > options.maxFileSize * 1000000) {
                                errors.push(i18n.Max_file_size_exceeded);
                            }

                            if (errors.length > 0) {
                                var self = this;
                                $('input[rel="' + $(file.previewTemplate).attr('rel') + '"]').remove();
                                var $form = $(file.previewTemplate).parent('.ccm-attachment-dropzone');
                                self.removeFile(file);
                                //obj.handlePostError($form, errors);
                                $form.children('.ccm-attachment-errors').delay(3000).fadeOut('slow', function () {
                                    $(this).html('');
                                });
                                attachmentCount = -1;
                                done('error'); // not displayed, just needs to have argument to trigger.
                            } else {
                                done();
                            }
                        },
                        'url': CCM_DISPATCHER_FILENAME + '/ccm/xan_forum/tools/file/upload?ccm_token='+$dropZone.data('token'),
                        'success': function (file, response) {
                            var self = this;
                            $(file.previewTemplate).click(function () {
                                $('input[rel="' + $(this).attr('rel') + '"]').remove();
                                self.removeFile(file);
                            });
                            if (!response.error) {
                                //this line for complete the progress upload file to 100% after response from server
                                file.previewTemplate.querySelector(".dz-progress .dz-upload").style.width=100+"%";
                                $me.append('<input rel="' + response.timestamp + '" type="hidden" name="attachments[]" value="' + response.id + '" />');
                            } else {
                                var $form = $('.preview.processing[rel="' + response.timestamp + '"]').closest('form');
                                //obj.handlePostError($form, [response.error]);
                                $('.preview.processing[rel="' + response.timestamp + '"]').remove();
                                $form.children('.ccm-attachment-errors').delay(3000).fadeOut('slow', function () {
                                    $(this).html('');
                                });
                            }
                        },
                        'sending': function (file, xhr, formData) {
                            $(file.previewTemplate).attr('rel', new Date().getTime());
                            formData.append("timestamp", $(file.previewTemplate).attr('rel'));
                            formData.append("fileCount", this.files.length);
                        },
                        'init': function () {
                            $(this.element).data('dropzone', this);
                            this.element.appendChild($("<div class=\"default message\"><span>" + this.options.dictDefaultMessage + "</span></div>")[0]);
                        },
                        'uploadprogress' : function (a, b) {
                            //limit the progress upload file to 80% until the response from server
                            a.previewTemplate.querySelector(".dz-progress .dz-upload").style.width=b+"%";
                            if (b>80){
                                a.previewTemplate.querySelector(".dz-progress .dz-upload").style.width=80+"%";
                            }
                        }
                    });
                    $dropZone.attr('data-dropzone-applied', 'true');
                }

            });
            
            
            return $.each($(this), function (i, obj) {
                $(this).find('.ccm-attachment-container').each(function () {
                    if ($(this).is(':visible')) {
                        $(this).toggle();
                    }
                });
            });
        },

        clearDropzoneQueues: function () {
            $('.preview.processing').each(function () {  // first remove any previous attachments and hide dropzone if it was open.
                $('input[rel="' + $(this).attr('rel') + '"]').remove();
            });
            $('.ccm-attachment-dropzone').each(function () {
                var d = $(this).data('dropzone');
                $.each(d.files, function (k, v) {
                    d.removeFile(v);
                });
            });
        },
        
    };

    $.fn.xanAttachments = function (method) {

        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || !method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error('Method ' + method + ' does not exist on xanAttachments');
        }

    };

    $.fn.xanAttachments.localize = function (dictionary) {
        $.extend(true, i18n, dictionary);
    };


})(jQuery, window);