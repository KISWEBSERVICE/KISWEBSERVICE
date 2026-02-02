/**
 * Room35 Client Hub - Admin JavaScript
 */

(function($) {
    'use strict';

    // Initialize when document is ready
    $(document).ready(function() {
        initDropzone();
        initAssignToggle();
        initUploadForm();
        initDeleteButtons();
    });

    /**
     * Initialize dropzone functionality
     */
    function initDropzone() {
        var $dropzone = $('#r35-dropzone');
        var $fileInput = $('#r35-file-input');
        var $content = $dropzone.find('.r35-dropzone-content');
        var $preview = $dropzone.find('.r35-file-preview');
        var $fileName = $dropzone.find('.r35-file-name');
        var $removeBtn = $dropzone.find('.r35-remove-file');

        // Drag and drop events
        $dropzone.on('dragover dragenter', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).addClass('dragover');
        });

        $dropzone.on('dragleave dragend drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).removeClass('dragover');
        });

        $dropzone.on('drop', function(e) {
            var files = e.originalEvent.dataTransfer.files;
            if (files.length) {
                $fileInput[0].files = files;
                showFilePreview(files[0].name);
            }
        });

        // File input change
        $fileInput.on('change', function() {
            if (this.files.length) {
                showFilePreview(this.files[0].name);
            }
        });

        // Remove file button
        $removeBtn.on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $fileInput.val('');
            $content.show();
            $preview.hide();
        });

        function showFilePreview(name) {
            $fileName.text(name);
            $content.hide();
            $preview.show();
        }
    }

    /**
     * Initialize assign type toggle
     */
    function initAssignToggle() {
        var $assignRadios = $('input[name="assign_type"]');
        var $clientSelect = $('#r35-client-select');

        $assignRadios.on('change', function() {
            if ($(this).val() === 'client') {
                $clientSelect.slideDown();
            } else {
                $clientSelect.slideUp();
            }
        });

        // Initial state
        if ($('input[name="assign_type"]:checked').val() === 'global') {
            $clientSelect.hide();
        }
    }

    /**
     * Initialize upload form
     */
    function initUploadForm() {
        var $form = $('#r35-admin-upload-form');
        var $progress = $form.find('.r35-upload-progress');
        var $progressFill = $form.find('.r35-progress-fill');
        var $progressText = $form.find('.r35-progress-text');
        var $result = $form.find('.r35-upload-result');
        var $submitBtn = $('#r35-upload-btn');

        $form.on('submit', function(e) {
            e.preventDefault();

            var fileInput = document.getElementById('r35-file-input');
            if (!fileInput.files.length) {
                showResult('error', 'Please select a file to upload.');
                return;
            }

            var assignType = $('input[name="assign_type"]:checked').val();
            var assignedTo = $('#assigned_to').val();

            if (assignType === 'client' && !assignedTo) {
                showResult('error', 'Please select a client.');
                return;
            }

            // Prepare form data
            var formData = new FormData();
            formData.append('action', 'r35_admin_upload');
            formData.append('nonce', r35HubAdmin.nonce);
            formData.append('file', fileInput.files[0]);
            formData.append('is_global', assignType === 'global' ? '1' : '0');
            formData.append('assigned_to', assignType === 'client' ? assignedTo : '0');

            // Show progress
            $result.hide();
            $progress.show();
            $progressFill.css('width', '0%');
            $submitBtn.prop('disabled', true);

            // Upload via AJAX
            $.ajax({
                url: r35HubAdmin.ajaxUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                xhr: function() {
                    var xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener('progress', function(e) {
                        if (e.lengthComputable) {
                            var percent = Math.round((e.loaded / e.total) * 100);
                            $progressFill.css('width', percent + '%');
                            $progressText.text(r35HubAdmin.strings.uploading + ' ' + percent + '%');
                        }
                    }, false);
                    return xhr;
                },
                success: function(response) {
                    $progress.hide();
                    $submitBtn.prop('disabled', false);

                    if (response.success) {
                        showResult('success', r35HubAdmin.strings.uploadSuccess);
                        // Reset form
                        $form[0].reset();
                        $('.r35-dropzone-content').show();
                        $('.r35-file-preview').hide();
                        $('#r35-client-select').show();
                    } else {
                        showResult('error', response.data.message || r35HubAdmin.strings.uploadError);
                    }
                },
                error: function() {
                    $progress.hide();
                    $submitBtn.prop('disabled', false);
                    showResult('error', r35HubAdmin.strings.uploadError);
                }
            });
        });

        function showResult(type, message) {
            $result
                .removeClass('success error')
                .addClass(type)
                .html(message)
                .show();
        }
    }

    /**
     * Initialize delete buttons
     */
    function initDeleteButtons() {
        $(document).on('click', '.r35-delete-file', function(e) {
            e.preventDefault();

            var $btn = $(this);
            var fileId = $btn.data('file-id');

            if (!confirm(r35HubAdmin.strings.confirmDelete)) {
                return;
            }

            $btn.prop('disabled', true);

            $.ajax({
                url: r35HubAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'r35_admin_delete',
                    nonce: r35HubAdmin.nonce,
                    file_id: fileId
                },
                success: function(response) {
                    if (response.success) {
                        // Remove the row from table
                        $btn.closest('tr').fadeOut(function() {
                            $(this).remove();
                        });
                    } else {
                        alert(response.data.message || r35HubAdmin.strings.deleteError);
                        $btn.prop('disabled', false);
                    }
                },
                error: function() {
                    alert(r35HubAdmin.strings.deleteError);
                    $btn.prop('disabled', false);
                }
            });
        });
    }

})(jQuery);
